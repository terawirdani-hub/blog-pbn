# Changelog

## 2026-09-12

- Initial Turbo PBN v1: PHP/SQLite blog template, admin dashboard, i18n, themes, audit log, backup, SEO URLs.
- Editorial public frontend (Tailwind CSS CDN), category taxonomy, author card settings.
- Default admin seed (`admin` / `password123`); login is `/admin` (not `/admin/login.php`).
- SaaS admin shell, logo/favicon uploader (png/jpg/svg/ico/webp) with live preview.
- SEO engine: robots Discover directives, canonical, Open Graph/Twitter, JSON-LD (WebSite/Organization/NewsArticle/BreadcrumbList), `/rss.xml`, richer `/sitemap.xml`.
- Post SEO fields: focus keyword, title/description counters, schema type, index/noindex, custom canonical.
- 30 homepage templates (5 layouts × 6 palettes) selectable in Settings → Appearance (`active_template` 01–30).
- Site SEO tab: homepage meta title/description/keywords, default OG image, Google/Bing verification, live sitemap/RSS shortcuts.
- Appearance tab: explicit 30-preset catalog (`t01`–`t30`) in a grouped dropdown, save button with hint row, `?status=success` success alert, and `tab=tampilan` accepted as an alias for `tab=appearance`.
- Hardening pass: `router.php` now mirrors the `.htaccess` deny rules (the built-in server ignores them), `templates/` is denied on Apache too, the admin seed no longer hashes a password on every request, setting defaults are only written when a key is missing, and array-shaped query parameters no longer raise conversion warnings (`request_str()`).
- New `site_url` setting (Settings → General) drives absolute URLs; when empty, `base_url()` derives them from the request and honours `X-Forwarded-Proto`/`X-Forwarded-Host` with host validation.
- The default admin is now seeded only when the `users` table is empty (`seed_admin_if_no_users()`); use `scripts/seed-admin.php` to recover. Tracking snippets are shown in the form and can be cleared by saving an empty field. Appearance layout/palette cards are shortcuts that drive the preset dropdown and are no longer submitted.
- Reliability pass: the last-admin rule is enforced inside the DELETE/UPDATE statements (no read-modify-write race), profile/backup/category/blogroll writes report errors instead of returning HTTP 500, the default admin seed tolerates a concurrent first boot, saving General no longer resets `comments_enabled`, wildcard-only searches no longer match every post, and scheme-less canonical URLs stay external.
