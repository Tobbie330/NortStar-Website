"""La Crosse View weather station integration.

La Crosse Technology has no official public API, but the La Crosse View
mobile app talks to a stable cloud API that the Home Assistant
`lacrosse_view` integration has used for years. This adapter implements the
same flow:

  1. Sign in through Google Identity Toolkit (the app's auth backend) to get
     an ID token.
  2. List locations:  lax-gateway.appspot.com/_ah/api/lacrosseClient/v1.1/active-user/locations
  3. List sensors per location: .../location/{id}/sensorAssociations
  4. Read the latest values from the ingestion feed at
     ingv2.lacrossetechnology.com.

Environment variables:
  LACROSSE_EMAIL, LACROSSE_PASSWORD  - your La Crosse View account
"""
from __future__ import annotations

import time
from typing import Any

import httpx

from ..config import env
from ..models import Device, DeviceType
from .base import Integration, IntegrationError

# Public API key embedded in the La Crosse View app (same one the Home
# Assistant integration uses). It only works for La Crosse's auth tenant.
LOGIN_URL = (
    "https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword"
    "?key=AIzaSyD-Uo0hkRIeDYJhyyIg-TvAv8HhExARIO4"
)
GATEWAY = "https://lax-gateway.appspot.com/_ah/api/lacrosseClient/v1.1"
FEED = "https://ingv2.lacrossetechnology.com/api/v1.1"

# Friendly names + unit conversion for common La Crosse field names.
FIELD_INFO = {
    "Temperature": ("temperature_f", lambda c: round(c * 9 / 5 + 32, 1)),
    "Humidity": ("humidity_pct", lambda v: round(v, 1)),
    "WindSpeed": ("wind_mph", lambda kmh: round(kmh * 0.621371, 1)),
    "WindHeading": ("wind_heading_deg", lambda v: round(v)),
    "Rain": ("rain_in", lambda mm: round(mm / 25.4, 2)),
    "RainRate": ("rain_rate_in_hr", lambda mm: round(mm / 25.4, 2)),
    "BarometricPressure": ("pressure_inhg", lambda hpa: round(hpa * 0.02953, 2)),
    "HeatIndex": ("heat_index_f", lambda c: round(c * 9 / 5 + 32, 1)),
    "WetDry": ("wet", lambda v: bool(v)),
    "FeelsLike": ("feels_like_f", lambda c: round(c * 9 / 5 + 32, 1)),
    "DewPoint": ("dew_point_f", lambda c: round(c * 9 / 5 + 32, 1)),
}


class LaCrosseIntegration(Integration):
    id = "lacrosse"
    name = "La Crosse View"

    def __init__(self, config: dict[str, Any]):
        super().__init__(config)
        self._token: str | None = None
        self._token_time: float = 0

    async def start(self) -> None:
        if not env("LACROSSE_EMAIL") or not env("LACROSSE_PASSWORD"):
            raise IntegrationError("Set LACROSSE_EMAIL and LACROSSE_PASSWORD")
        await self._login()

    async def _login(self) -> None:
        async with httpx.AsyncClient(timeout=30) as client:
            resp = await client.post(LOGIN_URL, json={
                "email": env("LACROSSE_EMAIL"),
                "password": env("LACROSSE_PASSWORD"),
                "returnSecureToken": True,
            })
        if resp.status_code != 200:
            raise IntegrationError("La Crosse View login failed — check email/password")
        self._token = resp.json()["idToken"]
        self._token_time = time.time()

    async def _get(self, url: str, params: dict | None = None) -> Any:
        if not self._token or time.time() - self._token_time > 45 * 60:
            await self._login()
        headers = {"Authorization": f"Bearer {self._token}"}
        async with httpx.AsyncClient(timeout=30) as client:
            resp = await client.get(url, headers=headers, params=params)
        if resp.status_code == 401:
            await self._login()
            headers = {"Authorization": f"Bearer {self._token}"}
            async with httpx.AsyncClient(timeout=30) as client:
                resp = await client.get(url, headers=headers, params=params)
        if resp.status_code >= 400:
            raise IntegrationError(f"La Crosse API error {resp.status_code}")
        return resp.json()

    async def get_devices(self) -> list[Device]:
        devices: list[Device] = []
        locations = (await self._get(f"{GATEWAY}/active-user/locations")).get("items", [])
        for location in locations:
            loc_id, loc_name = location.get("id"), location.get("name", "Home")
            sensors = (await self._get(
                f"{GATEWAY}/active-user/location/{loc_id}/sensorAssociations",
                params={"prettyPrint": "false"},
            )).get("items", [])
            for assoc in sensors:
                sensor = assoc.get("sensor", {})
                fields = [f for f in sensor.get("fields", []) if f != "NotSupported"]
                assoc_id = assoc.get("id")
                name = assoc.get("name") or sensor.get("type", {}).get("name") or "Sensor"
                state = await self._latest_values(assoc_id, fields) if fields else {}
                devices.append(Device(
                    id=str(assoc_id),
                    integration=self.id,
                    name=name,
                    type=DeviceType.WEATHER_STATION,
                    model=sensor.get("type", {}).get("name"),
                    online=bool(state),
                    state=state,
                    attributes={"location": loc_name, "fields": fields},
                ))
        return devices

    async def _latest_values(self, assoc_id: str, fields: list[str]) -> dict[str, Any]:
        now = int(time.time())
        data = await self._get(
            f"{FEED}/active-user/device-association/ref.user-device.{assoc_id}/feed",
            params={
                "fields": ",".join(fields),
                "tz": "America/Chicago",
                "from": now - 2 * 3600,
                "to": now,
                "aggregates": "ai.ticks.1",
                "types": "spot",
            },
        )
        state: dict[str, Any] = {}
        try:
            field_data = data[f"ref.user-device.{assoc_id}"]["ai.ticks.1"]["fields"]
        except (KeyError, TypeError):
            return state
        for field_name, series in field_data.items():
            values = series.get("values") or []
            if not values:
                continue
            raw = values[-1].get("s")
            if raw is None:
                continue
            key, convert = FIELD_INFO.get(field_name, (field_name.lower(), lambda v: v))
            try:
                state[key] = convert(float(raw))
            except (TypeError, ValueError):
                state[key] = raw
        return state
