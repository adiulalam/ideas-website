#!/usr/bin/env bash
# Deploy the latest main branch on the production server.
#
# Run from the repository checkout on the server (the GitHub Actions deploy
# workflow does this after pulling). Expects the legacy config file one level
# up, i.e. ~/ideas-website-content/environment.php, unless ENV_FILE is set.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ENV_FILE:-$APP_DIR/../environment.php}"
cd "$APP_DIR"
echo "[deploy] in $APP_DIR"

if [ ! -f "$ENV_FILE" ]; then
    echo "[deploy] ERROR: $ENV_FILE not found (copy example.environment.php and fill it in)" >&2
    exit 1
fi
cp "$ENV_FILE" ./environment.php
chmod 0644 ./environment.php   # read by Apache inside the container (bind mount, read-only)
echo "[deploy] copied environment.php"

# One-time move: uploads used to live in ./assets/img (served from the repo
# root). They now live in ./public/assets/img. Copy without overwriting.
if [ -d assets/img ]; then
    mkdir -p public/assets/img
    cp -n assets/img/* public/assets/img/ 2>/dev/null || true
    echo "[deploy] migrated legacy uploads from assets/img to public/assets/img"
fi

compose() {
    if docker compose version >/dev/null 2>&1; then
        docker compose "$@"
    else
        docker-compose "$@"
    fi
}

compose up -d --build --remove-orphans
echo "[deploy] container up"

# Wait for the health check (database connectivity included).
for _ in $(seq 1 20); do
    status="$(docker inspect -f '{{.State.Health.Status}}' php-apache 2>/dev/null || echo unknown)"
    if [ "$status" = "healthy" ]; then
        echo "[deploy] healthy"
        break
    fi
    sleep 3
done
if [ "${status:-unknown}" != "healthy" ]; then
    echo "[deploy] WARNING: container is '$status'. Check: docker logs --tail 100 php-apache" >&2
fi

docker image prune -f >/dev/null 2>&1 || true
