# Master Plan: Turbo PBN

> Generated: 2026-09-10 17:25:02  
> Updated: 2026-09-12  
> Slug: `turbo-pbn`  
> This document is the foundation for AI-assisted development. Keep it concise; put detailed specs in `docs/specs/`.

---

## 1. Project Identity

| Field | Value |
|-------|-------|
| **Name** | Turbo PBN |
| **Summary** | Drop-in dynamic blog template for Private Blog Network sites. Copy the project, upload a ZIP to a domain, then run the site from the admin panel (posts, blogroll, appearance, ads, tracking). |
| **Goals** | 1) A new domain is live after ZIP extract + first-run setup. 2) Daily operations happen in `/admin` (no code edits). 3) SEO-friendly URLs, on-page SEO, and a sidebar blogroll for PBN links. 4) Admin UI is bilingual (ID/EN), Dark+Light, with info tooltips. |
| **Motivation / Why** | Spin up many blog sites quickly without a developer on each domain. One master template, many copies. |

**Daily workflow (current):**

1. Copy this project folder (or the packed ZIP).
2. Upload and extract at the domain document root.
3. Open `https://domain/admin` → complete first-run setup (create admin account).
4. In admin: set site name, colors, template style, ads, tracking → write posts → fill blogroll.

`config.php` is **bootstrap only** (paths, timezone). It is not used for site name, colors, or other operational settings.

---

## 2. Language & Communication Conventions

| Context | Language |
|---------|----------|
| User ↔ AI chat | Bahasa Indonesia |
| Code, comments, commits | English |
| Technical documentation | English |
| UI copy (user-facing) | i18n — Indonesian + English (see Section 8) |

---

## 3. Technology Stack

| Field | Value |
|-------|-------|
| **Status** | Decided |
| **Runtime** | PHP 8.0+ (no Composer, no Node build) |
| **Database** | SQLite 3 via PDO (`database/data.sqlite`, created on first run) |
| **Web server** | Apache with `mod_rewrite` (production). PHP built-in server + `router.php` (local). |
| **Frontend** | Server-rendered PHP + centralized CSS variables. No SPA framework. |
| **Notes** | Chosen so a ZIP extract on typical shared hosting works immediately. |

---

## 4. Repository Structure

```
project-root/
├── README.md
├── AGENTS.md
├── MASTER_PLAN.md
├── .env.example              # Documents that .env is unused; bootstrap is config.php
├── .htaccess                 # SEO URLs + protect sensitive paths
├── router.php                # PHP built-in server router
├── config.php                # Bootstrap paths/timezone only
├── index.php                 # Public front controller
├── includes/                 # Shared PHP (auth, db, i18n, settings)
├── lang/                     # id.php / en.php
├── admin/                    # Dashboard
├── templates/                # Public layout partials + layouts/ (5 homepage frames)
├── assets/                   # Public CSS (tokens + themes)
├── database/                 # SQLite file (gitignored) + deny-all htaccess
├── uploads/                  # Images (gitignored) + PHP execution denied
├── scripts/pack.ps1          # Build deploy ZIP
├── .cursor/rules/
└── docs/
    ├── plans/roadmap.md
    ├── specs/
    ├── rules/
    ├── glossary.md
    └── changelog.md
```

---

## 5. AI & Cursor Guidelines

### 5.1 Rule Precedence (when instructions conflict)

1. `docs/plans/roadmap.md`
2. This master plan (`MASTER_PLAN.md`)
3. `AGENTS.md`
4. `docs/rules/*`
5. `.cursor/rules/*`

### 5.2 Recommended Cursor Rules (categories)

| Rule file | Purpose |
|-----------|---------|
| `global` | Project context, language, doc sync, post-task actions |
| `architecture` | Zero hardcoding, logging, type safety, patterns |
| `security` | Secrets, auth, input validation |
| `agent-workflows` | New task, deploy, incident handling |

### 5.3 Working with AI

- **Product owner:** Vibe coder — does not need to understand code syntax.
- **AI role:** Act as a professional IT consultant; explain in simple language, avoid unexplained jargon.
- **Default approach:** Propose solutions operable via UI, not "edit file X line Y".
- **For every new feature, ask:** Does this need to be configurable from admin panel?
- **Open questions:** List in Section 10 — do not guess in code.

---

## 6. Development Principles

### 6.1 Configuration-First (No-Code Operations)

- All operational parameters must be changeable from within the application (admin/settings UI).
- Product owner must **not** be asked to edit code, `.env`, or database manually for routine operations.

### 6.2 Zero Hardcoding

- Values that can change → database settings managed via UI.
- `config.php` is only infrastructure bootstrap (paths, timezone).

### 6.3 Self-Explanatory UI (Info Tooltips) — **Mandatory**

- Every form field, filter, and important table column must have an info tooltip.
- Tooltips must support i18n (ID/EN).
- Format: short title + 1–2 sentences explaining what the field is for.

### 6.4 UI Consistency (Centralized Styling)

- Colors, spacing, typography → CSS variables in `assets/css/tokens.css`.
- Admin and public themes consume the same token file.

### 6.5 Documentation Sync

- When code changes, update related documentation in the same task.

### 6.6 Fail Closed

- Unpublished posts are never served on the public site.
- Mutations require a valid CSRF token; invalid token → abort, no write.
- Login locks after repeated failures.
- File uploads: images only; PHP in `uploads/` cannot execute.
- Database directory is not web-accessible.
- The last admin account cannot be deleted or demoted.
- Config import rejects invalid JSON and does not apply a partial destructive replace.

