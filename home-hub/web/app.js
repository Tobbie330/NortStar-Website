/* Home Hub dashboard */
"use strict";

const REFRESH_MS = 15000;
const SNAPSHOT_MS = 10000;

let summary = { devices: [], integrations: [], counts: {}, hub_name: "Home Hub" };
let activeTab = "overview";
let interacting = false;        // true while a slider is being dragged
let camKey = "";                // fingerprint of camera list, to avoid rebuilding <video>s
const hlsPlayers = [];

const $ = (sel, el = document) => el.querySelector(sel);
const $$ = (sel, el = document) => [...el.querySelectorAll(sel)];

/* ---------------------------------------------------------------- auth */

async function checkAuth() {
  const res = await fetch("/api/auth-status");
  const st = await res.json();
  if (st.auth_required && !st.authenticated) {
    $("#login-screen").classList.remove("hidden");
    $("#app").classList.add("hidden");
    return false;
  }
  $("#login-screen").classList.add("hidden");
  $("#app").classList.remove("hidden");
  $("#logout-btn").classList.toggle("hidden", !st.auth_required);
  return true;
}

$("#login-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const res = await fetch("/api/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ password: $("#login-password").value }),
  });
  if (res.ok) {
    $("#login-error").classList.add("hidden");
    await checkAuth();
    await refresh(true);
  } else {
    $("#login-error").classList.remove("hidden");
  }
});

$("#logout-btn").addEventListener("click", async () => {
  await fetch("/api/logout", { method: "POST" });
  location.reload();
});

/* ------------------------------------------------------------- fetching */

async function refresh(force = false) {
  if (interacting && !force) return;
  try {
    const res = await fetch("/api/summary");
    if (res.status === 401) { await checkAuth(); return; }
    summary = await res.json();
    $("#hub-name").textContent = summary.hub_name;
    render();
    const connected = summary.integrations.filter((i) => i.enabled && i.connected).length;
    const enabled = summary.integrations.filter((i) => i.enabled).length;
    $("#status-line").textContent =
      `${summary.devices.length} devices · ${connected}/${enabled} integrations connected · updated ${new Date().toLocaleTimeString()}`;
  } catch (err) {
    $("#status-line").textContent = "Connection lost — retrying…";
  }
}

async function sendCommand(dev, command, params = {}) {
  try {
    const res = await fetch(`/api/devices/${dev.integration}/${encodeURIComponent(dev.id)}/command`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ command, params }),
    });
    const body = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(body.detail || `HTTP ${res.status}`);
    await refresh(true);
  } catch (err) {
    toast(`${dev.name}: ${err.message}`, true);
    await refresh(true);
  }
}

let toastTimer;
function toast(msg, isError = false) {
  const el = $("#toast");
  el.textContent = msg;
  el.classList.toggle("error", isError);
  el.classList.remove("hidden");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.add("hidden"), 4000);
}

/* ---------------------------------------------------------------- tabs */

$("#tabs").addEventListener("click", (e) => {
  const btn = e.target.closest(".tab");
  if (!btn) return;
  activeTab = btn.dataset.tab;
  $$(".tab").forEach((t) => t.classList.toggle("active", t === btn));
  $$(".tab-panel").forEach((p) => p.classList.toggle("hidden", p.id !== `tab-${activeTab}`));
});

$("#refresh-btn").addEventListener("click", async () => {
  await fetch("/api/refresh", { method: "POST" }).catch(() => {});
  await refresh(true);
  toast("Refreshed all integrations");
});

/* ------------------------------------------------------------ rendering */

const byType = (types) => summary.devices.filter((d) => types.includes(d.type));

function render() {
  renderOverview();
  renderCameras();
  renderLighting();
  renderWeather();
  renderDevices();
  renderIntegrations();
}

function pill(dev) {
  if (!dev.online) return `<span class="pill err">● offline</span>`;
  const power = dev.state && dev.state.power;
  if (power === "on") return `<span class="pill on">● on</span>`;
  if (power === "off") return `<span class="pill off">○ off</span>`;
  return `<span class="pill on">● online</span>`;
}

