#!/usr/bin/env bash
# One-time copy of the Ideas data from a legacy MySQL server into the stack's
# own database (container ideas-db).
#
# Called by deploy-ideas-website.sh after the db container is healthy and
# before the app is switched over. Does nothing unless LEGACY_DB_HOST is set
# in .env AND the stack's database has no tables yet, so it is safe to run on
# every deploy. The legacy database is only read, never changed.
#
# On any failure it empties the new database again and exits non-zero: the
# deploy stops and the running app keeps serving from the legacy database.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"
set -a
# shellcheck disable=SC1091
. ./.env
set +a

DB_CONTAINER="${DB_CONTAINER:-ideas-db}"
log() { echo "[import] $*"; }

if [ -z "${LEGACY_DB_HOST:-}" ]; then
    log "LEGACY_DB_HOST is not set; nothing to import"
    exit 0
fi
: "${LEGACY_DB_DATABASE:?LEGACY_DB_DATABASE is required}" "${LEGACY_DB_USERNAME:?LEGACY_DB_USERNAME is required}"
LEGACY_DB_PORT="${LEGACY_DB_PORT:-3306}"

# Passwords travel in MYSQL_PWD (passed through by name with `docker exec -e`)
# so they never appear on a command line or in the terminal.
new_sql() {
    MYSQL_PWD="$DB_ROOT_PASSWORD" docker exec -i -e MYSQL_PWD "$DB_CONTAINER" \
        mysql -uroot --default-character-set=utf8mb4 -N --batch "$@"
}
legacy_sql() {
    MYSQL_PWD="${LEGACY_DB_PASSWORD:-}" docker exec -i -e MYSQL_PWD "$DB_CONTAINER" \
        mysql -h"$LEGACY_DB_HOST" -P"$LEGACY_DB_PORT" -u"$LEGACY_DB_USERNAME" -N --batch "$@"
}

existing="$(new_sql -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB_DATABASE'")"
if [ "$existing" != "0" ]; then
    log "$DB_DATABASE already has $existing tables; skipping import"
    exit 0
fi

reset_new_database() {
    new_sql -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\`" || true
}
on_exit() {
    local status=$?
    if [ "$status" -ne 0 ]; then
        log "FAILED: emptying $DB_DATABASE again so the next deploy retries. The legacy database is untouched."
        reset_new_database
        [ -n "${DUMP:-}" ] && rm -f "$DUMP"
    fi
}
trap on_exit EXIT

BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/../backups}"
mkdir -p "$BACKUP_DIR"
umask 077
DUMP="$BACKUP_DIR/legacy-$LEGACY_DB_DATABASE-$(date +%Y%m%d-%H%M%S).sql"

log "dumping $LEGACY_DB_DATABASE from $LEGACY_DB_HOST:$LEGACY_DB_PORT"
# --lock-tables (part of the defaults) gives a consistent snapshot of MyISAM tables.
MYSQL_PWD="${LEGACY_DB_PASSWORD:-}" docker exec -e MYSQL_PWD "$DB_CONTAINER" \
    mysqldump -h"$LEGACY_DB_HOST" -P"$LEGACY_DB_PORT" -u"$LEGACY_DB_USERNAME" \
    --default-character-set=utf8mb4 --no-tablespaces --set-gtid-purged=OFF --hex-blob \
    "$LEGACY_DB_DATABASE" > "$DUMP"
if ! tail -n 1 "$DUMP" | grep -q 'Dump completed'; then
    log "the dump looks incomplete"
    exit 1
fi

log "importing $(wc -c < "$DUMP" | tr -d ' ') bytes into $DB_DATABASE"
new_sql "$DB_DATABASE" < "$DUMP"

log "verifying row counts (legacy -> new)"
mismatch=0
for table in $(legacy_sql -e "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$LEGACY_DB_DATABASE' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"); do
    old="$(legacy_sql -e "SELECT COUNT(*) FROM \`$LEGACY_DB_DATABASE\`.\`$table\`")"
    new="$(new_sql -e "SELECT COUNT(*) FROM \`$DB_DATABASE\`.\`$table\`" 2>/dev/null || echo missing)"
    printf '[import]   %-18s %6s -> %6s\n' "$table" "$old" "$new"
    [ "$old" = "$new" ] || mismatch=1
done
if [ "$mismatch" -ne 0 ]; then
    log "row counts differ (was someone writing during the copy?)"
    exit 1
fi

gzip "$DUMP"
log "done. A copy of the legacy data is kept in $DUMP.gz"
