#!/usr/bin/env sh
set -eu

php bin/migrate.php
ORDI_BOOTSTRAP_ADMIN_EMAIL="$ORDI_BOOTSTRAP_ADMIN_EMAIL" \
ORDI_BOOTSTRAP_ADMIN_PASSWORD="$ORDI_BOOTSTRAP_ADMIN_PASSWORD" \
  php bin/create-admin.php

# Bootstrap secrets must not be inherited by the long-running web server.
unset ORDI_BOOTSTRAP_ADMIN_EMAIL ORDI_BOOTSTRAP_ADMIN_PASSWORD
exec php -S 0.0.0.0:8080 -t public public/router.php
