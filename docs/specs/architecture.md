# Architecture

## Request flow

1. Apache (or `router.php`) sends public URLs to `index.php`.
2. `includes/bootstrap.php` loads `config.php`, starts a secure session, opens SQLite, runs migrations, loads settings and i18n.
3. Admin scripts live under `admin/` as discrete PHP pages (not a framework router).
4. Public routes: `/`, `/page/{n}`, `/category/{slug}`, `/{slug}`, `/sitemap.xml`, `/rss.xml`, `/robots.txt`.

## Persistence

- SQLite one file: `database/data.sqlite` (created on first request/setup).
- WAL mode + busy timeout to reduce writer lock errors.
- Schema versions in `schema_migrations`; add new versions in `includes/schema.php` only.

## Settings

Key/value table `settings`. Never add a new operational constant in PHP. Defaults live in `includes/settings.php` (`setting_defaults()`).

## Templates

`templates/*.php` plus `templates/layouts/` and `templates/partials/`. Homepage layout and palette come from settings (`homepage_layout`, `color_palette`; 5×6 = 30 presets). Legacy `template_style` (`classic` | `magazine`) still maps if the new keys are empty. SEO head tags, JSON-LD, sitemap, and RSS are built in `includes/seo.php`.

## Logging

User-facing errors: translated messages. Server: `error_log()`. Stack traces only if `config.php` `debug` is true.
