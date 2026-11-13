# Security audit: Ideal Ideas, November 2026

**Scope:** the full application as of commit `1dd8e05` (the 2022 code), its
Docker and Apache setup, and the GitHub Actions deploy pipeline. The live site
was probed from outside only (no server access).

**Result:** 27 findings. **7 critical** (unauthenticated SQL injection,
unauthenticated arbitrary file deletion and stored XSS, broken authentication
on writes, MD5 passwords), **8 high**, **8 medium** and **4 low**. All except
the four marked *Recommendation* are fixed on branch
`refactor/security-hardening`. Each fix is covered by unit tests (`tests/`) and
was replayed against the running app (see "Verification").

Severity reflects impact and how easily the issue can be exploited. File paths in
**Before** refer to the old code; **After** names the code that replaces it.

---

## Outage diagnosis (why the site is down)

| Check | Result |
| --- | --- |
| DNS / Cloudflare edge | ✅ resolves to Cloudflare; `/cdn-cgi/trace` answers instantly |
| Origin server + container | ✅ up: PHP 8.0.26 responds |
| Page load | ❌ every request takes **exactly 60.1 s** and returns a truncated "Error Page" |

60 s is PHP's `default_socket_timeout`. The old code opened a MySQL connection
on every request with no timeout. **The database host is unreachable from the
server.** It may be down, its IP may have changed, a firewall may be dropping
traffic, or a free tier may have expired. After the timeout, the error page
included `views/auth/login/index.php`, which re-included `alert.html.php` with
`include` instead of `include_once`. That caused a fatal "Cannot redeclare
generateAlert()". `error_reporting(0)` hid the error, so visitors got a blank
page after a minute.

**This cannot be fixed from the repository.** The database must be brought
back, or `environment.php` must be pointed at a working one. See the
`prod-triage` skill for the exact server commands. The refactor makes this
failure mode fast and visible (finding M6).

---

## Critical

### C1. Unauthenticated SQL injection via `orderBy`
- **Before:** `index.php` did `ORDER BY $_GET[orderBy]`.
- **Impact:** anyone could read the whole database (including password
  hashes) with boolean/time-based payloads such as
  `?orderBy=(SELECT IF(SUBSTR(Password,1,1)='a',IdeaDate,IdeaText) FROM Author LIMIT 1)`.
- **After:** sorting uses a fixed whitelist (`src/Ideas/IdeaFilters.php`,
  `SORTS`). Legacy `orderBy` values are mapped onto it and anything else falls
  back to the default.

### C2. Unauthenticated SQL injection and DoS via `limitRecords` / `page`
- **Before:** `LIMIT $start, $offset` built from raw `$_GET`.
  `?limitRecords=0` caused a `DivisionByZeroError`.
- **After:** page size must be one of 10, 20, 50 or 100, page is a positive
  integer clamped to the page count, and both are bound as `PARAM_INT`.

### C3. SQL injection in the comment page
- **Before:** `views/ideas/comment.php` ran `... AND IdeaID = $ideaID` with
  `$ideaID = $_GET['ideaID']`. Any logged-in user could exploit it.
- **After:** every query lives in `src/Repository/` with bound parameters, and
  native prepares are used (`ATTR_EMULATE_PREPARES=false`).

### C4. Unauthenticated arbitrary file deletion
- **Before:** `views/ideas/edit.php` did `unlink($destination . $_POST['fileInputName'])`
  before any login or ownership check. A single
  `POST /?editform` with `fileInputName=../../index.php` and any image deleted
  any file Apache could write. That was enough to take the site down or wipe
  every uploaded image.
- **After:** the client never names files. `ImageUploads::delete()` only
  deletes the filename recorded in the database for that idea, and only if it
  matches our upload naming patterns and resolves inside the upload directory.
  The handler requires login and ownership first.

### C5. Stored XSS on the home page (unauthenticated)
- **Before:** `components/card.html.php` echoed `Idea.Image` raw into `src="..."`.
  The only validation was `str_contains($image, 'https://')`, so
  `x" onerror="alert(document.cookie)" a="https://` ran script for every visitor.
  The uploaded file's original name was also echoed raw.
- **After:** `Images::src()` normalises stored values (absolute URLs or bare
  filenames), and every attribute is escaped with `e()`. New links must be valid
  `https://` URLs (`Images::isValidRemoteUrl()`). Uploads get random,
  server-chosen names. A strict CSP blocks inline script as a second layer.

