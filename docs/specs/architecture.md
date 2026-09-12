# Architecture

## Request flow

1. Apache (or `router.php`) sends public URLs to `index.php`.
2. `includes/bootstrap.php` loads `config.php`, starts a secure session, opens SQLite, runs migrations, loads settings and i18n.
3. Admin scripts live under `admin/` as discrete PHP pages (not a framework router).
4. Public routes: `/`, `/page/{n}`, `/category/{slug}`, `/{slug}`, `/sitemap.xml`, `/robots.txt`.

## Persistence

- SQLite one file: `database/data.sqlite` (created on first request/setup).
- WAL mode + busy timeout to reduce writer lock errors.
- Schema versions in `schema_migrations`; add new versions in `includes/schema.php` only.

## Settings

Key/value table `settings`. Never add a new operational constant in PHP. Defaults live in `includes/settings.php` (`setting_defaults()`).

## Templates

`templates/*.php` receive variables from `index.php`. Style is a setting (`template_style`: `classic` | `magazine`) plus CSS variables from color settings.

## Logging

User-facing errors: translated messages. Server: `error_log()`. Stack traces only if `config.php` `debug` is true.
