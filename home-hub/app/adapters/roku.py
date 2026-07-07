"""Roku integration.

Two parts, because Roku's ecosystem is split:

1. Roku streaming devices / TVs — fully supported via Roku's documented
   External Control Protocol (ECP, port 8060 on your LAN): device info,
   power/keypress commands, app launch. Add each device's IP to config.yaml.

2. Roku Smart Home cameras — Roku does NOT publish an API for these, and
   their cloud is closed (the hardware is made by Wyze but runs Roku
   firmware, so docker-wyze-bridge can't see them either). Until Roku opens
   an API, this adapter shows any camera you list in config.yaml with a
   stream URL you provide — e.g. an RTSP/HLS restream from go2rtc, an NVR,
   or (a popular route) a camera reflashed with Wyze firmware, which then
   just appears under the Wyze integration instead. See the README for the
   options.

config.yaml example:
  roku:
    enabled: true
    devices:                # Roku TVs / players on your LAN (ECP)
      - name: Living Room TV
        host: 192.168.1.40
    cameras:                # Roku cams via a stream source you control
      - name: Driveway Cam
        hls_url: http://192.168.1.50:1984/api/stream.m3u8?src=driveway
        snapshot_url: http://192.168.1.50:1984/api/frame.jpeg?src=driveway
"""
from __future__ import annotations

import xml.etree.ElementTree as ET
from typing import Any

import httpx

from ..models import Device, DeviceType, StreamInfo
from .base import Integration, IntegrationError

ECP_PORT = 8060
KEYS = {"home": "Home", "power_toggle": "Power", "play": "Play", "pause": "Play",
        "up": "Up", "down": "Down", "left": "Left", "right": "Right",
        "select": "Select", "back": "Back", "volume_up": "VolumeUp",
        "volume_down": "VolumeDown", "mute": "VolumeMute"}


class RokuIntegration(Integration):
    id = "roku"
    name = "Roku"

    async def get_devices(self) -> list[Device]:
        devices: list[Device] = []

        for entry in self.config.get("devices", []) or []:
            host = entry.get("host")
            if not host:
                continue
            name = entry.get("name") or f"Roku {host}"
            info: dict[str, Any] = {}
            online = False
            try:
                async with httpx.AsyncClient(timeout=5) as client:
                    resp = await client.get(f"http://{host}:{ECP_PORT}/query/device-info")
                if resp.status_code == 200:
                    online = True
                    root = ET.fromstring(resp.text)
                    for tag in ("model-name", "power-mode", "friendly-device-name", "serial-number"):
                        el = root.find(tag)
                        if el is not None and el.text:
                            info[tag.replace("-", "_")] = el.text
            except (httpx.HTTPError, ET.ParseError):
                pass
            devices.append(Device(
                id=host,
                integration=self.id,
                name=info.get("friendly_device_name") or name,
                type=DeviceType.MEDIA_PLAYER,
                model=info.get("model_name"),
                online=online,
                state={"power": "on" if info.get("power_mode") == "PowerOn" else "off"},
                capabilities=["key"] + [f"key:{k}" for k in KEYS],
                attributes={"host": host, **info},
            ))

        for cam in self.config.get("cameras", []) or []:
            name = cam.get("name", "Roku Camera")
            devices.append(Device(
                id=cam.get("id") or name.lower().replace(" ", "-"),
                integration=self.id,
                name=name,
                type=DeviceType.CAMERA,
                online=bool(cam.get("hls_url") or cam.get("mjpeg_url") or cam.get("snapshot_url")),
                stream=StreamInfo(
                    hls=cam.get("hls_url"),
                    webrtc=cam.get("webrtc_url"),
                    mjpeg=cam.get("mjpeg_url"),
                    snapshot=cam.get("snapshot_url"),
                    rtsp=cam.get("rtsp_url"),
                ),
                attributes={"note": "Roku Smart Home cameras have no public API; "
                                    "stream is provided by your configured source."},
            ))

        return devices

    async def send_command(self, device_id: str, command: str, params: dict[str, Any]) -> None:
        # Only LAN (ECP) devices are controllable.
        hosts = [d.get("host") for d in self.config.get("devices", []) or []]
        if device_id not in hosts:
            raise IntegrationError("This Roku device is view-only")
        key = params.get("key") if command == "key" else command.removeprefix("key:")
        key_name = KEYS.get(key or "", None)
        if not key_name:
            raise IntegrationError(f"Unknown Roku key '{key}'")
        try:
            async with httpx.AsyncClient(timeout=5) as client:
                resp = await client.post(f"http://{device_id}:{ECP_PORT}/keypress/{key_name}")
            if resp.status_code >= 400:
                raise IntegrationError(f"Roku returned {resp.status_code}")
        except httpx.HTTPError as exc:
            raise IntegrationError(f"Could not reach Roku at {device_id}: {exc}") from exc
