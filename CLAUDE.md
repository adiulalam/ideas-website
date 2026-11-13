# Ideal Ideas — guide for Claude

A Reddit-style site where people post ideas, vote and comment, with an admin
area for authors and categories. PHP 8.4 + MySQL, served by Apache in Docker,
live at https://ideas.adiulalamadil.me behind Cloudflare. No framework: a
small hand-written kernel, Composer for libraries, Tailwind v4 for CSS.

## Deploys go straight to production

- **Every push to `main` deploys** (`.github/workflows/deploy.yml` → SSH →
  `deploy-ideas-website.sh`). Work on a branch. Never push, merge or run the
  deploy script without the user's explicit go-ahead for that action.
- The production database is external and was created by hand.
  `database/schema.sql` is a reconstruction, not the source of truth. Never
  run `seed.sql`, DDL or destructive SQL against production.
- Secrets live in `environment.php` (gitignored, mounted read-only into the
  container) or `DB_*`/`MAIL_*`/`CAPTCHA_*` env vars. Never read, print or
  commit them.

## Layout

| Path | What lives there |
| --- | --- |
| `public/index.php` | Front controller. Only `public/` is web-served. |
| `src/App.php` | Kernel: security headers, session, CSRF check on **every** POST, error pages, `/healthz`. |
| `src/Routes.php` | The complete route table. |
| `src/Controllers/` | Request handlers. Authorisation checks happen here. |
| `src/Repository/` | **All** SQL. Prepared statements only. |
| `src/Security/` | `Auth`, `Csrf`, `Passwords` (legacy MD5 → `password_hash` upgrade), `SecurityHeaders` (CSP). |
| `src/Services/` | `Captcha`, `Mailer`, `HtmlSanitizer` (HTMLPurifier), `ImageUploads`. |
| `src/Support/` | Small pure helpers (`Url`, `Images`, `Text`, `Time`, `Flash`). |
| `templates/` | Plain PHP views: `layout.php`, `partials/`, one folder per area. |
| `database/` | `schema.sql` (legacy baseline), `seed.sql` (dev only), `migrations/`. |
| `docker/` | `apache.conf`, `php.ini`, `entrypoint.sh` (runs migrations on start). |
| `docs/security-audit.md` | The 2026 audit: every finding and how it was fixed. |

Old URLs (`/views/...`, `/?action=Comment&ideaID=`) are 301-redirected by
`src/Http/LegacyRoutes.php`.

## Commands

```bash
# Run the app locally (app :8080, Mailpit :8025, MySQL :3307). Accounts: top of database/seed.sql
docker compose -f docker-compose.dev.yml up --build -d

# Lint + PHPStan (level 6) + PHPUnit — run before saying a change is done
MSYS_NO_PATHCONV=1 docker run --rm -v "$(pwd -W)":/app -w /app composer:2 composer check   # Git Bash on Windows
docker run --rm -v "$PWD":/app -w /app composer:2 composer check                           # macOS / Linux

# Rebuild CSS after adding Tailwind classes (the Docker build does this too)
npm run build:css
```

The dev stack bakes code into the image: rebuild (`up --build -d`) after edits.
Use the `local-dev` skill for details, `prod-triage` when the live site is
broken, `add-migration` for schema changes and `audit-security` before
finishing any change that touches input, output, auth, SQL or uploads.

## Non-negotiable rules

Details live in `.claude/rules/` and load automatically for matching files.

1. **SQL**: only in `src/Repository/`, always bound parameters. Never put
   request data into SQL text. Identifiers such as `ORDER BY` come from a
   whitelist (see `IdeaFilters::SORTS`).
2. **Output**: escape every dynamic value with `e()`. The only raw output
   allowed is `$sanitizer->render()` for idea text and HTML from `$view->partial()`.
3. **Forms**: every POST form includes `<?= $csrf->field() ?>`. State changes
   are POST only, never GET.
4. **Authorisation** is checked server-side in the controller
   (`requireUser()`, `authorize($auth->can…())`). Identity always comes from the
   session (`$auth->id()`), never from a form field.
5. **No inline JavaScript** (`<script>` blocks, `onclick=`, `javascript:`):
   the CSP blocks it. Behaviour goes in `public/assets/app.js` using `data-*` hooks.
6. **Uploads** go through `ImageUploads` only. Never build file paths from
   request data.
7. **Request data** is read via `App\Http\Request` typed accessors, not raw
   superglobals.
8. Keep `composer check` green and add a test in `tests/` for every security
   fix or new validation rule.

## Conventions

- `declare(strict_types=1);` everywhere. PSR-12-style formatting, `final` classes.
- Source must remain PHP 8.1-compatible (`config.platform.php`), even though
  the image runs 8.4.
- Database table and column names keep their legacy casing (`Author.Author_ID`,
  `Idea.IdeaText`, `Vote.VoteNumber`, ...). MySQL on Linux is case-sensitive.
- User-facing errors are friendly; details go to `error_log()` (container logs).
  Never log passwords, hashes, tokens or email addresses.
- Role IDs are strings in `App\Security\Roles`. Only Site Administrators may
  grant the Site Administrator role.
