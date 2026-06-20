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
- A full multi-page website with a real navigation menu:
  - **Home** — hero, about, 9 services, "why choose us", mission, process, quote form
  - **Services** — every service explained in detail
  - **Gallery** — a photo grid with click-to-zoom lightbox
  - **About** — story, mission & values
  - **Contact** — contact details + working quote form + service-area band
- A working **"Request a Quote"** form that **saves every submission** to the
  dashboard under **Leads** *and* sends a real email.
- **Real, visible local email:** a built-in mail catcher (**Mailpit**) shows you
  exactly what emails the site sends at <http://localhost:8025> — so you can test
  the contact form end-to-end on your own computer, just like the live site.
- Business details (phone, email, hours, hero text, social links) are editable
  from **Appearance → Customize → North Star Settings** — no code needed.
- Everything runs in Docker, so it works the same on Windows, Mac, or Linux.

---

## Quick visual preview (no Docker needed)

Want to see the design right now without installing anything? Open
[`preview/index.html`](preview/index.html) in your web browser (double-click it).
This is a static snapshot of the **whole site** — you can click through Home,
Services, Gallery, About, and Contact. _It's read-only_ (forms and admin don't
work here); for the editable, fully-working site, use Docker below.

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

| What             | URL                                            | Login                         |
| ---------------- | ---------------------------------------------- | ----------------------------- |
| **Your website** | http://localhost:8080                          | —                             |
| **WP Admin**     | http://localhost:8080/wp-admin                 | `admin` / `admin123`          |
| **Email inbox**  | http://localhost:8025 (Mailpit)                | —                             |
| **Database UI**  | http://localhost:8081 (phpMyAdmin)             | `wordpress` / `wordpress`     |

> **Try the contact form:** open the site, go to **Contact**, fill in the form and
> submit. The submission appears in **WP Admin → Leads**, and the email the site
> sent shows up in the **Mailpit inbox** at http://localhost:8025.

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
- **Edit a page's text:** `Pages → (Home / Services / Gallery / About / Contact)`.
  The designed sections live in the theme, but each page also has an editable
  area in the block editor for extra text.
- **Edit the navigation menu:** `Appearance → Menus` (a "Main Menu" is set up for you).
- **Change the site title/tagline:** `Settings → General`.

### Add your gallery photos

The Gallery page ships with themed placeholders. To use real job photos, just
drop image files (`.jpg`, `.png`, or `.webp`) into:

```
theme/northstar-tree-care/assets/img/gallery/
```

They're picked up automatically, and the filename becomes the caption
(e.g. `oak-removal.jpg` → "Oak Removal"). Delete the placeholder `.svg` files
once you've added your own.

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
├── style.css              # All styles + brand colours (CSS variables at the top)
├── functions.php          # Theme setup, services, gallery, icons, quote-form handler
├── header.php             # Top navigation bar
├── footer.php             # Footer
├── front-page.php         # The home page
├── page-services.php      # Services page
├── page-gallery.php       # Gallery page (with lightbox)
├── page-about.php         # About page
├── page-contact.php       # Contact page
├── page.php               # Fallback for any other page
├── index.php              # Blog/archive fallback
├── template-parts/
│   └── quote-form.php     # The shared "Request a Quote" form
├── inc/customizer.php     # The "North Star Settings" admin panel
└── assets/
    ├── js/                # Scripts (mobile menu, lightbox)
    └── img/               # logo.svg, star.svg, gallery/ (+ your logo.png / hero.jpg)
```

- **Services** are defined in `functions.php` → `northstar_services()`.
- **Gallery images** are resolved by `functions.php` → `northstar_gallery()`.
- **Brand colours** are the CSS variables at the top of `style.css` (`:root`).
- WordPress automatically uses `page-{slug}.php` for the matching page, so the
  Services/Gallery/About/Contact pages get their designed templates with no setup.
- Because the theme folder is mounted into the container, edits appear on refresh —
  no rebuild needed.

---

## How the contact form & email works

The "Request a Quote" form (on the Home and Contact pages):

1. **Saves every submission** to the dashboard under **Leads** — nothing is ever lost.
2. **Sends a real email.** Locally, that email is captured by **Mailpit** so you can
   see it at <http://localhost:8025> (it is *not* sent to the real internet — perfect
   for testing). This is handled by `mu-plugins/northstar-mailpit.php`.

It also blocks spam bots with a hidden honeypot field and a WordPress nonce.

### Sending real email when you go live

On a public site you'll want emails to actually reach your inbox. The easy path:

1. Delete or disable `mu-plugins/northstar-mailpit.php` (that file is local-only).
2. Install the free **WP Mail SMTP** plugin (`Plugins → Add New`).
3. Connect it to your email provider (Gmail, Outlook, your host's SMTP, etc.).
4. Set the "send to" address under `Appearance → Customize → North Star Settings → Email`.

Prefer a plugin-managed form instead of the built-in one? You can install
**Contact Form 7** or **WPForms** and drop its shortcode onto the Contact page —
the Leads/Mailpit setup above is independent of which form you use.

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
