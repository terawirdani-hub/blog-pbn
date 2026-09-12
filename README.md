# Turbo PBN

Drop-in PHP blog template for PBN sites. Copy the project, upload a ZIP to your domain, finish setup in the admin panel, then publish posts and blogroll links.

## Requirements

- PHP 8.0+ with PDO SQLite
- Apache + `mod_rewrite` on hosting (or PHP built-in server locally)

## Local preview

```bash
php -S localhost:8080 router.php
```

Open [http://localhost:8080/admin](http://localhost:8080/admin). If the database is empty, a setup form creates the first **admin** account.

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
4. Visit `https://your-domain/admin` and complete setup.

`config.php` only stores paths, timezone, and a debug flag. Do not put the site name or API keys there.

## Security notes

- Default admin is created only during setup — there is no shipped password.
- `database/` is blocked from HTTP. PHP cannot run from `uploads/`.
- Keep PHP `debug` in `config.php` set to `false` on public hosts.

## Documentation

- Product rules: `MASTER_PLAN.md`
- Agent notes: `AGENTS.md`
- Specs: `docs/specs/`
- Roadmap: `docs/plans/roadmap.md`
