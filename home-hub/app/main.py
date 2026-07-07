"""Home Hub — household management dashboard API.

Run locally:   uvicorn app.main:app --host 0.0.0.0 --port 8100
Run in Docker: docker compose up -d   (from the home-hub directory)
"""
from __future__ import annotations

import logging
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.responses import FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel

from . import security
from .adapters.base import IntegrationError
from .config import load_config
from .models import CommandRequest, DeviceType
from .registry import Registry

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(name)s %(levelname)s %(message)s")
log = logging.getLogger("homehub")

WEB_DIR = Path(__file__).resolve().parent.parent / "web"

config = load_config()
registry = Registry(config)


@asynccontextmanager
async def lifespan(_: FastAPI):
    if not security.auth_enabled():
        log.warning("HUB_PASSWORD is not set — the dashboard is UNPROTECTED. "
                    "Set it in .env before exposing this beyond your LAN.")
    await registry.start()
    yield
    await registry.stop()


app = FastAPI(title="Home Hub", lifespan=lifespan)


# ---------------------------------------------------------------- auth ----

class LoginRequest(BaseModel):
    password: str


@app.middleware("http")
async def auth_middleware(request: Request, call_next):
    path = request.url.path
    needs_auth = path.startswith("/api/") and path not in ("/api/login", "/api/auth-status")
    if needs_auth and not security.verify_token(request.cookies.get(security.COOKIE_NAME)):
        return JSONResponse({"detail": "Not authenticated"}, status_code=401)
    return await call_next(request)


@app.get("/api/auth-status")
async def auth_status(request: Request):
    return {
        "auth_required": security.auth_enabled(),
        "authenticated": security.verify_token(request.cookies.get(security.COOKIE_NAME)),
    }


@app.post("/api/login")
async def login(body: LoginRequest, response: Response):
    if not security.auth_enabled():
        return {"ok": True}
    if not security.check_password(body.password):
        raise HTTPException(status_code=401, detail="Wrong password")
    response.set_cookie(
        security.COOKIE_NAME, security.make_token(),
        max_age=security.SESSION_TTL, httponly=True, samesite="lax",
    )
    return {"ok": True}


@app.post("/api/logout")
async def logout(response: Response):
    response.delete_cookie(security.COOKIE_NAME)
    return {"ok": True}


# ------------------------------------------------------------- devices ----

@app.get("/api/summary")
async def summary():
    devices = registry.all_devices()
    return {
        "hub_name": config.get("hub", {}).get("name", "Home Hub"),
        "devices": [d.model_dump() for d in devices],
        "integrations": [s.model_dump() for s in registry.status.values()],
        "counts": {
            "cameras": sum(1 for d in devices if d.type == DeviceType.CAMERA),
            "lights": sum(1 for d in devices if d.type in (DeviceType.LIGHT, DeviceType.SWITCH)),
            "weather": sum(1 for d in devices if d.type == DeviceType.WEATHER_STATION),
            "other": sum(1 for d in devices if d.type in (DeviceType.PLUG, DeviceType.MEDIA_PLAYER, DeviceType.SENSOR, DeviceType.OTHER)),
        },
    }


@app.get("/api/devices")
async def devices():
    return [d.model_dump() for d in registry.all_devices()]


@app.post("/api/devices/{integration}/{device_id}/command")
async def device_command(integration: str, device_id: str, body: CommandRequest):
    try:
        device = await registry.send_command(integration, device_id, body.command, body.params)
    except IntegrationError as exc:
        raise HTTPException(status_code=400, detail=str(exc))
    return {"ok": True, "device": device.model_dump() if device else None}


@app.post("/api/refresh")
async def refresh():
    await registry.refresh_all()
    return {"ok": True}


@app.get("/api/integrations")
async def integrations():
    return [s.model_dump() for s in registry.status.values()]


# --------------------------------------------------- demo camera images ----

_DEMO_SCENES = {
    "front-door": ("#1c2a3f", "#2f4a6b", "Front Door"),
    "backyard": ("#1f3325", "#35573f", "Backyard"),
}


@app.get("/api/demo/snapshot/{scene}")
async def demo_snapshot(scene: str):
    sky, ground, label = _DEMO_SCENES.get(scene, ("#222", "#333", "Demo"))
    svg = f"""<svg xmlns='http://www.w3.org/2000/svg' width='640' height='360'>
      <rect width='640' height='220' fill='{sky}'/>
      <rect y='220' width='640' height='140' fill='{ground}'/>
      <circle cx='540' cy='60' r='28' fill='#e8e3c9' opacity='0.85'/>
      <text x='20' y='340' fill='#ffffff' opacity='0.7' font-family='sans-serif' font-size='20'>{label} — demo feed</text>
      <text x='20' y='34' fill='#ff5555' font-family='monospace' font-size='16'>● REC</text>
    </svg>"""
    return Response(content=svg, media_type="image/svg+xml",
                    headers={"Cache-Control": "no-store"})


# ------------------------------------------------------------ frontend ----

@app.get("/")
async def index():
    return FileResponse(WEB_DIR / "index.html")


app.mount("/static", StaticFiles(directory=WEB_DIR), name="static")
