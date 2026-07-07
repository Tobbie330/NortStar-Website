"""Hubspace (Home Depot) lighting integration.

Hubspace has no official public API. This adapter speaks to the same Afero
cloud endpoints the Hubspace mobile app uses — the approach proven by the
Home Assistant community integration (jdeath/Hubspace-Homeassistant and the
aiohubspace library):

  1. Keycloak login (accounts.hubspaceconnect.com, realm `thd`) using the
     OAuth2 authorization-code + PKCE flow with client_id `hubspace_android`.
  2. Device list from  GET api2.afero.net/v1/accounts/{acct}/metadevices?expansions=state
  3. Control via      PUT api2.afero.net/v1/accounts/{acct}/metadevices/{id}/state

Environment variables:
  HUBSPACE_EMAIL, HUBSPACE_PASSWORD
"""
from __future__ import annotations

import asyncio
import base64
import hashlib
import re
import secrets
import time
from typing import Any
from urllib.parse import parse_qs, urlsplit

import httpx

from ..config import env
from ..models import Device, DeviceType
from .base import Integration, IntegrationError

AUTH_HOST = "https://accounts.hubspaceconnect.com"
AUTH_REALM = f"{AUTH_HOST}/auth/realms/thd/protocol/openid-connect"
API_HOST = "https://api2.afero.net"
CLIENT_ID = "hubspace_android"
REDIRECT_URI = "hubspace-app://loginredirect"
USER_AGENT = "Dart/2.15 (dart:io)"

CONTROLLABLE_CLASSES = {
    "light": DeviceType.LIGHT,
    "switch": DeviceType.SWITCH,
    "power-outlet": DeviceType.PLUG,
    "fan": DeviceType.OTHER,
}


