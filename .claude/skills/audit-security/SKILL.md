---
name: audit-security
description: Security checklist for reviewing a change to the Ideal Ideas PHP app (SQL injection, XSS, CSRF, authorisation/IDOR, uploads, email, redirects, secrets, infrastructure) plus the commands to prove each property. Use before finishing any change that touches request handling, templates, SQL, auth, uploads, email, Docker or CI, or when asked for a security review of this repo.
---

# Security review for Ideal Ideas

Background: `docs/security-audit.md` lists every vulnerability found in the
2022 code. This checklist stops them coming back. Review the diff
(`git diff main...HEAD`) against each section and report findings with file:line.

## 1. SQL injection
- [ ] New SQL lives in `src/Repository/` and every value is bound (`:name` or `?`).
- [ ] No request data in SQL text: `ORDER BY`, `LIMIT`, table and column names come
      from constants or whitelists (`IdeaFilters::SORTS`).
- [ ] `LIKE` input is escaped (`addcslashes($s, '%_\\')`).
- Prove it: `grep -rnE '\$_(GET|POST|REQUEST|COOKIE)' src templates` must return nothing.

## 2. XSS
- [ ] Every `<?= ... ?>` in templates is `e(...)`, `(int)`, `$view->partial()`,
      `$csrf->field()` or `$sanitizer->render()`.
- [ ] Attributes are double-quoted. URLs go through `Images::src()` / `Url::to()` and then `e()`.
- [ ] No inline `<script>`, `on*=` handlers or `javascript:` URLs (the CSP would block them anyway).
- [ ] Email bodies escape every interpolated value.
- Prove it: `grep -rn '<?=' templates | grep -vE 'e\(|\(int\)|partial\(|field\(\)|render\(|\?>$'` and inspect the hits.

## 3. CSRF and HTTP verbs
- [ ] State changes are POST routes only. Every POST form has `<?= $csrf->field() ?>`.
- [ ] Nothing bypasses the central check in `App::handle()`.

## 4. Authentication and authorisation
- [ ] Handlers call `requireUser()` and then `authorize($auth->can…())` *before* doing work.
- [ ] Acting user = `$auth->id()`, never a form field (the old code trusted a hidden `Author` input).
- [ ] Owner-only actions also filter by owner in SQL.
- [ ] Role changes: only Site Administrators can grant `Site Administrator` or edit one.
- [ ] Passwords use `Passwords::*`. No hashes or passwords in sessions, logs or templates.
- Test it: log in as a plain user and replay the request against another user's ID (expect 403/404).

## 5. Files and uploads
- [ ] Uploads only via `ImageUploads::store()`. Deletions only via `ImageUploads::delete()`,
      for a filename read from the database.
- [ ] No `unlink`, `move_uploaded_file`, `file_put_contents`, `include` or `require` on input-derived paths.

## 6. Redirects, email and outbound requests
- [ ] Redirect targets from input go through `Url::safeRedirect()`.
- [ ] Mail recipients come from the database or config, not from the form. User input goes in Reply-To at most.
- [ ] Outbound HTTP has a timeout and never puts secrets in URLs (POST bodies instead).

## 7. Secrets, errors and infrastructure
- [ ] No secrets in code, compose files, the image or logs. `environment.php` and `.env*` stay gitignored and dockerignored.
- [ ] Errors shown to users are generic. Details go to `error_log()`.
- [ ] `docker/apache.conf` still serves only `public/`, with no indexes and no PHP in uploads.
      `docker/php.ini` keeps `display_errors=Off` and the DB read timeout.
- [ ] GitHub Actions are pinned to SHAs, with `permissions: contents: read`, and deploy `needs: ci`.

## 8. Verify
```bash
MSYS_NO_PATHCONV=1 docker run --rm -v "$(pwd -W)":/app -w /app composer:2 composer check   # add a regression test for each fix
docker compose -f docker-compose.dev.yml up --build -d
curl -s -D - -o /dev/null http://127.0.0.1:8080/ | grep -iE 'content-security|x-frame|set-cookie'
```
The cookie must be `HttpOnly; SameSite=Lax`, and the CSP `script-src` must contain no `'unsafe-inline'`.