### C6. Missing authentication and author impersonation on idea writes
- **Before:** `ideasAddSubmit()` and `ideasEditSubmit()` had no login check,
  and the author came from a hidden `Author` form field. Anyone could post as
  anyone, logged in or not.
- **After:** `requireUser()` plus `canEditIdea()` run on every write. The author
  is always `$auth->id()` from the session.

### C7. Unsalted MD5 password hashing
- **Before:** `md5($password . 'ijdb')`, the same static salt for every user.
  Together with C1, every account was crackable within minutes.
- **After:** `password_hash()` (bcrypt, cost 12). Legacy hashes are verified
  in constant time and **upgraded automatically at next login**. A migration
  widens `Author.Password` first. Login timing is equalised for unknown emails.

## High

### H1. Stored XSS in the admin area leading to admin takeover
- **Before:** `views/admin/author/authors.html.php` echoed author names raw, and
  anyone can choose their own name at registration. Session cookies lacked
  `HttpOnly`, so registering as `<script>…</script>` stole the admin's session
  as soon as they opened "Manage authors". Category names were also unescaped.
- **After:** all template output is escaped. Cookies are `HttpOnly`. CSP is enforced.

### H2. Reflected XSS
- **Before:** the search box, every pagination hidden input (`orderBy`,
  `limitRecords`, `Author`, `Category`, `text`, `page`), the register form
  (`Name`, `Email`), and `$_SERVER['PHP_SELF']` written into a JavaScript
  redirect (`isMobile/index.php`, e.g. `/index.php/";alert(1);//`).
- **After:** all output is escaped. The mobile-detection redirect hack is gone.
  Layout is responsive CSS instead.

### H3. No CSRF protection
- **Before:** no tokens anywhere. A malicious page could make a logged-in admin
  delete users, change passwords or grant roles.
- **After:** a synchroniser token on every form, verified centrally for every
  POST (`App::handle()`). Cookies are `SameSite=Lax`.

### H4. Privilege escalation by Account Administrators
- **Before:** an Account Administrator could grant themselves *Site
  Administrator*, and could change any Site Administrator's password.
- **After:** only Site Administrators can grant that role or edit or delete a Site
  Administrator. Admins cannot delete their own account (`Admin/AuthorController`).

### H5. IDOR on idea children
- **Before:** `delete.php` deleted an idea's categories, departments, comments
  and votes **even when the ownership check on the idea failed**. `edit.php`
  rewrote any idea's categories and departments without checking ownership.
- **After:** authorisation runs before any write. All related rows are
  changed in one transaction.

### H6. Contact form was an open mail relay
- **Before:** it sent arbitrary HTML from the owner's Gmail account **to the
  address typed into the form**, with an unescaped name and subject. That was ideal for
  phishing, and likely to get the Gmail account suspended.
- **After:** messages always go to `CONTACT_TO` (the owner). The visitor's
  address is used only as Reply-To. All fields are escaped, CR/LF are stripped,
  and lengths are capped.

### H7. The whole repository was the web root
- **Before:** `./:/var/www/html` with Apache defaults made `/.git/`,
  `deploy-ideas-website.sh`, `docker-compose.yml`, `README.md`, library internals
  and directory listings (`/assets/img/`, `/connection/...`) publicly
  reachable.
- **After:** only `public/` is served. App code, vendor, config and
  `environment.php` sit outside it. Indexes are disabled, dotfiles are denied,
  and unknown paths go to the front controller (404).

### H8. Weak upload validation
- **Before:** extension-only check, the client filename was kept, files were
  stored in the web root, and the directory was listable.
- **After:** content is verified (`finfo` + `getimagesize`), JPG/PNG/GIF/WebP
  only, max 5 MB, random names. Apache refuses to execute or serve
  scripts, HTML or SVG from the upload directory and adds a sandboxing CSP and `nosniff`.

## Medium

