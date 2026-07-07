# 🏠 Home Hub — Household Management System

A self-hosted web dashboard that puts your whole house on one screen:

| What | How |
|---|---|
| **Wyze cameras, bulbs, plugs** | Official Wyze API-key login for control; live video via the bundled [wyze-bridge](https://github.com/mrlt8/docker-wyze-bridge) container (HLS/WebRTC/RTSP) |
| **Roku TVs & streaming players** | Roku's documented ECP protocol on your LAN — power, remote keys, status |
| **Roku Smart Home cameras** | ⚠️ Roku publishes **no API** for these — see [the Roku camera situation](#the-roku-camera-situation) for your options |
| **La Crosse View weather stations** | Same cloud API the La Crosse View phone app uses — temperature, humidity, wind, rain, pressure |
| **Hubspace lighting** | Same Afero cloud API the Hubspace phone app uses — on/off, brightness, color temperature |
| **Any other camera** | Generic integration: give it an HLS / MJPEG / snapshot URL |
| **Future brands** | Plugin architecture — one Python file per integration ([guide below](#adding-a-new-integration)) |

It runs alongside the North Star WordPress sites in this repo — different
containers, different ports (dashboard on **8100**; WordPress uses
8080/8090 etc.), zero interference.

---

## Quick start

```bash
cd home-hub
cp .env.example .env               # fill in the accounts you want connected
cp config.example.yaml config.yaml # enable integrations, set device IPs
docker compose up -d               # dashboard only
# or, to also get live Wyze camera video:
docker compose --profile wyze up -d
```

Open **http://\<your-server\>:8100** and sign in with the `HUB_PASSWORD` you
set in `.env`. Out of the box the **demo** integration is on, so you can
click around the dashboard (cameras, lights, weather) before connecting
anything real. Turn it off in `config.yaml` when you're done exploring.

Without Docker (needs Python 3.11+):

```bash
pip install -r requirements.txt
uvicorn app.main:app --host 0.0.0.0 --port 8100
```

---

## Setting up each integration

### Wyze (cameras, bulbs, plugs)

1. Create an API key at <https://developer-api-console.wyze.com/#/apikey/view>
   (sign in with your Wyze account — the key is free).
2. In `.env`, set `WYZE_EMAIL`, `WYZE_PASSWORD`, `WYZE_KEY_ID`, `WYZE_API_KEY`.
3. In `config.yaml`, set `wyze.enabled: true`.
4. **For live camera video**, start the bridge container:
   `docker compose --profile wyze up -d`, and set
   `WYZE_BRIDGE_URL=http://<your-server-LAN-IP>:8101` in `.env` so the
   dashboard knows where the browser can reach the streams.
   (Also uncomment `WB_IP` in docker-compose.yml for WebRTC.)

Control (on/off, brightness, color) works even without the bridge; the
bridge only provides the video feeds.

### Roku TVs / streaming players

Add each device's IP in `config.yaml` (give them static/reserved IPs in
your router first):

```yaml
roku:
  enabled: true
  devices:
    - name: Living Room TV
      host: 192.168.1.40
```

You get an on-screen remote (arrows/OK/home/volume/power) powered by Roku's
official External Control Protocol. Note: Home Hub must be on the **same
network** as the Rokus, and "Control by mobile apps" must be enabled on the
Roku (Settings → System → Advanced → Control by mobile apps → Enabled).

### The Roku camera situation

Roku Smart Home cameras are Wyze-manufactured hardware running Roku
firmware against Roku's **closed** cloud — there is no public API, no RTSP
option in the app, and the wyze-bridge can't log into them. Honest options,
best first:

1. **Reflash to Wyze firmware.** Because the hardware is a Wyze Cam
   (e.g. the Roku Indoor Camera SE is a Wyze Cam v3), the well-known SD-card
   flash procedure converts it into a genuine Wyze camera. It then shows up
   under the Wyze integration with full control *and* live video through
   the bridge. This is the route most self-hosters take. (It leaves the
   Roku app behind, and carries the usual "flashing firmware" caveats.)
2. **Restream from an NVR/other source.** If the camera's video already
   lands somewhere you control (an NVR, go2rtc, Scrypted, etc.), point
   `roku.cameras` in `config.yaml` at that HLS/MJPEG/snapshot URL and it
   appears on the Cameras tab like any other feed.
3. **Wait for Roku.** The adapter is already structured so that if Roku
   ever opens an API, support drops into `app/adapters/roku.py` without
   touching anything else.

### La Crosse View (weather stations)

1. Make sure your station is registered in the **La Crosse View** phone app
   (that's what links it to their cloud).
2. In `.env`, set `LACROSSE_EMAIL` / `LACROSSE_PASSWORD`.
3. In `config.yaml`, set `lacrosse.enabled: true`.

Every sensor on your account appears on the Weather tab with US units
(°F, mph, inches, inHg). Uses the same community-documented cloud API as
Home Assistant's `lacrosse_view` integration.

### Hubspace (lighting)

1. In `.env`, set `HUBSPACE_EMAIL` / `HUBSPACE_PASSWORD` (same login as the
   Hubspace phone app).
2. In `config.yaml`, set `hubspace.enabled: true`.

Lights, plugs, switches and fans on your account appear with on/off,
brightness, and color-temperature controls. Uses the same Afero cloud
endpoints as the Hubspace app (the approach proven by the Home Assistant
community integration).

### Generic cameras

Any camera that can produce a browser-playable URL — directly (MJPEG) or
through a restreamer like [go2rtc](https://github.com/AlexxIT/go2rtc)
(which converts RTSP/ONVIF to HLS/WebRTC) — goes under `cameras:` in
`config.yaml`.

---

## Security notes

- The dashboard is protected by `HUB_PASSWORD` (signed session cookie,
  30-day expiry). If you leave it empty, auth is **off** — fine on a
  trusted LAN, not otherwise.
- If you want access from outside the house, don't port-forward 8100
  directly. Put it behind HTTPS (Caddy/nginx/Cloudflare Tunnel) or —
  simplest and safest — a VPN like Tailscale or WireGuard.
- `.env` and `config.yaml` are gitignored; your passwords never enter git.
- The wyze-bridge ports (8101/8554/8888/8889) serve unauthenticated video
  by default (`WB_AUTH: "false"`) — keep them LAN-only, or set
  `WB_AUTH: "true"` + `WB_PASSWORD` in docker-compose.yml.

## A note on the unofficial APIs

Wyze's API-key login is official. La Crosse View and Hubspace have **no
official public APIs** — this app uses the same cloud endpoints their own
mobile apps use, as reverse-engineered and battle-tested by the Home
Assistant community. That means they work today but a vendor update could
break them until the adapter is updated; the Integrations tab shows a
clear error state when that happens, and each adapter is a single file to
fix.

---

## Architecture

```
home-hub/
├── docker-compose.yml       # homehub + optional wyze-bridge
├── config.yaml              # which integrations are on, device IPs (copy from config.example.yaml)
├── .env                     # secrets (copy from .env.example)
├── app/
│   ├── main.py              # FastAPI app: REST API + serves the dashboard
│   ├── registry.py          # starts adapters, polls state, routes commands
│   ├── models.py            # normalized Device / StreamInfo / status models
│   ├── security.py          # password login, signed session cookies
│   ├── config.py            # config.yaml + .env loading
│   └── adapters/
│       ├── base.py          # the Integration interface
│       ├── wyze.py          # Wyze cloud + wyze-bridge streams
│       ├── roku.py          # Roku ECP devices + camera stream slots
│       ├── lacrosse_view.py # La Crosse View cloud
│       ├── hubspace.py      # Hubspace/Afero cloud
│       ├── cameras.py       # generic URL-based cameras
│       └── demo.py          # sample devices for first run
└── web/                     # the dashboard (vanilla HTML/CSS/JS)
```

Every adapter normalizes its gadgets into the same `Device` model, so the
dashboard doesn't know or care what brand anything is. The registry polls
each integration every `refresh_seconds` and caches state, so the UI stays
fast even when a vendor cloud is slow; one integration failing never takes
down the others (its error just shows on the Integrations tab).

### REST API

Everything the dashboard does is plain JSON you can script against:

| Endpoint | What |
|---|---|
| `GET /api/summary` | all devices + integration status + counts |
| `GET /api/devices` | all devices |
| `POST /api/devices/{integration}/{id}/command` | `{"command": "turn_on"}`, `{"command": "set_brightness", "params": {"brightness": 60}}`, … |
| `POST /api/refresh` | re-poll every integration now |
| `GET /api/integrations` | connection status per integration |

## Adding a new integration

1. Create `app/adapters/mybrand.py`:

```python
from ..models import Device, DeviceType
from .base import Integration, IntegrationError

class MyBrandIntegration(Integration):
    id = "mybrand"
    name = "My Brand"

    async def start(self):
        ...  # authenticate; raise IntegrationError with a helpful message on failure

    async def get_devices(self) -> list[Device]:
        ...  # return normalized Device objects

    async def send_command(self, device_id, command, params):
        ...  # only needed for controllable devices
```

2. Register it in `app/adapters/__init__.py` (add the class to `ADAPTERS`).
3. Add `mybrand: {enabled: true}` under `integrations:` in `config.yaml`,
   and any secrets to `.env`.

Rebuild (`docker compose up -d --build`) and the new devices appear on the
dashboard automatically — device types map to tabs (cameras → Cameras,
lights → Lighting, weather stations → Weather, everything else → Devices).