class HubspaceIntegration(Integration):
    id = "hubspace"
    name = "Hubspace"

    def __init__(self, config: dict[str, Any]):
        super().__init__(config)
        self._refresh_token: str | None = None
        self._access_token: str | None = None
        self._token_expiry: float = 0
        self._account_id: str | None = None
        self._lock = asyncio.Lock()

    async def start(self) -> None:
        if not env("HUBSPACE_EMAIL") or not env("HUBSPACE_PASSWORD"):
            raise IntegrationError("Set HUBSPACE_EMAIL and HUBSPACE_PASSWORD")
        await self._login()
        await self._load_account_id()

    async def _login(self) -> None:
        verifier = secrets.token_urlsafe(48)[:64]
        challenge = base64.urlsafe_b64encode(
            hashlib.sha256(verifier.encode()).digest()
        ).decode().rstrip("=")

        async with httpx.AsyncClient(follow_redirects=False, timeout=30) as client:
            # 1. Fetch the Keycloak login page to get the form action URL.
            resp = await client.get(
                f"{AUTH_REALM}/auth",
                params={
                    "response_type": "code",
                    "client_id": CLIENT_ID,
                    "redirect_uri": REDIRECT_URI,
                    "code_challenge": challenge,
                    "code_challenge_method": "S256",
                    "scope": "openid offline_access",
                },
                headers={"user-agent": USER_AGENT},
            )
            match = re.search(r'action\s*=\s*"([^"]+)"', resp.text)
            if not match:
                raise IntegrationError("Hubspace login page did not contain a login form")
            action_url = match.group(1).replace("&amp;", "&")

            # 2. Submit credentials; Keycloak answers with a redirect carrying ?code=
            resp = await client.post(
                action_url,
                data={
                    "username": env("HUBSPACE_EMAIL"),
                    "password": env("HUBSPACE_PASSWORD"),
                    "credentialId": "",
                },
                headers={"user-agent": USER_AGENT},
                cookies=resp.cookies,
            )
            location = resp.headers.get("location", "")
            code = parse_qs(urlsplit(location).query).get("code", [None])[0]
            if not code:
                raise IntegrationError("Hubspace login failed — check email/password")

            # 3. Exchange the code for tokens.
            resp = await client.post(
                f"{AUTH_REALM}/token",
                data={
                    "grant_type": "authorization_code",
                    "code": code,
                    "redirect_uri": REDIRECT_URI,
                    "code_verifier": verifier,
                    "client_id": CLIENT_ID,
                },
                headers={"user-agent": USER_AGENT},
            )
            resp.raise_for_status()
            tokens = resp.json()
            self._refresh_token = tokens["refresh_token"]
            self._access_token = tokens["access_token"]
            self._token_expiry = time.time() + int(tokens.get("expires_in", 300)) - 30

    async def _get_token(self) -> str:
        async with self._lock:
            if self._access_token and time.time() < self._token_expiry:
                return self._access_token
            if not self._refresh_token:
                await self._login()
                return self._access_token  # type: ignore[return-value]
            async with httpx.AsyncClient(timeout=30) as client:
                resp = await client.post(
                    f"{AUTH_REALM}/token",
                    data={
                        "grant_type": "refresh_token",
                        "refresh_token": self._refresh_token,
                        "scope": "openid email offline_access profile",
                        "client_id": CLIENT_ID,
                    },
                    headers={"user-agent": USER_AGENT},
                )
            if resp.status_code != 200:
                await self._login()
                return self._access_token  # type: ignore[return-value]
            tokens = resp.json()
            self._access_token = tokens["access_token"]
            self._refresh_token = tokens.get("refresh_token", self._refresh_token)
            self._token_expiry = time.time() + int(tokens.get("expires_in", 300)) - 30
            return self._access_token

    async def _api(self, method: str, path: str, **kwargs) -> Any:
        token = await self._get_token()
        headers = {
            "user-agent": USER_AGENT,
            "authorization": f"Bearer {token}",
            "accept-encoding": "gzip",
        }
        async with httpx.AsyncClient(timeout=30) as client:
            resp = await client.request(method, f"{API_HOST}{path}", headers=headers, **kwargs)
        if resp.status_code >= 400:
            raise IntegrationError(f"Hubspace API error {resp.status_code}: {resp.text[:200]}")
        return resp.json() if resp.text else None

    async def _load_account_id(self) -> None:
        me = await self._api("GET", "/v1/users/me")
        try:
            self._account_id = me["accountAccess"][0]["account"]["accountId"]
        except (KeyError, IndexError) as exc:
            raise IntegrationError("Could not read Hubspace account id") from exc

    @staticmethod
    def _state_map(values: list[dict]) -> dict[str, Any]:
        state: dict[str, Any] = {}
        for item in values or []:
            fc = item.get("functionClass")
            if fc in ("power", "brightness", "color-temperature", "color-mode", "fan-speed", "toggle"):
                key = fc.replace("-", "_")
                state[key] = item.get("value")
        return state

    async def get_devices(self) -> list[Device]:
        if not self._account_id:
            await self._load_account_id()
        data = await self._api(
            "GET", f"/v1/accounts/{self._account_id}/metadevices", params={"expansions": "state"}
        )
        devices: list[Device] = []
        for item in data:
            if item.get("typeId") != "metadevice.device":
                continue
            desc = (item.get("description") or {}).get("device", {})
            device_class = desc.get("deviceClass", "")
            dtype = CONTROLLABLE_CLASSES.get(device_class)
            values = (item.get("state") or {}).get("values", [])
            state = self._state_map(values)
            capabilities = []
            if any(v.get("functionClass") == "power" for v in values):
                capabilities += ["turn_on", "turn_off"]
            if any(v.get("functionClass") == "brightness" for v in values):
                capabilities.append("set_brightness")
            if any(v.get("functionClass") == "color-temperature" for v in values):
                capabilities.append("set_color_temp")
            devices.append(Device(
                id=item["id"],
                integration=self.id,
                name=item.get("friendlyName") or desc.get("friendlyName") or "Hubspace device",
                type=dtype or DeviceType.OTHER,
                model=desc.get("model"),
                online=True,
                state=state,
                capabilities=capabilities,
                attributes={"device_class": device_class},
            ))
        return devices

    async def _set_state(self, device_id: str, values: list[dict]) -> None:
        now_ms = int(time.time() * 1000)
        for v in values:
            v.setdefault("lastUpdateTime", now_ms)
        await self._api(
            "PUT",
            f"/v1/accounts/{self._account_id}/metadevices/{device_id}/state",
            json={"metadeviceId": device_id, "values": values},
        )

    async def _function_instance(self, device_id: str, function_class: str) -> str | None:
        """Some Hubspace models require the functionInstance from current state."""
        data = await self._api(
            "GET", f"/v1/accounts/{self._account_id}/metadevices/{device_id}/state"
        )
        for item in (data or {}).get("values", []):
            if item.get("functionClass") == function_class:
                return item.get("functionInstance")
        return None

    async def send_command(self, device_id: str, command: str, params: dict[str, Any]) -> None:
        if command in ("turn_on", "turn_off"):
            instance = await self._function_instance(device_id, "power")
            value = {"functionClass": "power", "value": "on" if command == "turn_on" else "off"}
            if instance:
                value["functionInstance"] = instance
            await self._set_state(device_id, [value])
        elif command == "set_brightness":
            await self._set_state(device_id, [{
                "functionClass": "brightness",
                "value": int(params.get("brightness", 100)),
            }])
        elif command == "set_color_temp":
            instance = await self._function_instance(device_id, "color-temperature")
            value = {"functionClass": "color-temperature", "value": str(params.get("color_temp", "3000"))}
            if instance:
                value["functionInstance"] = instance
            await self._set_state(device_id, [value])
        else:
            raise IntegrationError(f"Unsupported Hubspace command '{command}'")
