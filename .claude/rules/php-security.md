---
paths:
  - "src/**/*.php"
  - "public/**/*.php"
  - "bin/**/*.php"
---

# Security rules for PHP code

These rules exist because the 2022 code had every one of these bugs (see
`docs/security-audit.md`). Do not reintroduce them.

## Input
- Read request data only through `App\Http\Request` (`query()`, `input()`,
  `queryInt()`, `inputInt()`, `inputIntList()`, `inputStringList()`, `file()`).
  They coerce types, so `?id[]=1` can never reach SQL or string functions as an array.
- Normalise free text with `Text::singleLine()` (names, emails, subjects; strips
  CR/LF and so prevents header injection) or `Text::multiLine()` (comments, messages).
- Enforce maximum lengths server-side. HTML `maxlength` is not a control.
- Validate emails with `FILTER_VALIDATE_EMAIL` and image links with `Images::isValidRemoteUrl()`.

## Auth and authorisation
- Start every state-changing handler with `$this->requireUser()` and then
  `$this->authorize(...)` using `Auth::can*()`. Hiding a button is not authorisation.
- Take the acting user's ID from `$this->app->auth->id()`, never from POST data.
- For "owner only" actions, put the owner check in the SQL `WHERE` clause too
  (see `CommentRepository::deleteOwned()`).
- Passwords: `Passwords::hash()` / `Passwords::validate()` only. Never MD5/SHA,
  never store or log plaintext, and never keep hashes in the session.
- Call `$auth->login()` (which regenerates the session ID) on every privilege change.

## CSRF and HTTP
- `App::handle()` rejects any POST without a valid token. Never add a route
  that changes state on GET, and never bypass the check.
- Redirect targets taken from input must go through `Url::safeRedirect()`.
- Add new third-party script, frame or connect origins to
  `SecurityHeaders::for()` deliberately and explain why in the PR.

## Files and email
- Uploads: `ImageUploads::store()` (content-checked, random names) and
  `ImageUploads::delete()` (only deletes files we created). Never call
  `unlink()`, `move_uploaded_file()` or `file_put_contents()` on paths derived from input.
- Email: `Mailer::send()` only. Recipients come from the database or config,
  never from a form field (the contact form uses the visitor's address only as
  Reply-To). Escape every value placed in an HTML body with `e()`.

## Errors
- Throw `HttpException` for user-facing errors. Never echo exception messages
  or SQL errors to the browser. Log with `error_log()`, without personal data.
