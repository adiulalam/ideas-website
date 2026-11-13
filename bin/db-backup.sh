#!/usr/bin/env bash
# Back up the stack's database (container ideas-db).
#
#   bash bin/db-backup.sh        # writes ../backups/ideas-YYYYmmdd-HHMMSS.sql.gz, keeps the newest 14
#   bash bin/db-backup.sh -      # writes the SQL dump to stdout (e.g. to copy it to your PC)
#
# Optional settings: BACKUP_DIR, BACKUP_KEEP.
# Daily backups: add to `crontab -e` on the server, e.g.
#   15 3 * * * bash $HOME/ideas-website-content/ideas-website/bin/db-backup.sh >/dev/null
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"
set -a
# shellcheck disable=SC1091
. ./.env
set +a

DB_CONTAINER="${DB_CONTAINER:-ideas-db}"

dump() {
    MYSQL_PWD="$DB_ROOT_PASSWORD" docker exec -e MYSQL_PWD "$DB_CONTAINER" \
        mysqldump -uroot --default-character-set=utf8mb4 --single-transaction \
        --no-tablespaces --routines --triggers --set-gtid-purged=OFF --hex-blob \
        "$DB_DATABASE"
}

if [ "${1:-}" = "-" ]; then
    dump
    exit 0
fi

BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/../backups}"
BACKUP_KEEP="${BACKUP_KEEP:-14}"
mkdir -p "$BACKUP_DIR"
umask 077

file="$BACKUP_DIR/$DB_DATABASE-$(date +%Y%m%d-%H%M%S).sql.gz"
dump | gzip > "$file"
echo "$file"

# Keep only the newest $BACKUP_KEEP scheduled backups (legacy-* import dumps are never pruned).
ls -1t "$BACKUP_DIR/$DB_DATABASE"-*.sql.gz 2>/dev/null | tail -n +"$((BACKUP_KEEP + 1))" | xargs -r rm -f
