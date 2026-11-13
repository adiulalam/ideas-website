# Ideal Ideas

A Reddit-style website for sharing ideas. People post ideas (with text and
an optional image), upvote or downvote them, and discuss them in comments.
Authors can edit and delete their own posts and are emailed when someone
comments. An admin area manages authors (with roles) and categories.

Live at **https://ideas.adiulalamadil.me**.

## Stack

- PHP 8.4 (code is PHP 8.1-compatible), no framework: a small kernel in `src/`
- MySQL 8
- Apache in Docker. Only `public/` is web-served.
- Tailwind CSS v4, compiled at build time. Vanilla JS for progressive enhancement.
- [HTMLPurifier](https://github.com/ezyang/htmlpurifier), [PHPMailer](https://github.com/PHPMailer/PHPMailer)
  and Google reCAPTCHA v2

## Running locally

Requires Docker only.

```bash
docker compose -f docker-compose.dev.yml up --build -d
```

| Service | URL |
| --- | --- |
| App | http://localhost:8080 |
| Mailpit (captures every email) | http://localhost:8025 |
| MySQL | `127.0.0.1:3307` (user `ideas`) |

The database is created from `database/schema.sql` and filled with
`database/seed.sql`. Test accounts are listed at the top of that file.
reCAPTCHA is off in development. Rebuild after code changes
(`up --build -d`). Reset everything with `down -v`.

### Checks

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer check
# Git Bash on Windows:
MSYS_NO_PATHCONV=1 docker run --rm -v "$(pwd -W)":/app -w /app composer:2 composer check
```

`composer check` runs `php -l` on every file, PHPStan (level 6) and PHPUnit.
CI runs the same checks plus `composer audit` and a Docker build.

## Project layout

```
public/          web root: index.php (front controller), assets/
src/             application code (App kernel, Controllers, Repository, Security, Services)
templates/       PHP views
database/        schema.sql (baseline), seed.sql (dev), migrations/
docker/          Apache, PHP and entrypoint configuration
tests/           PHPUnit tests
docs/            security audit
.claude/         Claude Code rules, skills and settings
```

## Production stack

`docker-compose.yml` runs the whole site as one Compose project
(`ideas-website`):

| Service | Container | What |
| --- | --- | --- |
| `app` | `php-apache` | PHP/Apache, published on port 8076 for the host's reverse proxy |
| `db` | `ideas-db` | MySQL 8.4, data in the external volume `ideas-mysql-data`, port `127.0.0.1:3309` (loopback only) |

The app reaches the database as `db:3306` on the stack's private network.
Uploaded images are bind-mounted from `public/assets/img`.

### Configuration

All settings live in one file on the server, `~/ideas-website-content/.env`
(one level above the checkout). Copy [`example.env`](example.env) to start.
Compose reads it for the `${...}` values in `docker-compose.yml`:

| Variable | Purpose |
| --- | --- |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` | The stack's MySQL database (created on first start) |
| `MAIL_USERNAME`, `MAIL_PASSWORD` | Gmail SMTP sender (empty username disables mail) |
| `CONTACT_TO` | Where contact-form messages go (default: `MAIL_USERNAME`) |
| `CAPTCHA_SECRET_KEY`, `CAPTCHA_SITE_KEY` | Google reCAPTCHA v2 |
| `APP_URL` | Absolute base URL for links in emails |
| `LEGACY_DB_*` | Optional one-time import from an old MySQL server (see below) |

The app also understands the older `environment.php` file format, which
production used until November 2026.

### Deploying

Every push to `main` runs CI and then deploys over SSH
(`.github/workflows/deploy.yml`). The server pulls `main` and runs
[`deploy-ideas-website.sh`](deploy-ideas-website.sh). That script:

1. Copies `../.env` into place.
2. Creates the database volume if it's missing.
3. Builds the new image while the old app keeps serving.
4. Starts `db` and waits for it to be healthy.
5. Runs the one-time import (no-op once done).
6. Replaces the app container.

Database migrations run automatically when the app starts. `GET /healthz`
reports app and database health.

Needed repository secrets: `DOCKER_HOST`, `DOCKER_USERNAME`, `DOCKER_SSH_PRIVATE_KEY`.

### Moving data from an old MySQL server

Set the `LEGACY_DB_*` values in `.env` and deploy.
[`bin/import-legacy-db.sh`](bin/import-legacy-db.sh) behaves like this:

- **Runs only when the stack's database has no tables.** Once data is there,
  it does nothing.
- **Copies the data:** dumps the old database, imports it, and checks that
  every table's row count matches.
- **Keeps a copy** of the old data in `../backups/legacy-*.sql.gz`.
- **Only reads the old server.** It never changes the old database.
- **On failure**, empties the new database again and stops the deploy. The
  running app keeps using the old database, and the next deploy retries.

### Backups and getting at the data

```bash
# On the server: gzip dump into ../backups/ (keeps the newest 14)
bash ~/ideas-website-content/ideas-website/bin/db-backup.sh
# Suggested crontab line (daily at 03:15)
15 3 * * * bash $HOME/ideas-website-content/ideas-website/bin/db-backup.sh >/dev/null
```

Open the production database in a GUI client from your PC through an SSH
tunnel, then connect to `127.0.0.1:3309` with `DB_USERNAME` / `DB_PASSWORD`:

```bash
ssh -N -L 3309:127.0.0.1:3309 opc@<server>
```

To load production data into your local dev stack, see the `prod-data`
skill in `.claude/skills/`. The data includes real users' emails and
password hashes, so keep such dumps off shared machines.

## Security

See [docs/security-audit.md](docs/security-audit.md) for the November 2026
audit and the fixes made.

## Author

Adiul Alam Adil: [adiulalamadil.me](https://adiulalamadil.me) · [GitHub](https://github.com/adiulalam)

## License

Copyright © Adiul Alam Adil. All rights reserved.
