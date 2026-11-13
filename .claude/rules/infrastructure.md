---
paths:
  - "Dockerfile"
  - "docker/**"
  - "docker-compose*.yml"
  - "deploy-ideas-website.sh"
  - "bin/*.sh"
  - ".github/**"
  - "example.env"
---

# Infrastructure rules

## Production stack (`docker-compose.yml`)
- It is **production**: the server runs it through `deploy-ideas-website.sh`
  as Compose project `ideas-website`. `docker-compose.dev.yml` is local only.
- Keep these names stable. Scripts, health checks and docs depend on them:
  service `app` with container `php-apache` on port `8076` (the host's
  nginx-proxy-manager points there); service `db` with container `ideas-db`;
  volume `ideas-mysql-data`.
- The database volume stays `external: true`, so `docker compose down -v`
  cannot delete production data. Never change that, and never remove the volume.
- MySQL is published on `127.0.0.1:3309` only (for SSH tunnels). Never publish
  a database port on all interfaces.
- Every value from `.env` must be listed explicitly in `environment:`. Don't
  use `env_file:`, which would leak `DB_ROOT_PASSWORD` and `LEGACY_DB_*` into
  the app container. Add new keys to `example.env`, and CI validates the
  Compose file against it.
- The server runs Docker Compose v2 (2.27+). Compose-spec features such as
  `name:` and `depends_on: condition` are fine.

## Deploy and data scripts
- Deploy order matters for near-zero downtime: build, start `db`, wait for
  health, import, and only then replace `app`. Exit non-zero before replacing
  the app if anything about the database is wrong.
- `bin/import-legacy-db.sh` must stay idempotent: a no-op when the database
  has tables. It must only ever read the legacy server, and on failure it
  empties the new database again.
- Pass MySQL passwords via `MYSQL_PWD` with `docker exec -e MYSQL_PWD`, never
  on a command line, and never echo them.
- Dumps and backups go to `../backups/` with `umask 077`.

## Hardening
- Apache serves only `public/`. Keep `Options -Indexes`, the dotfile deny, and
  `php_admin_flag engine off` on the uploads directory.
- `docker/php.ini` keeps `display_errors = Off`, `expose_php = Off` and the
  MySQL read timeout.
- Pin GitHub Actions to full commit SHAs with a version comment. Keep
  `permissions: contents: read` and `persist-credentials: false`.
- Deploy must keep `needs: ci`. Never deploy code that fails `composer check`,
  the Compose validation or the Docker build.
- Shell scripts and container files must keep LF line endings (`.gitattributes`).
  The repo is edited on Windows with `core.autocrlf=true`.
- Never bake secrets into the image. `.dockerignore` excludes `environment.php` and `.env*`.
