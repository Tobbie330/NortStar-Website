# North Star Tree Care — Website

A professional WordPress website for **North Star Tree Care**, built to run locally
in Docker so you can preview it instantly and edit everything through the familiar
WordPress dashboard.

> _Rooted in Quality. Guided by the North Star._

![Brand colours: black, olive green, silver](theme/northstar-tree-care/assets/img/logo.svg)

---

## What you get

- A custom WordPress theme (`North Star Tree Care`) styled to match your logo
  (black, olive-green, silver) — no page builder or coding required to use it.
- A complete one-page site: hero, about, **9 services**, "why choose us",
  mission statement, process steps, and a working **"Request a Quote"** form.
- Quote requests are saved in the dashboard under **Leads** (so you never lose
  one, even before email is set up).
- Business details (phone, email, hours, hero text, social links) are editable
  from **Appearance → Customize → North Star Settings** — no code needed.
- Everything runs in Docker, so it works the same on Windows, Mac, or Linux.

---

## Quick visual preview (no Docker needed)

Want to see the design right now without installing anything? Open
[`preview/index.html`](preview/index.html) in your web browser
(double-click it). This is a static snapshot of the home page using the real
theme styles — handy for a quick look. _It's read-only;_ for the editable,
fully-working site (admin dashboard, working quote form), use Docker below.

## 1. Prerequisites

Install **Docker Desktop** (includes Docker Compose):
- Windows / Mac: https://www.docker.com/products/docker-desktop/
- Linux: install `docker` and the `docker-compose-plugin`.

That's the only thing you need to install.

## 2. Start the website

From this folder, run:

```bash
docker compose up -d
```

The first run downloads WordPress and sets everything up automatically
(give it 1–2 minutes). Then open:

| What            | URL                                            | Login                         |
| --------------- | ---------------------------------------------- | ----------------------------- |
| **Your website**| http://localhost:8080                          | —                             |
| **WP Admin**    | http://localhost:8080/wp-admin                 | `admin` / `admin123`          |
| **Database UI** | http://localhost:8081 (phpMyAdmin)             | `wordpress` / `wordpress`     |

> If the homepage still looks like a default WordPress site, the auto-setup may
> still be running. Re-run it with: `docker compose up wp-setup`

## 3. Stop / restart

```bash
docker compose stop      # pause (keeps all data)
docker compose start     # resume
docker compose down      # stop & remove containers (data is kept in volumes)
```

To wipe everything and start fresh (deletes the database and uploads):

```bash
docker compose down -v
```

---

## Editing your site (the easy way)

Log in at **http://localhost:8080/wp-admin** (`admin` / `admin123`), then:

- **Change phone / email / hours / hero text / social links:**
  `Appearance → Customize → North Star Settings`.
- **Upload your real logo:**
  `Appearance → Customize → Site Identity → Select logo`.
  (Tip: the theme already shows a placeholder North Star mark until you do.)
- **See quote requests:** the **Leads** menu in the dashboard sidebar.
- **Change the site title/tagline:** `Settings → General`.

### Use your actual logo image

You have two options:

1. **Easiest:** upload it in `Appearance → Customize → Site Identity → Select logo`.
2. **Bundle it with the theme:** save your logo as
   `theme/northstar-tree-care/assets/img/logo.png` (it will be used automatically).

### Add a hero background photo (optional)

Drop a wide photo at `theme/northstar-tree-care/assets/img/hero.jpg`
(e.g. a tree crew at work). The hero already looks good without one.

---

## Editing the design (for developers)

The theme is a normal, hand-coded WordPress theme — easy to read and change:

```
theme/northstar-tree-care/
├── style.css            # All styles + brand colours (CSS variables at the top)
├── functions.php        # Theme setup, services list, icons, quote-form handler
├── header.php           # Top navigation bar
├── footer.php           # Footer
├── front-page.php       # The home page (all sections live here)
├── index.php            # Blog/archive fallback
├── page.php             # Standard pages (block editor)
├── inc/customizer.php   # The "North Star Settings" admin panel
└── assets/
    ├── css/  js/        # Scripts
    └── img/             # logo.svg, star.svg (+ your logo.png / hero.jpg)
```

- **Services** are defined in `functions.php` → `northstar_services()`.
- **Brand colours** are the CSS variables at the top of `style.css` (`:root`).
- Because the theme folder is mounted into the container, edits appear on refresh —
  no rebuild needed.

---

## Notes on the contact form & email

The "Request a Quote" form always **saves submissions to the Leads** section of the
dashboard, so nothing is lost. It also *attempts* to email you — but local Docker
has no mail server, so to actually receive emails on a live site, install a free
SMTP plugin (e.g. **WP Mail SMTP**) and connect it to your email provider.

---

## Going live later

This same theme can be uploaded to any WordPress host (e.g. your own server,
Bluehost, SiteGround, WP Engine). Zip the `theme/northstar-tree-care` folder and
install it under `Appearance → Themes → Add New → Upload Theme`. Your content and
Customizer settings can be recreated there, or migrated with a plugin like
**All-in-One WP Migration**.

---

_Default admin credentials (`admin` / `admin123`) are for local development only.
Change them before putting this site on the public internet._
