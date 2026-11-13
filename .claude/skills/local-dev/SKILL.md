---
name: local-dev
description: Start, reset and test the Ideal Ideas app locally with Docker (app + seeded MySQL + Mailpit), log in with the seeded test accounts, read captured emails, and run lint/PHPStan/PHPUnit. Use when asked to run, preview, test or debug the site locally, or before declaring a code change done.
---

# Local development

Everything runs in Docker. No local PHP, Composer or MySQL is needed.

## Start or rebuild

```bash
docker compose -f docker-compose.dev.yml up --build -d
docker compose -f docker-compose.dev.yml ps          # wait for app = healthy
docker compose -f docker-compose.dev.yml logs -f app # PHP errors and migration output
```

- App: http://localhost:8080 · Mailpit (all outgoing email): http://localhost:8025 · MySQL: 127.0.0.1:3307
- Code is **baked into the image**, so run `up --build -d` again after every change.
  The Tailwind CSS is rebuilt during the image build.
- Dev mode (`APP_ENV=development`) disables reCAPTCHA and shows exception
  details on error pages. Production never does either.

## Test accounts

Listed at the top of `database/seed.sql` (admin with every role, a regular
user, and a "legacy" user). All seed passwords use the old MD5 format, so the
first login also exercises the automatic upgrade to `password_hash()`. Check it with:

```bash
docker compose -f docker-compose.dev.yml exec -T db mysql -uideas -pideas-dev-password ideas \
  -e "SELECT Email, LEFT(Password,4), LENGTH(Password) FROM Author"
```

## Reset the database

```bash
docker compose -f docker-compose.dev.yml down -v   # drops the dbdata + uploads volumes
docker compose -f docker-compose.dev.yml up --build -d
```

`schema.sql` + `seed.sql` load only into an empty volume. Migrations run on
every app start.

## Checks (run before saying a change is done)

```bash
# Git Bash on Windows (MSYS_NO_PATHCONV stops /app being rewritten to C:/Program Files/Git/app)
MSYS_NO_PATHCONV=1 docker run --rm -v "$(pwd -W)":/app -w /app composer:2 composer check
# macOS / Linux
docker run --rm -v "$PWD":/app -w /app composer:2 composer check
```

`composer check` = `lint` (php -l on every file) + `analyse` (PHPStan level 6)
+ `test` (PHPUnit). Composer installs dev dependencies into `vendor/` on the
first run (`composer install`).

## Scripted requests

POSTs need the session cookie and CSRF token:

```bash
J=/tmp/jar; B=http://127.0.0.1:8080
T=$(curl -s -c $J -b $J $B/login | grep -o 'name="_token" value="[a-f0-9]*"' | sed 's/.*value="//;s/"//')
curl -s -c $J -b $J --data-urlencode "_token=$T" --data-urlencode "email=..." --data-urlencode "password=..." $B/login
```

On Windows, files you upload with `curl -F image=@...` need Windows-style
paths (`cygpath -m`). Antivirus may lock test files that contain PHP code.
Run those tests inside the container (`docker compose ... exec -T app sh -c '...'`).

## Simulate the production outage

```bash
docker compose -f docker-compose.dev.yml pause db     # hung DB → 503 after ~10 s
docker compose -f docker-compose.dev.yml unpause db
docker run --rm -d --name blackhole -p 127.0.0.1:8081:80 -e APP_ENV=development \
  -e RUN_MIGRATIONS=0 -e DB_HOST=10.255.255.1 -e DB_DATABASE=x ideas-dev-app   # unreachable DB → 503 after ~5 s
```