const TYPE_ICON = { camera: "📷", light: "💡", plug: "🔌", switch: "🎚️", weather_station: "🌤️", media_player: "📺", sensor: "📟", other: "🧩" };

/* ---- overview ---- */

function renderOverview() {
  const c = summary.counts || {};
  const weather = byType(["weather_station"])[0];
  const w = weather ? weather.state : {};
  const lights = byType(["light", "switch"]);
  const on = lights.filter((d) => d.state.power === "on").length;

  let html = `<h2>Overview</h2>
  <div class="stat-row">
    <div class="stat"><div class="stat-value">${c.cameras ?? 0}</div><div class="stat-label">📷 Cameras</div></div>
    <div class="stat"><div class="stat-value">${on}<small>/ ${lights.length}</small></div><div class="stat-label">💡 Lights on</div></div>
    ${w.temperature_f != null ? `<div class="stat"><div class="stat-value">${w.temperature_f}<small>°F</small></div><div class="stat-label">🌡️ Outdoor temp</div></div>` : ""}
    ${w.humidity_pct != null ? `<div class="stat"><div class="stat-value">${w.humidity_pct}<small>%</small></div><div class="stat-label">💧 Humidity</div></div>` : ""}
    ${w.wind_mph != null ? `<div class="stat"><div class="stat-value">${w.wind_mph}<small>mph</small></div><div class="stat-label">🌬️ Wind</div></div>` : ""}
  </div>`;

  if (lights.length) {
    html += `<div class="section-title">Quick lighting</div><div class="grid" id="ov-lights"></div>`;
  }
  const panel = $("#tab-overview");
  panel.innerHTML = html;
  if (lights.length) {
    const grid = $("#ov-lights", panel);
    lights.forEach((d) => grid.appendChild(lightCard(d, true)));
  }
}

/* ---- cameras ---- */

function renderCameras() {
  const cams = byType(["camera"]);
  const key = cams.map((d) => `${d.integration}:${d.id}:${JSON.stringify(d.stream)}:${d.online}`).join("|");
  const panel = $("#tab-cameras");
  if (key === camKey && panel.childElementCount) {
    // Only update status pills; leave the <video> elements alone.
    cams.forEach((d) => {
      const el = $(`[data-campill="${d.integration}:${d.id}"]`, panel);
      if (el) el.innerHTML = pill(d);
    });
    return;
  }
  camKey = key;
  hlsPlayers.splice(0).forEach((p) => { try { p.destroy(); } catch {} });
  panel.innerHTML = `<h2>Cameras</h2>`;
  if (!cams.length) {
    panel.innerHTML += `<div class="empty">No cameras yet. Enable the Wyze integration (with the wyze-bridge container for live video), or add stream URLs under <b>roku</b> / <b>cameras</b> in config.yaml.</div>`;
    return;
  }
  const grid = document.createElement("div");
  grid.className = "grid grid-wide";
  panel.appendChild(grid);
  cams.forEach((dev) => grid.appendChild(cameraCard(dev)));
}

