---
name: prod-data
description: Work with the production Ideal Ideas database safely — take or restore backups, run read-only queries, open it in a GUI client through an SSH tunnel, or copy production data into the local dev stack. Use when the user wants to look at, export, back up, restore or pull production data, or before any manual change to production data.
---

# Production data

The database is the `db` service (container `ideas-db`, MySQL 8.4) of the
production Compose stack. Its data lives in the external volume
`ideas-mysql-data`, and credentials are in `~/ideas-website-content/.env`.
Everything here runs over SSH. Ask the user for their SSH command (e.g.
`ssh opc@<server-ip>`), and the user approves each server command.

Ground rules:
- **Back up before any write**, and get the user's explicit go-ahead for
  every statement that changes production data.
- Never print `.env` or passwords. Scripts read them from `.env` themselves.
- Production dumps contain real emails and password hashes. Keep them on the
  server or in the user's own machine, never in the repo, chat or anywhere shared.

## Back up

```bash
cd ~/ideas-website-content/ideas-website
bash bin/db-backup.sh                  # → ../backups/ideas-YYYYmmdd-HHMMSS.sql.gz, keeps 14
ls -lh ../backups/
```

Daily backups (only set this up if the user asks): add to `crontab -e`
`15 3 * * * bash $HOME/ideas-website-content/ideas-website/bin/db-backup.sh >/dev/null`.

## Read-only queries

Write the SQL to a scratchpad file, `scp` it to `/tmp/q.sql`, then:

```bash
ssh <target> 'cd ~/ideas-website-content/ideas-website && set -a && . ./.env && set +a && MYSQL_PWD="$DB_PASSWORD" docker exec -i -e MYSQL_PWD ideas-db mysql -u"$DB_USERNAME" --table "$DB_DATABASE" < /tmp/q.sql; rm -f /tmp/q.sql'
```

## GUI client from the user's PC

```bash
ssh -N -L 3309:127.0.0.1:3309 <target>
```

Then connect a client (VS Code SQLTools, DBeaver, MySQL Workbench) to
`127.0.0.1:3309`, database `DB_DATABASE`, user `DB_USERNAME`.

## Copy production data into the local dev stack

This replaces the local dev data. Confirm with the user first.

```bash
ssh <target> 'bash ~/ideas-website-content/ideas-website/bin/db-backup.sh -' > prod.sql
docker compose -f docker-compose.dev.yml exec -T db sh -c 'MYSQL_PWD=ideas-dev-root-password mysql -uroot -e "DROP DATABASE ideas; CREATE DATABASE ideas; GRANT ALL ON ideas.* TO ideas"'
docker compose -f docker-compose.dev.yml exec -T db sh -c 'MYSQL_PWD=ideas-dev-root-password mysql -uroot ideas' < prod.sql
rm prod.sql
```

Real users' passwords won't be known locally. Create a dev admin, or give an
account a known password locally only.

## Restore a backup (destructive)

Only with the user's explicit go-ahead, after taking a fresh backup:

```bash
cd ~/ideas-website-content/ideas-website && set -a && . ./.env && set +a
gunzip -c ../backups/<file>.sql.gz | MYSQL_PWD="$DB_ROOT_PASSWORD" docker exec -i -e MYSQL_PWD ideas-db mysql -uroot "$DB_DATABASE"
```
