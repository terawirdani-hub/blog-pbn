# Security rules

- Never commit `database/data.sqlite`, uploaded files, or passwords.
- Use PDO prepared statements only.
- Escape output with `h()` unless the value is intentionally raw admin HTML (ads/tracking) and the page is admin-only or the slot is meant to render HTML.
- CSRF token on every state-changing POST.
- `session_regenerate_id(true)` on login and setup.
- Passwords: `password_hash` / `password_verify`; min 10 characters.
- Uploads: images only (jpeg/png/gif/webp); random stored name; deny PHP execution in `uploads/` and deny HTTP to `database/`.
- Strip `<script>`, `<iframe>`, `on*` attributes from post HTML. Do not strip tracking/ad settings (admin-only raw HTML).
- Login: 5 failures / 15 minutes / IP+username → lock with a generic error (do not reveal whether the user exists).
- Roles: check on every admin page via `require_login()` / `require_role()`.
