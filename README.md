# Turbo PBN

Drop-in PHP blog template for PBN sites. Copy the project, upload a ZIP to your domain, finish setup in the admin panel, then publish posts and blogroll links.

## Requirements

- PHP 8.0+ with PDO SQLite
- Apache + `mod_rewrite` on hosting (or PHP built-in server locally)

## Local preview

```bash
php -S localhost:8080 router.php
```

Open [http://localhost:8080/admin](http://localhost:8080/admin) (same page as `/admin/index.php` — there is no `/admin/login.php`).

Default login (created automatically if missing):

- Username: `admin`
- Password: `password123`

Change this password in **Profile** after the first sign-in. To recreate or reset it: `php scripts/seed-admin.php --reset`.

## Daily use (no code)

1. **Settings** — site name, colors, template style (classic / magazine), ads, tracking, robots.txt
2. **Posts** — write articles and on-page SEO
3. **Blogroll** — sidebar links
4. **Users** — extra admin or editor accounts
5. **Backup** — download config JSON or the SQLite file

Language (Indonesian / English) and Dark / Light are in **Profile** for the admin UI. Visitors can toggle Light/Dark on the public site.

## Deploy

1. Run `scripts/pack.ps1` (or zip the project excluding `.git`, `docs` is optional).
2. Upload and extract at the **document root** of the domain.
3. Ensure `database/` and `uploads/` are writable.
4. Visit `https://your-domain/admin` and sign in (`admin` / `password123`), then change the password.

`config.php` only stores paths, timezone, and a debug flag. Do not put the site name or API keys there.

## Security notes

- Default login is `admin` / `password123` (seeded if that user is missing). Change it immediately on a public host.
- `database/` is blocked from HTTP. PHP cannot run from `uploads/`.
- Keep PHP `debug` in `config.php` set to `false` on public hosts.

## Documentation

- Product rules: `MASTER_PLAN.md`
- Agent notes: `AGENTS.md`
- Specs: `docs/specs/`
- Roadmap: `docs/plans/roadmap.md`
