"""Configuration loading for Home Hub.

Non-secret settings live in config.yaml (which integrations are enabled,
extra cameras, Roku device IPs, ...). Secrets (account passwords, API keys)
come from environment variables / the .env file so they never end up in git.
"""
from __future__ import annotations

import os
from pathlib import Path
from typing import Any

import yaml

CONFIG_PATH = Path(os.environ.get("HUB_CONFIG", Path(__file__).resolve().parent.parent / "config.yaml"))

DEFAULT_CONFIG: dict[str, Any] = {
    "hub": {
        "name": "Home Hub",
        "refresh_seconds": 30,
    },
    "integrations": {
        "demo": {"enabled": True},
        "wyze": {"enabled": False},
        "roku": {"enabled": False, "devices": [], "cameras": []},
        "lacrosse": {"enabled": False},
        "hubspace": {"enabled": False},
        "cameras": {"enabled": False, "cameras": []},
    },
}


def _deep_merge(base: dict, override: dict) -> dict:
    out = dict(base)
    for key, value in override.items():
        if isinstance(value, dict) and isinstance(out.get(key), dict):
            out[key] = _deep_merge(out[key], value)
        else:
            out[key] = value
    return out


def load_config() -> dict[str, Any]:
    config = DEFAULT_CONFIG
    if CONFIG_PATH.exists():
        with open(CONFIG_PATH) as fh:
            user_config = yaml.safe_load(fh) or {}
        config = _deep_merge(DEFAULT_CONFIG, user_config)
    return config


def env(name: str, default: str | None = None) -> str | None:
    value = os.environ.get(name, default)
    if value is not None:
        value = value.strip()
    return value or default
