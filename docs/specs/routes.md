# Public routes and admin pages

No JSON API in v1. Forms POST to the same admin page.

## Public

| URL | Behavior |
|-----|----------|
| `/` | Homepage: featured post + two-column feed |
| `/page/{n}` | Pagination of the feed |
| `/category/{slug}` | Published posts in a category |
| `/category/{slug}/page/{n}` | Category pagination |
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
| `/admin/categories.php` | admin, editor |
| `/admin/blogroll.php` | admin, editor |
| `/admin/settings.php` | admin |
| `/admin/users.php` | admin |
| `/admin/audit.php` | admin |
| `/admin/backup.php` | admin |
| `/admin/profile.php` | any logged-in |

All POST bodies must include `csrf_token`.
