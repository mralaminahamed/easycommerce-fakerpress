#!/usr/bin/env bash
set -e

WP_PATH="${WP_PATH:-/home/alamin/Sites/easycommerce-development}"
ADMIN_PASS="${WP_ADMIN_PASS:-Test@12345}"

echo "Setting test admin password..."
wp --path="$WP_PATH" --allow-root --skip-plugins \
  user update admin --user_pass="$ADMIN_PASS"

echo "Done. Admin credentials: admin / $ADMIN_PASS"
echo "Next: yarn test:e2e"