| # | Issue (before) | Fix (after) |
| --- | --- | --- |
| M1 | Session cookies had no `HttpOnly`, `SameSite` or `Secure`; no strict mode; no ID regeneration at login (fixation); the email **and password hash** were kept in the session. | `Session::start()` hardens cookies (`Secure` behind HTTPS/Cloudflare), uses strict mode, and regenerates the ID at login and logout. The session holds only the user ID. Roles are re-read from the DB on every request. |
| M2 | `.htaccess` set `display_errors On`. The DB error (including host) was shown to visitors. `X-Powered-By: PHP/8.0.26` was sent. | `docker/php.ini` turns errors off and logs them to stderr. Visitors get generic pages. `expose_php=Off` and `ServerTokens Prod`. |
| M3 | No security headers (clickjacking, MIME sniffing, no CSP). | CSP (`script-src 'self'` + reCAPTCHA only), `frame-ancestors 'none'`, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` and HSTS. All inline JS was removed so the CSP can be strict. |
| M4 | The reCAPTCHA secret was sent in a GET URL, the response token wasn't URL-encoded, and there was no timeout. | POST body, 5 s timeout, fails closed. Always enforced in production. |
| M5 | The `PHP_SELF`-based redirect in the mobile-detection script (open redirect / XSS). | Removed entirely. |
| M6 | **Availability:** no DB timeout (60 s hang, i.e. the current outage), the error page itself crashed, no container restart policy, a 300 s SMTP timeout, and a mail failure aborted requests after the DB write. | 5 s connect timeout, 10 s read timeout (`mysqlnd.net_read_timeout`), cached failure leading to a styled 503 page with `Retry-After`, `/healthz` + Docker `HEALTHCHECK`, `restart: unless-stopped`, 10 s SMTP timeout, and mail failures logged without breaking the action. |
| M7 | **Supply chain:** unpinned CDN scripts without SRI (Tailwind Play CDN, `tailwindcss@latest`, jQuery, Flowbite); `appleboy/ssh-action@master`; vendored HTMLPurifier 4.12 (2019) and PHPMailer 6.6.4; PHP 8.0 (EOL Nov 2023). | Tailwind is compiled at build time and there is **no third-party JS** except reCAPTCHA. Composer locks HTMLPurifier 4.19.1 and PHPMailer 6.12.0, with `composer audit` in CI. Every Action is pinned to a commit SHA. PHP 8.4. |
| M8 | Deploy ran without tests, using a copy of the deploy script on the server that could drift from the repo. | `deploy.yml` needs CI (lint, PHPStan, PHPUnit, audit, Docker build) and runs the repo's own script. Concurrency guard. `permissions: contents: read`. |

## Low

| # | Issue | Status |
| --- | --- | --- |
| L1 | No length limits. Array-typed parameters (`?q[]=x`) caused 500s. | Fixed: typed `Request` accessors and server-side limits. |
| L2 | The origin is published on `0.0.0.0:8076`, so the site can be reached directly, bypassing Cloudflare. | **Recommendation:** if the reverse proxy runs on the host, bind `127.0.0.1:8076:80` (see the comment in `docker-compose.yml`), or firewall port 8076. Not changed, because the proxy topology couldn't be verified. |
| L3 | No login rate limiting besides reCAPTCHA. | **Recommendation:** add Cloudflare rate-limiting rules for `POST /login` and `/register`. |
| L4 | Registration reveals whether an email address already has an account. | Accepted: inherent to sign-up and low impact. |

---

## Actions for the owner

1. **Fix the database connection** (see "Outage diagnosis").
2. **Rotate secrets.** C1/C3 allowed reading the database for years, and with
   MySQL's `FILE` privilege `LOAD_FILE()` could have read `environment.php`.
   Change the **MySQL password**, the **Gmail app password** and the
   **reCAPTCHA secret**.
3. **Assume password hashes leaked.** Legacy MD5 hashes stay in the
   database until each user logs in again. Consider asking users to change
   their passwords, or invalidating old hashes once a password-reset feature
   exists (the app has none yet).
4. Apply the L2 and L3 recommendations.

## Verification

Run against the Docker dev stack (`docker-compose.dev.yml`) after the refactor:

- `composer check`: 78 files lint-clean, PHPStan level 6 with no errors, 59 PHPUnit tests (246 assertions) passing.
- Replayed payloads: C1/C2 SQLi (including `SLEEP(3)`, which returned in 9 ms, so it never ran),
  reflected and stored XSS (escaped or stripped), CSRF-less POSTs (403), the C4
  `fileInputName=../index.php` deletion (no effect), HTML and PHP disguised as
  images (rejected), a spoofed `Author` field (ignored), IDOR on another user's
  idea or comment (403/404), and Account Admin → Site Admin escalation (422/403).
- File exposure: `/.git/config`, `/environment.php`, `/src/*`, `/vendor/*`,
  `/deploy-ideas-website.sh` and `/assets/img/` all return 403/404.
- Outage simulation: unreachable DB gives a 503 page in 5.2 s, hung DB gives a 503 in 10.3 s, and `/healthz` reports it.
- Legacy MD5 login works and the hash is upgraded to bcrypt in place.
