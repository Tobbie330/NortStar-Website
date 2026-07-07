"""Wyze integration.

Device list + control uses the community `wyze-sdk` with Wyze's official
API-key login (create a key at https://developer-api-console.wyze.com/#/apikey/view).

Live video: Wyze's cloud streams are not directly embeddable, so streams are
served by the optional `wyze-bridge` container in docker-compose.yml
(https://github.com/mrlt8/docker-wyze-bridge), which turns every camera on
your account into HLS / WebRTC / RTSP feeds this dashboard can play.

Environment variables:
  WYZE_EMAIL, WYZE_PASSWORD   - your Wyze account
  WYZE_KEY_ID, WYZE_API_KEY   - from the Wyze developer console
  WYZE_BRIDGE_URL             - base URL of the wyze-bridge web UI as the
                                *browser* reaches it, e.g. http://192.168.1.50:8101
  WYZE_BRIDGE_HLS_URL         - HLS base (default: bridge host port 8888)
  WYZE_BRIDGE_WEBRTC_URL      - WebRTC base (default: bridge host port 8889)
"""
from __future__ import annotations

import asyncio
import re
from typing import Any
from urllib.parse import urlsplit, urlunsplit

from ..config import env
from ..models import Device, DeviceType, StreamInfo
from .base import Integration, IntegrationError


def _bridge_uri(nickname: str) -> str:
    """docker-wyze-bridge names streams by lowercased, hyphenated nickname."""
    return re.sub(r"[^a-z0-9\-]", "", re.sub(r"\s+", "-", nickname.strip().lower()))


def _swap_port(base_url: str, port: int) -> str:
    parts = urlsplit(base_url)
    host = parts.hostname or "localhost"
    return urlunsplit((parts.scheme or "http", f"{host}:{port}", "", "", ""))


class WyzeIntegration(Integration):
    id = "wyze"
    name = "Wyze"

    def __init__(self, config: dict[str, Any]):
        super().__init__(config)
        self._client = None
        self._lock = asyncio.Lock()

    async def start(self) -> None:
        missing = [
            var for var in ("WYZE_EMAIL", "WYZE_PASSWORD", "WYZE_KEY_ID", "WYZE_API_KEY")
            if not env(var)
        ]
        if missing:
            raise IntegrationError(f"Missing environment variables: {', '.join(missing)}")
        await self._login()

    async def _login(self) -> None:
        def _connect():
            from wyze_sdk import Client

            return Client(
                email=env("WYZE_EMAIL"),
                password=env("WYZE_PASSWORD"),
                key_id=env("WYZE_KEY_ID"),
                api_key=env("WYZE_API_KEY"),
            )

        try:
            self._client = await asyncio.to_thread(_connect)
        except Exception as exc:
            raise IntegrationError(f"Wyze login failed: {exc}") from exc

    async def _call(self, fn, *args, **kwargs):
        """Run a sync wyze-sdk call in a thread; re-login once on auth expiry."""
        from wyze_sdk.errors import WyzeApiError

        async with self._lock:
            try:
                return await asyncio.to_thread(fn, *args, **kwargs)
            except WyzeApiError as exc:
                if "AccessTokenError" in str(exc) or "2001" in str(exc):
                    await self._login()
                    return await asyncio.to_thread(fn, *args, **kwargs)
                raise

    def _camera_stream(self, nickname: str) -> StreamInfo | None:
        bridge = self.config.get("bridge_url") or env("WYZE_BRIDGE_URL")
        if not bridge:
            return None
        bridge = bridge.rstrip("/")
        uri = _bridge_uri(nickname)
        hls = (self.config.get("bridge_hls_url") or env("WYZE_BRIDGE_HLS_URL") or _swap_port(bridge, 8888)).rstrip("/")
        webrtc = (self.config.get("bridge_webrtc_url") or env("WYZE_BRIDGE_WEBRTC_URL") or _swap_port(bridge, 8889)).rstrip("/")
        return StreamInfo(
            hls=f"{hls}/{uri}/index.m3u8",
            webrtc=f"{webrtc}/{uri}",
            snapshot=f"{bridge}/snapshot/{uri}.jpg",
            rtsp=f"rtsp://{urlsplit(bridge).hostname}:8554/{uri}",
        )

    async def get_devices(self) -> list[Device]:
        if not self._client:
            raise IntegrationError("Wyze client not connected")
        raw_devices = await self._call(self._client.devices_list)
        devices: list[Device] = []
        for dev in raw_devices:
            dev_type = (dev.type or "").lower()
            nickname = dev.nickname or dev.mac
            base = dict(
                id=dev.mac,
                integration=self.id,
                name=nickname,
                model=getattr(dev.product, "model", None),
                online=bool(dev.is_online),
            )
            if "camera" in dev_type:
                devices.append(Device(
                    **base,
                    type=DeviceType.CAMERA,
                    capabilities=["turn_on", "turn_off", "restart"],
                    state={"power": "on" if dev.is_online else "off"},
                    stream=self._camera_stream(nickname),
                ))
            elif "light" in dev_type or "bulb" in dev_type:
                devices.append(Device(
                    **base,
                    type=DeviceType.LIGHT,
                    capabilities=["turn_on", "turn_off", "set_brightness", "set_color_temp", "set_color"],
                ))
            elif "plug" in dev_type or "outlet" in dev_type:
                devices.append(Device(
                    **base,
                    type=DeviceType.PLUG,
                    capabilities=["turn_on", "turn_off"],
                ))
            else:
                devices.append(Device(**base, type=DeviceType.OTHER))
        return devices

    async def send_command(self, device_id: str, command: str, params: dict[str, Any]) -> None:
        if not self._client:
            raise IntegrationError("Wyze client not connected")

        # Find the device to learn its type/model (wyze-sdk needs the model).
        raw_devices = await self._call(self._client.devices_list)
        target = next((d for d in raw_devices if d.mac == device_id), None)
        if target is None:
            raise IntegrationError(f"Unknown Wyze device {device_id}")
        model = getattr(target.product, "model", None)
        dev_type = (target.type or "").lower()
        kwargs = {"device_mac": device_id, "device_model": model}

        if "camera" in dev_type:
            api = self._client.cameras
        elif "light" in dev_type or "bulb" in dev_type:
            api = self._client.bulbs
        elif "plug" in dev_type or "outlet" in dev_type:
            api = self._client.plugs
        else:
            raise IntegrationError(f"Wyze device type '{target.type}' is not controllable yet")

        if command == "turn_on":
            await self._call(api.turn_on, **kwargs)
        elif command == "turn_off":
            await self._call(api.turn_off, **kwargs)
        elif command == "restart" and hasattr(api, "restart"):
            await self._call(api.restart, **kwargs)
        elif command == "set_brightness" and hasattr(api, "set_brightness"):
            await self._call(api.set_brightness, **kwargs, brightness=int(params.get("brightness", 100)))
        elif command == "set_color_temp" and hasattr(api, "set_color_temp"):
            await self._call(api.set_color_temp, **kwargs, color_temp=int(params.get("color_temp", 3800)))
        elif command == "set_color" and hasattr(api, "set_color"):
            await self._call(api.set_color, **kwargs, color=str(params.get("color", "ffffff")).lstrip("#"))
        else:
            raise IntegrationError(f"Unsupported command '{command}' for this Wyze device")
