#!/bin/sh
# Auto-installs and configures WordPress for North Star Tree Care.
# Safe to run multiple times — it only installs once, then just re-applies
# the theme and settings.

set -e

echo "→ Waiting for WordPress core files..."
until [ -f /var/www/html/wp-load.php ]; do
  sleep 3
done

echo "→ Installing / configuring WordPress (waiting for the database)..."
ATTEMPTS=0
until wp core is-installed 2>/dev/null; do
  ATTEMPTS=$((ATTEMPTS + 1))

  if wp core install \
      --url="${SITE_URL:-http://localhost:8080}" \
      --title="North Star Tree Care" \
      --admin_user="${WP_ADMIN_USER:-admin}" \
      --admin_password="${WP_ADMIN_PASSWORD:-admin123}" \
      --admin_email="${WP_ADMIN_EMAIL:-info@northstartreecare.com}" \
      --skip-email 2>/dev/null; then
    break
  fi

  if [ "$ATTEMPTS" -ge 40 ]; then
    echo "✗ Database did not become ready in time. Try: docker compose up wp-setup"
    exit 1
  fi

  echo "   ...database not ready yet (attempt $ATTEMPTS), retrying in 5s"
  sleep 5
done

echo "→ Applying North Star branding & settings..."
wp option update blogname "North Star Tree Care"
wp option update blogdescription "Rooted in Quality. Guided by the North Star."
wp option update timezone_string "America/New_York" || true
wp rewrite structure '/%postname%/' --hard
wp theme activate northstar-tree-care

# --- Create the site pages (idempotent) ----------------------------------
ensure_page() {
  _slug="$1"; _title="$2"
  _id=$(wp post list --post_type=page --name="$_slug" --field=ID --posts_per_page=1 2>/dev/null | head -n1)
  if [ -z "$_id" ]; then
    _id=$(wp post create --post_type=page --post_status=publish \
            --post_title="$_title" --post_name="$_slug" --post_content="" --porcelain)
  fi
  echo "$_id"
}

echo "→ Creating pages (Home, Services, Gallery, About, Contact)..."
HOME_ID=$(ensure_page "home" "Home")
SERVICES_ID=$(ensure_page "services" "Services")
GALLERY_ID=$(ensure_page "gallery" "Gallery")
ABOUT_ID=$(ensure_page "about" "About")
CONTACT_ID=$(ensure_page "contact" "Contact")

# Use the designed home page as the front page.
wp option update show_on_front page
wp option update page_on_front "$HOME_ID"

# --- Build the navigation menu (idempotent) ------------------------------
echo "→ Building the navigation menu..."
if ! wp menu list --fields=slug --format=csv 2>/dev/null | grep -q '^main-menu$'; then
  wp menu create "Main Menu"
fi
ITEMS=$(wp menu item list main-menu --format=count 2>/dev/null || echo 0)
if [ "$ITEMS" = "0" ]; then
  wp menu item add-post main-menu "$HOME_ID"     --title="Home"     >/dev/null 2>&1 || true
  wp menu item add-post main-menu "$SERVICES_ID" --title="Services" >/dev/null 2>&1 || true
  wp menu item add-post main-menu "$GALLERY_ID"  --title="Gallery"  >/dev/null 2>&1 || true
  wp menu item add-post main-menu "$ABOUT_ID"    --title="About"    >/dev/null 2>&1 || true
  wp menu item add-post main-menu "$CONTACT_ID"  --title="Contact"  >/dev/null 2>&1 || true
fi
wp menu location assign main-menu primary >/dev/null 2>&1 || true

wp rewrite flush --hard

echo ""
echo "✅ North Star Tree Care is ready!"
echo "   Website : ${SITE_URL:-http://localhost:8080}"
echo "   Admin   : ${SITE_URL:-http://localhost:8080}/wp-admin  (user: ${WP_ADMIN_USER:-admin} / pass: ${WP_ADMIN_PASSWORD:-admin123})"
echo "   Mail    : http://localhost:8025  (emails the contact form sends appear here)"
echo ""
