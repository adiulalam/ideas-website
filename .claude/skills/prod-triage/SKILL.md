---
name: prod-triage
description: Diagnose why the live site https://ideas.adiulalamadil.me is down, slow, blank or erroring — separates Cloudflare, origin server, container and database problems from outside, then gives the exact server-side commands. Use when the user says the site is not working, not loading, returns 5xx, or "was working before".
---

# Production triage

Production topology: Cloudflare → reverse proxy on the server → Docker
container `php-apache` (port 8076) → **external** MySQL (host in
`~/ideas-website-content/environment.php`). Deploys come from pushes to `main`.

You cannot SSH in. Diagnose from outside, then give the user the commands
to run on the server. Never ask for or print secrets.

## 1. Outside-in checks (run these yourself)

```bash
nslookup ideas.adiulalamadil.me                      # should resolve to Cloudflare IPs
curl -s https://ideas.adiulalamadil.me/cdn-cgi/trace # Cloudflare edge alive?
curl -s -m 30 -o /dev/null -w '%{http_code} %{time_total}s\n' https://ideas.adiulalamadil.me/healthz
curl -s -m 150 -o page.html -D - -w '%{http_code} %{time_total}s\n' https://ideas.adiulalamadil.me/
```

Use `-m 150`: Cloudflare waits up to 100 s before its own 524 error.

| What you see | Meaning |
| --- | --- |
| DNS/trace fail | Cloudflare / DNS problem |
| `520`–`523` from Cloudflare | origin server or proxy down / unreachable |
| `524` after ~100 s | origin accepted the connection but PHP hung |
| `/healthz` → `{"status":"ok"}` | app and DB are fine; look at the specific page |
| `/healthz` → 503 `database: unreachable` in ~5 s | **database down/unreachable** (most common) |
| 503 "Temporarily unavailable" page in ~5–10 s | same: DB unreachable or hung |
| exactly ~60 s then a blank/truncated page | old (pre-2026) code still deployed and DB unreachable |
| 200 with error text | read it; then check container logs |

The response header `X-Powered-By` shows the old code is still running (the
new image hides it).

## 2. Commands for the user to run on the server

```bash
docker ps --filter name=php-apache                     # running? healthy?
docker logs --tail 100 php-apache 2>&1 | grep -E '\[(db|app|migrate|mail|health)\]'
docker exec php-apache php -r 'require "/var/www/html/vendor/autoload.php"; $c=App\Config::load("/var/www/html"); $t=microtime(true); try { App\Database::connect($c)->query("SELECT 1"); echo "DB OK"; } catch (Throwable $e) { echo "DB FAIL: ", $e->getMessage(); } printf(" (%.1fs)\n", microtime(true)-$t);'
```

The `php -r` line prints the real connection error without exposing credentials.

- **Connection timed out** → the DB host is down, its IP changed, a firewall or
  security list drops traffic, or a free-tier DB was paused or expired. Fix it
  at the DB provider, or update `environment.php` and run `docker restart php-apache`.
- **Connection refused** → DB server is up but MySQL isn't listening or isn't
  running. Restart it.
- **Access denied** → the credentials in `environment.php` are wrong or were rotated.
- **Container missing or exited** → `cd ~/ideas-website-content/ideas-website && bash deploy-ideas-website.sh`.

## 3. After a fix

Re-run the step 1 curls. `/healthz` must return `{"status":"ok"}` quickly.
