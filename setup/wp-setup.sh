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

echo ""
echo "✅ North Star Tree Care is ready!"
echo "   Website : ${SITE_URL:-http://localhost:8080}"
echo "   Admin   : ${SITE_URL:-http://localhost:8080}/wp-admin  (user: ${WP_ADMIN_USER:-admin} / pass: ${WP_ADMIN_PASSWORD:-admin123})"
echo ""
