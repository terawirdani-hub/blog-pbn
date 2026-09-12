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

Key/value table `settings`. Never add a new operational constant in PHP. Defaults live in `includes/settings.php` (`setting_defaults()`). Homepage SEO fields (`home_meta_title`, `home_meta_description`, `home_meta_keywords`, `og_default_image_path`, webmaster verification) are edited on Settings → Site SEO.

## Templates

`templates/*.php` plus `templates/layouts/` and `templates/partials/`. The public homepage reads `active_template` (`01`–`30`) from settings, which maps to `homepage_layout` + `color_palette` (5×6 presets). Legacy `template_style` still maps if those keys are empty.

## Logging

User-facing errors: translated messages. Server: `error_log()`. Stack traces only if `config.php` `debug` is true.
