# Data model

SQLite. Timestamps are UTC ISO-8601 (`Y-m-d H:i:s`).

## users

| Column | Type | Notes |
|--------|------|--------|
| id | INTEGER PK | |
| username | TEXT UNIQUE | 3–32 chars, `[a-zA-Z0-9_]` |
| password_hash | TEXT | `password_hash` / bcrypt |
| role | TEXT | `admin` or `editor` |
| locale | TEXT | `id` or `en` |
| theme | TEXT | `dark` or `light` |
| created_at, updated_at | TEXT | |

## settings

| Column | Type | Notes |
|--------|------|--------|
| key | TEXT PK | |
| value | TEXT | |
| is_secret | INTEGER | 1 = omit from config export |
| updated_at | TEXT | |
| updated_by | INTEGER | user id, nullable |

## posts

| Column | Type | Notes |
|--------|------|--------|
| id | INTEGER PK | |
| title, slug | TEXT | slug UNIQUE |
| excerpt, content | TEXT | content is HTML (admin-only authoring) |
| featured_image | TEXT | relative path under uploads |
| status | TEXT | `draft` or `published` |
| seo_title, seo_description, seo_keywords | TEXT | |
| canonical_url | TEXT | optional |
| og_image | TEXT | optional; fallback featured image |
| robots_index | INTEGER | 1 index, 0 noindex |
| published_at | TEXT | null until first publish |
| author_id | INTEGER | |
| created_at, updated_at | TEXT | |

## blogroll

| Column | Type | Notes |
|--------|------|--------|
| id | INTEGER PK | |
| title, url | TEXT | |
| rel | TEXT | e.g. `noopener noreferrer` |
| target | TEXT | `_blank` or `_self` |
| sort_order | INTEGER | ascending |
| is_active | INTEGER | |
| created_at, updated_at | TEXT | |

## audit_log

| Column | Type | Notes |
|--------|------|--------|
| id | INTEGER PK | |
| user_id | INTEGER | nullable (system) |
| action | TEXT | e.g. `settings.update` |
| entity | TEXT | `settings`, `post`, `user`, … |
| entity_id | TEXT | |
| details | TEXT | JSON |
| ip | TEXT | |
| created_at | TEXT | |

## login_attempts

Used for lockout. Rows older than the window are ignored; prune on login.

## schema_migrations

`version INTEGER PK`, `applied_at TEXT`.
