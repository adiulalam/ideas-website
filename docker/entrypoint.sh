#!/bin/sh
# Container entrypoint: prepare the upload directory, apply database
# migrations, then hand over to the stock PHP/Apache entrypoint.
set -e

UPLOAD_DIR=/var/www/html/public/assets/img
mkdir -p "$UPLOAD_DIR"
# The directory is usually a bind mount owned by the host user. Make it
# writable for Apache; the sticky bit stops it deleting files it does not own
# (e.g. the images tracked in git).
chmod 1777 "$UPLOAD_DIR" 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    # Never block start-up on the database: if it is down, the site serves a
    # 503 page and the next restart retries the migrations.
    php /var/www/html/bin/migrate.php || echo "[entrypoint] WARNING: migrations did not run (see above); continuing" >&2
fi

exec docker-php-entrypoint "$@"