function cameraCard(dev) {
  const card = document.createElement("div");
  card.className = "card";
  card.innerHTML = `
    <div class="card-head">
      <div class="card-title"><span>${TYPE_ICON.camera}</span><span class="name">${esc(dev.name)}</span></div>
      <span data-campill="${dev.integration}:${dev.id}">${pill(dev)}</span>
    </div>
    <div class="cam-frame"></div>
    <div class="cam-actions"></div>
    <div class="card-sub">${esc(dev.integration)}${dev.model ? " · " + esc(dev.model) : ""}</div>`;

  const frame = $(".cam-frame", card);
  const s = dev.stream || {};

  if (s.hls && window.Hls && Hls.isSupported()) {
    const video = document.createElement("video");
    video.muted = true; video.autoplay = true; video.playsInline = true; video.controls = true;
    frame.appendChild(video);
    const hls = new Hls({ liveDurationInfinity: true });
    hls.loadSource(s.hls);
    hls.attachMedia(video);
    hls.on(Hls.Events.ERROR, (_e, data) => {
      if (data.fatal) { hls.destroy(); video.remove(); useFallback(); }
    });
    hlsPlayers.push(hls);
    frame.insertAdjacentHTML("beforeend", `<span class="cam-badge">LIVE · HLS</span>`);
  } else if (s.hls && document.createElement("video").canPlayType("application/vnd.apple.mpegurl")) {
    const video = document.createElement("video");
    video.src = s.hls; video.muted = true; video.autoplay = true; video.playsInline = true; video.controls = true;
    frame.appendChild(video);
    frame.insertAdjacentHTML("beforeend", `<span class="cam-badge">LIVE · HLS</span>`);
  } else {
    useFallback();
  }

  function useFallback() {
    if (s.mjpeg) {
      frame.innerHTML = `<img src="${escAttr(s.mjpeg)}" alt="${escAttr(dev.name)}"><span class="cam-badge">LIVE · MJPEG</span>`;
    } else if (s.snapshot) {
      const img = document.createElement("img");
      img.alt = dev.name;
      img.dataset.snapshot = s.snapshot;
      img.src = snapUrl(s.snapshot);
      frame.innerHTML = "";
      frame.appendChild(img);
      frame.insertAdjacentHTML("beforeend", `<span class="cam-badge">SNAPSHOT · ${SNAPSHOT_MS / 1000}s</span>`);
    } else {
      frame.innerHTML = `<div class="cam-offline"><span>📷</span><span>No stream configured</span></div>`;
    }
  }

  const actions = $(".cam-actions", card);
  if (s.webrtc) {
    const a = document.createElement("a");
    a.href = s.webrtc; a.target = "_blank"; a.rel = "noopener";
    a.className = "btn btn-sm"; a.textContent = "Low-latency view ↗";
    actions.appendChild(a);
  }
  (dev.capabilities || []).filter((cmd) => ["turn_on", "turn_off", "restart"].includes(cmd)).forEach((cmd) => {
    const b = document.createElement("button");
    b.className = "btn btn-sm";
    b.textContent = { turn_on: "Power on", turn_off: "Power off", restart: "Restart" }[cmd];
    b.onclick = () => sendCommand(dev, cmd);
    actions.appendChild(b);
  });
  return card;
}

const snapUrl = (base) => base + (base.includes("?") ? "&" : "?") + "t=" + Date.now();

setInterval(() => {
  $$("#tab-cameras img[data-snapshot]").forEach((img) => { img.src = snapUrl(img.dataset.snapshot); });
}, SNAPSHOT_MS);

/* ---- lighting ---- */

function renderLighting() {
  const panel = $("#tab-lighting");
  const lights = byType(["light", "switch", "plug"]);
  panel.innerHTML = `<h2>Lighting &amp; switches</h2>`;
  if (!lights.length) {
    panel.innerHTML += `<div class="empty">No lights yet. Enable <b>hubspace</b> or <b>wyze</b> in config.yaml and add your account credentials to .env.</div>`;
    return;
  }
  const grid = document.createElement("div");
  grid.className = "grid";
  panel.appendChild(grid);
  lights.forEach((d) => grid.appendChild(lightCard(d)));
}

