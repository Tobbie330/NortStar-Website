# GoldStandardX Gaming — Per‑Server Pages Update

This folder builds an **import file** for your WordPress site
(`yellowgreen-jay-473493.hostingersite.com`) that does everything you asked:

- **Rules & Events are now per server, not global.** Every community gets its own page
  with its **own Rules, Events, Updates, and Info** — so Hornet Heaven (PvE) and Final
  Order (PvP) each keep their own rules instead of one shared list.
- **Those pages stay out of the top navigation bar.** They live *under* "Our Servers"
  (e.g. `/our-servers/final-order/`), so they don't clutter the menu.
- **On "Our Servers", every community card has an "Open Server Page" button** that goes
  to that community's own page — which then links to its rules, events, updates, Discord,
  and (where it exists) its real website. Final Order links to **rustfinalorder.org**;
  the Community Hub links to your **Google Sites** page.
- **The Join Staff page** keeps all the migrated old staff‑site content **and** now has a
  **Leadership & Contacts** block with editable **Owner** and **Head Admin** email spots.

---

## What's in this folder

| File | What it does | Do you need to delete anything first? |
|------|--------------|----------------------------------------|
| `01-server-pages.xml` | Adds the **15 new per‑server pages**. | **No** — safe to import as‑is. |
| `02-replace-our-servers-and-staff.xml` | Replaces **Our Servers** + **Join Staff**. | **Yes** — trash the 2 old pages first (see below). |
| `goldstandardx-gaming-update.xml` | Everything in one file (all 17 pages). | Yes — trash 5 old pages first. |
| `generate_import.py` | The script that builds the files. Edit + re‑run to regenerate. | — |

If you just want the quick win with zero risk, **import `01-server-pages.xml`** and you
immediately have 15 per‑server pages. Do the "replace" file when you're ready.

---

## How to import (the simple way)

### Step 1 — (only for the replace file) Trash the old pages
In WordPress: **Pages → All Pages**, hover each of these and click **Trash**:
- `Our Servers`
- `Join Staff`
- `Server Rules`  ← old global one, replaced by per‑server rules
- `Events`        ← old global one, replaced by per‑server events
- `Server Updates`← old global one, replaced by per‑server updates

(Trashing frees up the clean web addresses so the new pages import correctly.)

### Step 2 — Import
1. **Tools → Import**.
2. Under **WordPress**, click **Install Now** (first time only), then **Run Importer**.
3. **Choose File** → pick the `.xml` file → **Upload file and import**.
4. On the author screen, you can map the author to yourself. Leave
   **"Download and import file attachments"** unchecked (your logos are already on the site).
5. Click **Submit**.

### Step 3 — Put the two pages back in the menu
Because the old Our Servers / Join Staff were trashed, re‑add the new ones to the menu:
1. **Appearance → Menus**.
2. Tick **Our Servers** and **Join Staff** on the left → **Add to Menu**.
3. Drag them into the order you want → **Save Menu**.

### Step 4 — Clear cache
**LiteSpeed Cache → Purge All.** (Your changes show up right away.)

---

## How to edit content later

Everything is normal WordPress now — no separate websites.

**Edit a server's rules/events/info:**
1. **Pages → All Pages**.
2. Open the community page (e.g. `Final Order`, `Hornet Heaven`, `MetroLife RP`).
3. Click into the text and edit it. (Each page is one **Custom HTML / Code** block — you can
   edit the wording directly, or use **Edit as HTML** for full control.)
4. **Update**.

**Change the owner / head‑admin emails:**
- Open **Join Staff** → find the **Leadership & Contacts** block → replace
  `owner@goldstandardxgaming.org` and `headadmin@goldstandardxgaming.org` with the real ones → **Update**.

**Add a community's real website button:**
- Open that community's page → in the top buttons, change the **Official Website** link, or add
  it in the **Server Info** section.

**Add a brand‑new community later:**
- Easiest: open `generate_import.py`, copy one line in the `SERVERS` list, change the name /
  slug / Discord / logo, re‑run `python3 generate_import.py`, and import the new page.
- Or in WordPress: **Pages → Add New**, set **Parent = Our Servers**, paste a copy of an existing
  server page's HTML, and add an "Open Server Page" button on the Our Servers page.

---

## Communities included (15)

Community Hub, Final Order (Rust PvP), Final Order Console Edition, Ark of War, Last Breath
Survival, Warborn Exiles, EmberCraft Survival, Emberveil Kingdom, Convict Outbreak,
Prospectors Survival, Windrose Frontier Network, MetroLife RP, Gaming Server, Fallen
Bloodline, Hornet Heaven (Rust PvE).

Uploaded logos already show for **Final Order Console Edition**, **Windrose Frontier
Network**, and **MetroLife RP**. The rest show a reserved "Logo Slot" until you upload art —
to add one, open the page, click the logo area, and swap in your image.

---

## A note on the login you shared
This build environment is **network‑blocked from your Hostinger site** (the proxy returns
403), so changes can't be pushed live from here — that's why we use the import file. For
your security, please **change that password** after importing, or create a revocable
**Application Password** (Users → Profile → Application Passwords) instead of sharing your
main one.
