# Agent instructions — Turbo PBN

Turbo PBN is a PHP 8 + SQLite blog template. Operators copy the folder, upload a ZIP, and manage the site from `/admin`. Chat with the product owner in **Bahasa Indonesia**. Code, comments, commits, and technical docs stay in **English**.

## Rule precedence

1. `docs/plans/roadmap.md`
2. `MASTER_PLAN.md`
3. This file
4. `docs/rules/*`
5. `.cursor/rules/*`

## Non-negotiables

- Operational settings go through the admin UI, never new hardcoded values in PHP.
- `config.php` is bootstrap only (paths, timezone, debug flag).
- Every new admin field needs an i18n key (ID+EN) and an info tooltip.
- Styles use CSS variables from `assets/css/tokens.css`.
- Fail closed: CSRF on all POST mutations; unpublished posts are not public; last admin cannot be removed.
- After code changes, update the matching files under `docs/` in the same task.

## Layout

Public front controller: `index.php`. Shared PHP: `includes/`. Admin pages: `admin/*.php`. Translations: `lang/id.php`, `lang/en.php`. Schema: `includes/schema.php`.

## Local run

```text
php -S localhost:8080 router.php
```

Open `/admin` (login is `/admin` or `/admin/index.php`, not `/admin/login.php`). Default user `admin` / `password123` is seeded if missing. Do not commit `database/data.sqlite`, `uploads/*` (except placeholders), or extra secrets.

## Deploy

Shared hosting: extract at the domain document root (Apache + `mod_rewrite`). Pack with `scripts/pack.ps1`. There is no separate staging app.