function lightCard(dev, compact = false) {
  const card = document.createElement("div");
  card.className = "card";
  const canToggle = dev.capabilities.includes("turn_on");
  const isOn = dev.state.power === "on";
  card.innerHTML = `
    <div class="card-head">
      <div class="card-title"><span>${TYPE_ICON[dev.type] || "💡"}</span><span class="name">${esc(dev.name)}</span></div>
      ${canToggle ? `
        <label class="switch" title="Power">
          <input type="checkbox" ${isOn ? "checked" : ""} ${dev.online ? "" : "disabled"}>
          <span class="track"></span>
        </label>` : pill(dev)}
    </div>
    <div class="card-sub">${esc(dev.integration)}${dev.online ? "" : " · offline"}</div>`;

  if (canToggle) {
    $("input[type=checkbox]", card).addEventListener("change", (e) => {
      sendCommand(dev, e.target.checked ? "turn_on" : "turn_off");
    });
  }
  if (!compact && dev.capabilities.includes("set_brightness")) {
    card.appendChild(sliderRow("Brightness", 1, 100, Number(dev.state.brightness) || 100, "%", (v) =>
      sendCommand(dev, "set_brightness", { brightness: v })));
  }
  if (!compact && dev.capabilities.includes("set_color_temp")) {
    card.appendChild(sliderRow("Warmth", 2200, 6500, Number(dev.state.color_temp || dev.state.color_temperature) || 3000, "K", (v) =>
      sendCommand(dev, "set_color_temp", { color_temp: v })));
  }
  return card;
}

function sliderRow(label, min, max, value, unit, onChange) {
  const row = document.createElement("div");
  row.className = "slider-row";
  row.innerHTML = `<label>${label}</label><input type="range" min="${min}" max="${max}" value="${value}"><output>${value}${unit}</output>`;
  const input = $("input", row);
  const output = $("output", row);
  input.addEventListener("pointerdown", () => { interacting = true; });
  input.addEventListener("input", () => { output.textContent = input.value + unit; });
  input.addEventListener("change", () => {
    interacting = false;
    onChange(Number(input.value));
  });
  return row;
}

/* ---- weather ---- */

const WEATHER_LABELS = {
  temperature_f: ["Temperature", "°F", "🌡️"],
  feels_like_f: ["Feels like", "°F", "🌡️"],
  heat_index_f: ["Heat index", "°F", "🥵"],
  dew_point_f: ["Dew point", "°F", "💧"],
  humidity_pct: ["Humidity", "%", "💧"],
  wind_mph: ["Wind", "mph", "🌬️"],
  wind_heading_deg: ["Wind direction", "°", "🧭"],
  rain_in: ["Rain", "in", "🌧️"],
  rain_rate_in_hr: ["Rain rate", "in/hr", "🌧️"],
  pressure_inhg: ["Pressure", "inHg", "📉"],
  wet: ["Leaf wetness", "", "🍃"],
};

function renderWeather() {
  const panel = $("#tab-weather");
  const stations = byType(["weather_station"]);
  panel.innerHTML = `<h2>Weather</h2>`;
  if (!stations.length) {
    panel.innerHTML += `<div class="empty">No weather stations yet. Enable <b>lacrosse</b> in config.yaml and add LACROSSE_EMAIL / LACROSSE_PASSWORD to .env.</div>`;
    return;
  }
  stations.forEach((st) => {
    const title = document.createElement("div");
    title.className = "section-title";
    title.textContent = `${st.name}${st.attributes.location ? " — " + st.attributes.location : ""}`;
    panel.appendChild(title);
    const row = document.createElement("div");
    row.className = "stat-row";
    const entries = Object.entries(st.state);
    if (!entries.length) {
      row.innerHTML = `<div class="stat"><div class="stat-label">No recent data ${st.online ? "" : "(sensor offline)"}</div></div>`;
    }
    for (const [key, value] of entries) {
      const [label, unit, icon] = WEATHER_LABELS[key] || [key.replaceAll("_", " "), "", "📟"];
      const shown = typeof value === "boolean" ? (value ? "Wet" : "Dry") : value;
      row.insertAdjacentHTML("beforeend",
        `<div class="stat"><div class="stat-value">${esc(String(shown))}<small>${unit}</small></div>
         <div class="stat-label">${icon} ${esc(label)}</div></div>`);
    }
    panel.appendChild(row);
  });
}

/* ---- other devices ---- */

