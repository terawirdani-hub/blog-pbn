# Public routes and admin pages

No JSON API in v1. Forms POST to the same admin page.

## Public

| URL | Behavior |
|-----|----------|
| `/` | Homepage post list |
| `/page/{n}` | Pagination, n ≥ 1; `/page/1` redirects to `/` |
| `/{slug}` | Published post; unknown or draft → 404 |
| `/sitemap.xml` | Published posts |
| `/robots.txt` | Body from settings |
| `/uploads/...` | Static files |

Pretty URLs require Apache rewrite or `php -S ... router.php`.

## Admin (session required except setup/login)

| Path | Roles |
|------|--------|
| `/admin/setup.php` | Unauthenticated, only if zero users |
| `/admin/index.php` | Login or dashboard |
| `/admin/logout.php` | Any logged-in |
| `/admin/posts.php`, `post-edit.php` | admin, editor |
| `/admin/blogroll.php` | admin, editor |
| `/admin/settings.php` | admin |
| `/admin/users.php` | admin |
| `/admin/audit.php` | admin |
| `/admin/backup.php` | admin |
| `/admin/profile.php` | any logged-in |

All POST bodies must include `csrf_token`.