### 6.7 Build Order

- Core logic first; additional template styles and extras later.

### 6.8 Future-Proof Mindset

- Schema migrations table; settings as key/value; roles ready for more than one admin.

---

## 7. Access Control & Configuration

### 7.1 Authentication & Roles

| Role | Access |
|------|--------|
| **admin** | Full: posts, blogroll, settings, users, audit log, backup |
| **editor** | Posts and blogroll only; own profile (language/theme/password) |

### 7.2 Admin Config vs User Preferences

| Type | Who can change | Examples | Audit log |
|------|----------------|----------|-----------|
| **Admin Config** | admin role | Site name, colors, ads, tracking, template style, feature flags | **Yes** |
| **User Preferences** | Each logged-in user (own account) | UI language, admin theme | No |

### 7.3 Sensitive Data in UI

- Passwords never displayed. Tracking/ad HTML is admin-only (intentional raw HTML).
- Admin config changes are recorded in the audit log.

### 7.4 Config Export / Backup

- **Config JSON:** settings export/import from Backup page. Secret-flagged keys omitted on export.
- **Database download:** full SQLite file for disaster recovery (admin only).

---

## 8. UI/UX Standards

### 8.1 Internationalization (i18n) — **Mandatory**

- Languages: **Indonesian + English**.
- Admin UI follows the logged-in user’s language.
- Public site language is an admin setting (`site_locale`).
- Preference persists in the user row (admin) or cookie (login screen / public theme toggle).

### 8.2 Theme Mode

| Setting | Value |
|---------|-------|
| **Mode** | Dark + Light |
| **Admin default** | Dark |
| **Public default** | Light (overridable in Settings; visitors can toggle; stored in cookie) |

### 8.3 Info Tooltips — **Mandatory**

See Section 6.3.

### 8.4 Design Direction

- Modern, clean, editorial public site (Tailwind). One public layout: featured story + two-column feed + sticky sidebar. The classic/magazine setting is kept in admin for compatibility and does not switch public CSS.

### 8.5 User-Friendly Error Messages

- Users see plain language + next step.
- Stack traces only in server logs when `debug` is enabled in `config.php` (default off).

---

## 9. Configuration Map

| Setting | Description | Changed by | Notes |
|---------|-------------|------------|-------|
| Site name, tagline, footer | Branding | admin | |
| Logo, favicon | Appearance | admin | Uploads |
| Public locale | ID or EN for the blog | admin | |
| Public theme default | Light or Dark | admin | Visitors may override |
| Template style | Maps to layout (`classic` → newspaper, `magazine` → magazine) | admin | Kept for older copies |
| Homepage layout | magazine, tech, bento, newspaper, masonry | admin | |
| Color palette | slate, crimson, emerald, violet, amber, mono | admin | Also writes primary/accent |
| Colors (primary, accent) | Appearance | admin | Synced from palette |
| Posts per page | Homepage pagination | admin | |
| Homepage intro | HTML/text above the list | admin | |
| Ad slots | Header / sidebar / in-article / footer HTML | admin | Raw HTML |
| Tracking | Head / body snippet | admin | Raw HTML, admin-only |
| Robots.txt body | Crawler rules | admin | Served at `/robots.txt` |
| Homepage meta title/description/keywords | Site SEO tab | admin | Empty title falls back to name + tagline |
| Default Open Graph image | Site SEO tab | admin | Used on homepage and posts without an image |
| Google / Bing verification | Site SEO tab | admin | Meta tags in public `<head>` |
| Feature: comments | Reserved flag (off in v1) | admin | |
| Author name, bio, avatar | Sidebar author card | admin | |
| Social profile URLs | Author card links | admin | |
| Categories | Names, slugs, badge colors | admin/editor | Assigned on each post |

---

## 10. Open Questions

Resolved 2026-09-12:

- **Stack:** PHP 8.0+ / SQLite / Apache (see Section 3).
- **Layout:** repository = deployable site + `docs/` (see Section 4).
- **Deploy:** ZIP to document root; local `php -S localhost:8080 router.php`.
- **Categories:** shipped with the editorial frontend (admin + `/category/{slug}`).

Still open (do not implement until discussed):

- Comment system
- Extra paid template packs
- Multi-author public bylines beyond the site author card

---

## 11. Definition of Done (for Non-Coder QA)

- [ ] Feature is accessible from the correct menu/navigation
- [ ] Info tooltips exist and are clear (ID + EN)
- [ ] i18n works — switch language, all labels update
- [ ] Theme behaves as defined in Section 8.2
- [ ] Admin can change related settings from UI (no code/deploy needed)
- [ ] Sensitive values are masked; change flow works
- [ ] Config changes appear in audit log
- [ ] Error messages are user-friendly (no raw stack traces)
- [ ] UI looks consistent with other menus/tabs (centralized styling)
- [ ] Related documentation updated

---

## 12. Post-Task Checklist (for AI / Developer)

- [ ] Code matches agreed conventions (Section 6)
- [ ] Related docs updated (Section 6.5)
- [ ] No secrets committed
- [ ] Tested / verified as applicable
- [ ] No obvious race conditions, bugs, or conflicts
- [ ] Tooltips and i18n keys added for any new user-facing fields

---

*End of Master Plan — detailed specs belong in `docs/specs/`, not here.*
