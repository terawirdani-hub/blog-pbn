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

`templates/*.php` plus `templates/layouts/` and `templates/partials/`. The public homepage reads `active_template` (`01`–`30`) from settings, which maps to `homepage_layout` + `color_palette` (5×6 presets). The catalog lives in `theme_presets()` (`includes/theme.php`) as an explicit list; the admin picker posts the `t01`–`t30` form codes, which `normalize_template_id()` reduces to the stored id. Legacy `template_style` still maps if those keys are empty. Settings tabs accept Indonesian aliases (`tampilan`, `umum`, `penulis`, `iklan`) and redirect back with `?status=success` after a save.

`router.php` (built-in server only) denies the same paths Apache blocks through the per-directory `.htaccess` files: `database/`, `includes/`, `lang/`, `templates/`, `tests/`, `docs/`, `scripts/`, `config.php`, `*.sqlite*`, `*.md`, and `admin/_*.php`. Keep both lists in sync when adding a sensitive folder. Template partials fatal-error when loaded without bootstrap, so `templates/` must stay denied. Bootstrap runs on every request, so `ensure_setting_defaults()` and `seed_admin_if_no_users()` must stay read-only when the rows already exist.

`base_url()` resolves in this order: the `site_url` setting when it is a valid URL, otherwise the request scheme (`X-Forwarded-Proto`, then `HTTPS`/port 443) plus host (`X-Forwarded-Host`, then `Host`, rejected unless it matches `[A-Za-z0-9.-]+(:\d+)?`). Everything public and absolute — canonical, Open Graph, sitemap, RSS, `admin_url()` — flows through it.

## Logging

User-facing errors: translated messages. Server: `error_log()`. Stack traces only if `config.php` `debug` is true.