function renderDevices() {
  const panel = $("#tab-devices");
  const others = byType(["media_player", "sensor", "other"]);
  panel.innerHTML = `<h2>Other devices</h2>`;
  if (!others.length) {
    panel.innerHTML += `<div class="empty">Nothing here yet. Roku TVs/players (via <b>roku.devices</b> in config.yaml) and any unrecognized devices show up on this tab.</div>`;
    return;
  }
  const grid = document.createElement("div");
  grid.className = "grid";
  panel.appendChild(grid);
  others.forEach((dev) => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      <div class="card-head">
        <div class="card-title"><span>${TYPE_ICON[dev.type] || "🧩"}</span><span class="name">${esc(dev.name)}</span></div>
        ${pill(dev)}
      </div>
      <div class="card-sub">${esc(dev.integration)}${dev.model ? " · " + esc(dev.model) : ""}</div>`;
    if (dev.integration === "roku" && dev.type === "media_player" && dev.online) {
      card.appendChild(rokuRemote(dev));
    }
    grid.appendChild(card);
  });
}

function rokuRemote(dev) {
  const remote = document.createElement("div");
  remote.className = "remote";
  const key = (k, label, cls = "") =>
    `<button class="btn btn-sm ${cls}" data-key="${k}">${label}</button>`;
  remote.innerHTML =
    key("back", "↩") + key("up", "▲") + key("home", "⌂") +
    key("left", "◀") + key("select", "OK") + key("right", "▶") +
    key("volume_down", "V−") + key("down", "▼") + key("volume_up", "V+") +
    key("power_toggle", "⏻ Power", "span3");
  remote.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-key]");
    if (btn) sendCommand(dev, "key", { key: btn.dataset.key });
  });
  return remote;
}

/* ---- integrations ---- */

const INT_HELP = {
  demo: "Sample devices so you can explore the dashboard. Turn off in config.yaml when your real devices are connected.",
  wyze: "Wyze cameras, bulbs and plugs. Needs WYZE_EMAIL/PASSWORD/KEY_ID/API_KEY in .env; live video needs the wyze-bridge container.",
  roku: "Roku TVs & players on your LAN (full control), plus Roku Smart Home cameras via a stream source you provide — Roku has no public camera API (see README).",
  lacrosse: "La Crosse View weather stations. Needs LACROSSE_EMAIL / LACROSSE_PASSWORD in .env.",
  hubspace: "Hubspace lighting & plugs. Needs HUBSPACE_EMAIL / HUBSPACE_PASSWORD in .env.",
  cameras: "Any other camera with an HLS / MJPEG / snapshot URL — list them in config.yaml.",
};

function renderIntegrations() {
  const panel = $("#tab-integrations");
  panel.innerHTML = `<h2>Integrations</h2>`;
  summary.integrations.forEach((integ) => {
    let status;
    if (!integ.enabled) status = `<span class="pill off">○ disabled</span>`;
    else if (integ.connected) status = `<span class="pill on">● connected · ${integ.device_count} devices</span>`;
    else status = `<span class="pill err">⚠ error</span>`;
    panel.insertAdjacentHTML("beforeend", `
      <div class="int-row">
        <div>
          <div class="int-name">${esc(integ.name)}</div>
          <div class="int-detail">${esc(INT_HELP[integ.id] || "")}${integ.error ? `<br><span style="color:var(--bad)">⚠ ${esc(integ.error)}</span>` : ""}</div>
        </div>
        ${status}
      </div>`);
  });
  panel.insertAdjacentHTML("beforeend",
    `<div class="empty" style="margin-top:16px">Want another brand? Each integration is one Python file — see “Adding a new integration” in home-hub/README.md.</div>`);
}

/* ---------------------------------------------------------------- util */

function esc(s) {
  return String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
}
const escAttr = esc;

/* ---------------------------------------------------------------- boot */

(async function boot() {
  const ok = await checkAuth();
  if (ok) await refresh(true);
  setInterval(refresh, REFRESH_MS);
})();
