# Roadmap — Turbo PBN

Single source of truth for direction. Update this file when priorities change.

## Now (v1) — shipped in this repository

- First-run setup wizard (create admin)
- Auth: login lockout, CSRF, roles admin/editor
- Posts with slug URLs, drafts, on-page SEO, featured image
- Categories (admin + public badges, nav, `/category/{slug}`)
- Blogroll widget
- Settings: branding, colors, author card, template style, ads, tracking, robots
- Admin i18n ID/EN, Dark/Light, tooltips
- Public Dark/Light toggle
- Editorial public theme (Tailwind CDN)
- Audit log
- Config JSON export/import + SQLite download
- `sitemap.xml` and `robots.txt`

## Next (only after product owner asks)

- Tags
- Comments
- Additional public template styles
- Image optimization / media library
- Multi-site control panel (one dashboard for many domains)

## Explicitly out of scope for v1

- Composer/Node build pipeline
- Remote MySQL (SQLite is the default; do not add a second database without a plan update)
- Public user registration
