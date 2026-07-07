"""Simple single-household auth.

The dashboard is protected by one password (HUB_PASSWORD). A successful
login sets a signed, expiring session cookie. Good enough for a LAN /
personal deployment — if you ever expose this to the internet, put it
behind HTTPS (reverse proxy) and use a strong password.

Set HUB_PASSWORD in .env. If it is unset, auth is DISABLED and a warning is
logged (convenient for first run on a trusted network).
"""
from __future__ import annotations

import hashlib
import hmac
import logging
import os
import secrets
import time

from .config import env

log = logging.getLogger("homehub.security")

COOKIE_NAME = "homehub_session"
SESSION_TTL = 30 * 24 * 3600  # 30 days

# Signing secret: persisted so sessions survive restarts.
_SECRET_FILE = os.environ.get("HUB_SECRET_FILE", "/tmp/homehub_secret")


def _secret() -> bytes:
    explicit = env("HUB_SECRET")
    if explicit:
        return explicit.encode()
    try:
        with open(_SECRET_FILE, "rb") as fh:
            return fh.read()
    except FileNotFoundError:
        secret = secrets.token_bytes(32)
        try:
            fd = os.open(_SECRET_FILE, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
            with os.fdopen(fd, "wb") as fh:
                fh.write(secret)
        except FileExistsError:
            with open(_SECRET_FILE, "rb") as fh:
                return fh.read()
        return secret


def auth_enabled() -> bool:
    return bool(env("HUB_PASSWORD"))


def check_password(password: str) -> bool:
    expected = env("HUB_PASSWORD") or ""
    return hmac.compare_digest(password.encode(), expected.encode())


def make_token() -> str:
    expires = str(int(time.time()) + SESSION_TTL)
    sig = hmac.new(_secret(), expires.encode(), hashlib.sha256).hexdigest()
    return f"{expires}.{sig}"


def verify_token(token: str | None) -> bool:
    if not auth_enabled():
        return True
    if not token or "." not in token:
        return False
    expires, sig = token.rsplit(".", 1)
    expected = hmac.new(_secret(), expires.encode(), hashlib.sha256).hexdigest()
    if not hmac.compare_digest(sig, expected):
        return False
    try:
        return int(expires) > time.time()
    except ValueError:
        return False
