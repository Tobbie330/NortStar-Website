"""Demo integration — sample devices so the dashboard is explorable before
any real accounts are connected. Disable it in config.yaml once your real
integrations are set up (integrations.demo.enabled: false).

State changes are kept in memory so toggles and sliders actually respond.
"""
from __future__ import annotations

import math
import time
from typing import Any

from ..models import Device, DeviceType, StreamInfo
from .base import Integration, IntegrationError


class DemoIntegration(Integration):
    id = "demo"
    name = "Demo devices"

    def __init__(self, config: dict[str, Any]):
        super().__init__(config)
        self._lights: dict[str, dict[str, Any]] = {
            "demo-light-1": {"name": "Kitchen Lights", "power": "on", "brightness": 80, "color_temp": 3000},
            "demo-light-2": {"name": "Porch Light", "power": "off", "brightness": 100, "color_temp": 2700},
        }
        self._plug = {"power": "on"}

    async def get_devices(self) -> list[Device]:
        # Weather values drift slowly so the dashboard looks alive.
        t = time.time() / 900
        devices = [
            Device(
                id="demo-cam-1", integration=self.id, name="Front Door (demo)",
                type=DeviceType.CAMERA, online=True,
                capabilities=["turn_on", "turn_off"],
                state={"power": "on"},
                stream=StreamInfo(snapshot="/api/demo/snapshot/front-door"),
            ),
            Device(
                id="demo-cam-2", integration=self.id, name="Backyard (demo)",
                type=DeviceType.CAMERA, online=True,
                capabilities=["turn_on", "turn_off"],
                state={"power": "on"},
                stream=StreamInfo(snapshot="/api/demo/snapshot/backyard"),
            ),
            Device(
                id="demo-weather-1", integration=self.id, name="Backyard Station (demo)",
                type=DeviceType.WEATHER_STATION, online=True,
                state={
                    "temperature_f": round(72 + 6 * math.sin(t), 1),
                    "humidity_pct": round(48 + 10 * math.cos(t / 2), 1),
                    "wind_mph": round(abs(7 * math.sin(t / 3)), 1),
                    "pressure_inhg": round(29.9 + 0.2 * math.sin(t / 5), 2),
                    "rain_in": 0.0,
                },
                attributes={"location": "Demo Home"},
            ),
            Device(
                id="demo-plug-1", integration=self.id, name="Garage Heater Plug (demo)",
                type=DeviceType.PLUG, online=True,
                capabilities=["turn_on", "turn_off"],
                state=dict(self._plug),
            ),
        ]
        for dev_id, light in self._lights.items():
            devices.append(Device(
                id=dev_id, integration=self.id, name=f"{light['name']} (demo)",
                type=DeviceType.LIGHT, online=True,
                capabilities=["turn_on", "turn_off", "set_brightness", "set_color_temp"],
                state={k: v for k, v in light.items() if k != "name"},
            ))
        return devices

    async def send_command(self, device_id: str, command: str, params: dict[str, Any]) -> None:
        if device_id in self._lights:
            target = self._lights[device_id]
        elif device_id == "demo-plug-1":
            target = self._plug
        elif device_id.startswith("demo-cam"):
            return  # accept and ignore
        else:
            raise IntegrationError(f"Unknown demo device {device_id}")

        if command == "turn_on":
            target["power"] = "on"
        elif command == "turn_off":
            target["power"] = "off"
        elif command == "set_brightness":
            target["brightness"] = max(1, min(100, int(params.get("brightness", 100))))
        elif command == "set_color_temp":
            target["color_temp"] = int(params.get("color_temp", 3000))
        else:
            raise IntegrationError(f"Unsupported demo command '{command}'")
