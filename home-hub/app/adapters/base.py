"""Base class every integration adapter implements.

To add a new integration later:
  1. Create app/adapters/<name>.py with a class extending Integration.
  2. Register it in app/adapters/__init__.py (ADAPTERS dict).
  3. Add an `integrations.<name>` section to config.yaml and any secrets
     to .env.
That's the whole process — the API and dashboard pick it up automatically.
"""
from __future__ import annotations

import abc
from typing import Any

from ..models import Device


class IntegrationError(Exception):
    """Raised by adapters for user-facing failures (bad credentials, etc.)."""


class Integration(abc.ABC):
    id: str = "base"
    name: str = "Base"

    def __init__(self, config: dict[str, Any]):
        self.config = config or {}

    async def start(self) -> None:
        """Connect / authenticate. Raise IntegrationError on failure."""

    async def stop(self) -> None:
        """Release any resources."""

    @abc.abstractmethod
    async def get_devices(self) -> list[Device]:
        """Return the current device list with fresh state."""

    async def send_command(self, device_id: str, command: str, params: dict[str, Any]) -> None:
        """Execute a command against a device. Override for controllable devices."""
        raise IntegrationError(f"{self.name} devices do not accept commands")
