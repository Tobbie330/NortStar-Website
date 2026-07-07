"""Shared data models for Home Hub.

Every integration adapter normalizes its devices into these models so the
web dashboard and API can treat a Wyze bulb, a Hubspace light, and a demo
device exactly the same way.
"""
from __future__ import annotations

from enum import Enum
from typing import Any, Optional

from pydantic import BaseModel, Field


class DeviceType(str, Enum):
    CAMERA = "camera"
    LIGHT = "light"
    PLUG = "plug"
    SWITCH = "switch"
    SENSOR = "sensor"
    WEATHER_STATION = "weather_station"
    MEDIA_PLAYER = "media_player"
    OTHER = "other"


class StreamInfo(BaseModel):
    """URLs the browser can use to view a camera."""

    hls: Optional[str] = None       # HTTP Live Streaming (.m3u8) — plays via hls.js
    webrtc: Optional[str] = None    # WebRTC page (lowest latency)
    mjpeg: Optional[str] = None     # Motion JPEG stream
    snapshot: Optional[str] = None  # Still image URL, refreshed periodically
    rtsp: Optional[str] = None      # For external players (VLC etc.), not the browser


class Device(BaseModel):
    id: str                               # unique within its integration
    integration: str                      # adapter id, e.g. "wyze"
    name: str
    type: DeviceType = DeviceType.OTHER
    model: Optional[str] = None
    online: bool = True
    # Current state, e.g. {"power": "on", "brightness": 80, "temperature_f": 71.2}
    state: dict[str, Any] = Field(default_factory=dict)
    # Commands this device accepts, e.g. ["turn_on", "turn_off", "set_brightness"]
    capabilities: list[str] = Field(default_factory=list)
    stream: Optional[StreamInfo] = None
    # Free-form extras (battery, signal, firmware, room, ...)
    attributes: dict[str, Any] = Field(default_factory=dict)

    @property
    def uid(self) -> str:
        return f"{self.integration}:{self.id}"


class IntegrationStatus(BaseModel):
    id: str
    name: str
    enabled: bool
    connected: bool = False
    error: Optional[str] = None
    device_count: int = 0
    last_refresh: Optional[float] = None  # unix timestamp


class CommandRequest(BaseModel):
    command: str
    params: dict[str, Any] = Field(default_factory=dict)
