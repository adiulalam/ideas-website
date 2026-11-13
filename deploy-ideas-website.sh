#!/usr/bin/env bash
# Deploy the latest main branch on the production server.
#
# Run from the repository checkout on the server (the GitHub Actions deploy
# workflow does this after pulling). Expects the secrets file one level up,
# ~/ideas-website-content/.env, unless ENV_FILE is set (see example.env).
#
# Order matters: the new image is built and the database started while the
# old app container keeps serving; the app is only replaced at the very end.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ENV_FILE:-$APP_DIR/../.env}"
DB_VOLUME=ideas-mysql-data
cd "$APP_DIR"

log() { echo "[deploy] $*"; }
compose() { docker compose "$@"; }

wait_healthy() { # <container> <attempts, 3 s apart>
    local status=unknown
    for _ in $(seq 1 "$2"); do
        status="$(docker inspect -f '{{.State.Health.Status}}' "$1" 2>/dev/null || echo missing)"
        if [ "$status" = "healthy" ]; then
            log "$1 is healthy"
            return 0
        fi
        sleep 3
    done
    echo "[deploy] WARNING: $1 is '$status'. Check: docker logs --tail 100 $1" >&2
    return 1
}

log "in $APP_DIR"

if [ ! -f "$ENV_FILE" ]; then
    echo "[deploy] ERROR: $ENV_FILE not found (copy example.env and fill it in)" >&2
    exit 1
fi
cp "$ENV_FILE" ./.env
chmod 0600 ./.env
rm -f ./environment.php   # legacy config file, no longer used by the stack
log "copied .env"

if ! docker volume inspect "$DB_VOLUME" >/dev/null 2>&1; then
    docker volume create "$DB_VOLUME" >/dev/null
    log "created volume $DB_VOLUME"
fi

compose pull --quiet db
compose build --pull app
log "image built"

compose up -d db
wait_healthy ideas-db 40 || exit 1

# One-time data copy from the old database server (no-op once done).
bash bin/import-legacy-db.sh

compose up -d --remove-orphans
wait_healthy php-apache 30 || true

if [ "${PRUNE_IMAGES:-1}" = "1" ]; then
    docker image prune -f >/dev/null 2>&1 || true
fi
log "done"
