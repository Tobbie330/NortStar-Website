"""Integration registry: owns adapter lifecycles and a device-state cache.

The registry starts every enabled adapter, polls each one on a timer, and
serves cached device snapshots to the API so the dashboard stays fast even
when a cloud service is slow. Commands are forwarded to the owning adapter
and that adapter is refreshed immediately afterwards.
"""
from __future__ import annotations

import asyncio
import logging
import time
from typing import Any

from .adapters import ADAPTERS
from .adapters.base import Integration, IntegrationError
from .models import Device, IntegrationStatus

log = logging.getLogger("homehub.registry")


class Registry:
    def __init__(self, config: dict[str, Any]):
        self.config = config
        self.refresh_seconds: int = int(config.get("hub", {}).get("refresh_seconds", 30))
        self.adapters: dict[str, Integration] = {}
        self.status: dict[str, IntegrationStatus] = {}
        self.devices: dict[str, list[Device]] = {}
        self._lock = asyncio.Lock()
        self._task: asyncio.Task | None = None

    async def start(self) -> None:
        integrations_cfg = self.config.get("integrations", {})
        for adapter_id, adapter_cls in ADAPTERS.items():
            cfg = integrations_cfg.get(adapter_id, {}) or {}
            enabled = bool(cfg.get("enabled", False))
            self.status[adapter_id] = IntegrationStatus(
                id=adapter_id, name=adapter_cls.name, enabled=enabled
            )
            if not enabled:
                continue
            adapter = adapter_cls(cfg)
            try:
                await adapter.start()
                self.adapters[adapter_id] = adapter
                self.status[adapter_id].connected = True
                log.info("Started integration: %s", adapter_id)
            except Exception as exc:  # keep the hub alive if one integration fails
                self.status[adapter_id].error = str(exc)
                log.exception("Failed to start integration %s", adapter_id)

        await self.refresh_all()
        self._task = asyncio.create_task(self._refresh_loop())

    async def stop(self) -> None:
        if self._task:
            self._task.cancel()
        for adapter in self.adapters.values():
            try:
                await adapter.stop()
            except Exception:
                pass

    async def _refresh_loop(self) -> None:
        while True:
            await asyncio.sleep(self.refresh_seconds)
            try:
                await self.refresh_all()
            except Exception:
                log.exception("Refresh loop error")

    async def refresh_all(self) -> None:
        await asyncio.gather(*(self.refresh(aid) for aid in list(self.adapters)))

    async def refresh(self, adapter_id: str) -> None:
        adapter = self.adapters.get(adapter_id)
        if not adapter:
            return
        status = self.status[adapter_id]
        try:
            devices = await adapter.get_devices()
            async with self._lock:
                self.devices[adapter_id] = devices
            status.connected = True
            status.error = None
            status.device_count = len(devices)
            status.last_refresh = time.time()
        except Exception as exc:
            status.connected = False
            status.error = str(exc)
            log.warning("Refresh failed for %s: %s", adapter_id, exc)

    def all_devices(self) -> list[Device]:
        out: list[Device] = []
        for devices in self.devices.values():
            out.extend(devices)
        return out

    def get_device(self, adapter_id: str, device_id: str) -> Device | None:
        for device in self.devices.get(adapter_id, []):
            if device.id == device_id:
                return device
        return None

    async def send_command(
        self, adapter_id: str, device_id: str, command: str, params: dict[str, Any]
    ) -> Device | None:
        adapter = self.adapters.get(adapter_id)
        if not adapter:
            raise IntegrationError(f"Integration '{adapter_id}' is not running")
        await adapter.send_command(device_id, command, params or {})
        await self.refresh(adapter_id)
        return self.get_device(adapter_id, device_id)
