---
name: prod-triage
description: Diagnose why the live site https://ideas.adiulalamadil.me is down, slow, blank or erroring — separates Cloudflare, origin server, container and database problems from outside, then runs read-only checks on the server over SSH with the user's approval. Use when the user says the site is not working, not loading, returns 5xx, or "was working before".
---

# Production triage

Topology: Cloudflare → nginx-proxy-manager on the server (ports 80/443) →
Compose project `ideas-website`: container `php-apache` (port 8076) → container
`ideas-db` (MySQL 8.4, volume `ideas-mysql-data`). Secrets are in
`~/ideas-website-content/.env`. Deploys come from pushes to `main`.

The server also runs many unrelated containers. Never stop or change them,
in particular `mysql-server`, which holds another app's `notes` database.

## 1. Outside-in checks (run these yourself)

```bash
curl -s https://ideas.adiulalamadil.me/cdn-cgi/trace | head -3   # Cloudflare edge alive?
curl -s -m 30 -w ' [%{http_code} %{time_total}s]\n' https://ideas.adiulalamadil.me/healthz
curl -s -m 150 -o /dev/null -D - -w '%{http_code} %{time_total}s\n' https://ideas.adiulalamadil.me/
```

| What you see | Meaning |
| --- | --- |
| trace fails | Cloudflare / DNS problem |
| `520`–`523` | origin server or nginx-proxy-manager down |
| `524` after ~100 s | origin accepted the connection but PHP hung |
| `/healthz` → `{"status":"ok"}` | app and DB fine; look at the specific page and the app logs |
| `/healthz` → 503 `database: unreachable` in ~5 s | app can't reach `ideas-db` (container down, unhealthy, or wrong credentials in `.env`) |
| 503 "Temporarily unavailable" in ~5–10 s | same as above |

## 2. On the server (read-only, with the user's approval)

Ask the user for their SSH command (e.g. `ssh opc@<server-ip>`). Never write
the server's IP into the repo: it is public and the IP would let people
bypass Cloudflare.

The terminal tool refuses pipes and `$` in PowerShell, so run one-shot
commands as `ssh <target> '<simple command>'` in a terminal tab and read the
tab. For anything with pipes or quoting, write a script to the scratchpad,
`scp` it to `/tmp/`, run it with `ssh <target> bash /tmp/<script>.sh`, and
delete it afterwards.

```bash
cd ~/ideas-website-content/ideas-website
docker compose ps                                          # both containers up and healthy?
docker logs --tail 100 php-apache 2>&1 | grep -E '\[(db|app|migrate|mail|health)\]'
docker logs --tail 50 ideas-db
docker exec php-apache php -r 'require "/var/www/html/vendor/autoload.php"; $c=App\Config::load("/var/www/html"); try { App\Database::connect($c)->query("SELECT 1"); echo "DB OK\n"; } catch (Throwable $e) { echo "DB FAIL: ", $e->getMessage(), "\n"; }'
df -h /                                                    # full disk stops MySQL
```

| Symptom | Fix |
| --- | --- |
| `ideas-db` exited or unhealthy | `docker logs ideas-db`, then `docker compose up -d db`. Never delete the volume. |
| Access denied | `.env` credentials don't match the user created on the volume's first start. Fix `.env`; don't recreate the volume. |
| Container missing | `bash deploy-ideas-website.sh` from the checkout |
| Disk full | Prune old images with `docker image prune -f` and old dumps in `../backups/` |

## 3. Past incidents (learn from them)

- **Nov 2026, every page hung 60 s, then went blank.** The app reached the
  shared `mysql-server` through the server's *public* IP, and the Oracle Cloud
  security list had stopped allowing that port, so packets were silently
  dropped. Lessons: containers must reach each other over Docker networks,
  never through a public IP. A timeout (rather than "refused") means a
  firewall is dropping packets. Fixed for good by moving the DB into the stack.

## 4. After a fix

Re-run the step 1 curls. `/healthz` must return `{"status":"ok"}` quickly.
