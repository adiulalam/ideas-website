---
paths:
  - "Dockerfile"
  - "docker/**"
  - "docker-compose*.yml"
  - "deploy-ideas-website.sh"
  - ".github/**"
  - "example.environment.php"
---

# Infrastructure rules

- `docker-compose.yml` is **production**. The server runs it through
  `deploy-ideas-website.sh`. Keep the service and container name
  (`php-apache`), port `8076`, the `./public/assets/img` uploads mount and the
  read-only `./environment.php` mount, or the live site breaks.
  `docker-compose.dev.yml` is local only.
- The deploy script must work with both `docker compose` (v2) and
  `docker-compose` (v1), and with the legacy `environment.php` beside the checkout.
- Apache serves only `public/`. Keep `Options -Indexes`, the dotfile deny, and
  `php_admin_flag engine off` on the uploads directory.
- `docker/php.ini` keeps `display_errors = Off` and `expose_php = Off`.
- Pin GitHub Actions to full commit SHAs with a version comment. Keep
  `permissions: contents: read` and `persist-credentials: false`.
- Deploy must keep `needs: ci`. Never deploy code that fails `composer check`
  or the Docker build.
- Shell scripts and container files must keep LF line endings (`.gitattributes`).
  The repo is edited on Windows with `core.autocrlf=true`.
- Never bake secrets into the image. `.dockerignore` excludes `environment.php` and `.env*`.
