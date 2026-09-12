<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$path = request_path();
$path = rtrim($path, '/') ?: '/';

if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    $body = setting('robots_txt');
    echo str_replace('{base}', rtrim(base_url(), '/'), $body);
    exit;
}

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $st = db()->query("SELECT slug, updated_at, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo '<url><loc>' . h(url_path()) . '</loc></url>' . "\n";
    foreach ($st as $row) {
        $loc = url_path($row['slug']);
        $last = $row['updated_at'] ?: $row['published_at'];
        echo '<url><loc>' . h($loc) . '</loc>';
        if ($last) {
            echo '<lastmod>' . h(substr((string) $last, 0, 10)) . '</lastmod>';
        }
        echo "</url>\n";
    }
    echo '</urlset>';
    exit;
}

if (preg_match('#^/category/([a-z0-9\-]+)(?:/page/(\d+))?$#', $path, $m)) {
    $page = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 1;
    if ($page <= 1 && isset($m[2]) && $m[2] !== '') {
        redirect(url_path('category/' . $m[1]));
    }
    render_home($page, $m[1]);
    exit;
}

if (preg_match('#^/page/(\d+)$#', $path, $m)) {
    $page = (int) $m[1];
    if ($page <= 1) {
        redirect(url_path());
    }
    render_home($page, null);
    exit;
}

if ($path === '/' || $path === '/index.php') {
    render_home(1, null);
    exit;
}

$slug = ltrim($path, '/');
if (str_contains($slug, '/') || $slug === '' || is_reserved_public_slug($slug)) {
    render_404();
    exit;
}

$st = db()->prepare(post_list_sql() . " WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
$st->execute([$slug]);
$post = $st->fetch();
if (!$post) {
    render_404();
    exit;
}

render_single($post);

function render_home(int $page, ?string $categorySlug): void
{
    $per = max(5, min(50, (int) setting('posts_per_page', '10')));
    $category = null;
    $where = " WHERE p.status = 'published'";
    $args = [];
    if ($categorySlug !== null && $categorySlug !== '') {
        $cst = db()->prepare('SELECT id, name, slug, color FROM categories WHERE slug = ?');
        $cst->execute([$categorySlug]);
        $category = $cst->fetch() ?: null;
        if (!$category) {
            render_404();
            return;
        }
        $where .= ' AND p.category_id = ?';
        $args[] = (int) $category['id'];
    }

    $countSql = 'SELECT COUNT(*) FROM posts p' . $where;
    $countSt = db()->prepare($countSql);
    $countSt->execute($args);
    $total = (int) $countSt->fetchColumn();

    $showFeatured = $category === null && $page === 1 && $total > 0;
    $feedTotal = $category === null ? max(0, $total - ($total > 0 ? 1 : 0)) : $total;
    $pages = $total === 0 ? 1 : max(1, (int) ceil($feedTotal / $per));
    if ($page > $pages) {
        render_404();
        return;
    }

    $featured = null;
    $posts = [];
    if ($showFeatured) {
        $featSt = db()->prepare(post_list_sql() . $where . ' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT 1');
        $featSt->execute($args);
        $featured = $featSt->fetch() ?: null;
        $listSql = post_list_sql() . $where . ' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT ' . $per . ' OFFSET 1';
        $listSt = db()->prepare($listSql);
        $listSt->execute($args);
        $posts = $listSt->fetchAll();
    } else {
        $offset = ($page - 1) * $per;
        if ($category === null && $total > 0) {
            $offset = 1 + ($page - 1) * $per;
        }
        $listSql = post_list_sql() . $where . ' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT ' . $per . ' OFFSET ' . $offset;
        $listSt = db()->prepare($listSql);
        $listSt->execute($args);
        $posts = $listSt->fetchAll();
    }

    $title = $category ? $category['name'] . ' — ' . setting('site_name') : setting('site_name');
    $description = $category ? $category['name'] : setting('site_tagline');
    $excludeId = null;
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/home.php';
    include APP_ROOT . '/templates/footer.php';
}

function render_single(array $post): void
{
    $title = ($post['seo_title'] ?? '') !== '' ? $post['seo_title'] : $post['title'];
    $description = ($post['seo_description'] ?? '') !== '' ? $post['seo_description'] : $post['excerpt'];
    $canonical = ($post['canonical_url'] ?? '') !== '' ? $post['canonical_url'] : url_path($post['slug']);
    $og = ($post['og_image'] ?? '') !== '' ? $post['og_image'] : $post['featured_image'];
    $index = (int) ($post['robots_index'] ?? 1) === 1 ? 'index,follow' : 'noindex,follow';
    $content = (string) $post['content'];
    $ad = setting('ad_in_article_html');
    if ($ad !== '') {
        $injected = false;
        $content = preg_replace_callback('/<\/p>/i', static function ($m) use (&$injected, $ad) {
            if ($injected) {
                return $m[0];
            }
            $injected = true;
            return $m[0] . $ad;
        }, $content, 1) ?? $content;
    }
    $excludeId = (int) $post['id'];
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/single.php';
    include APP_ROOT . '/templates/footer.php';
}

function render_404(): void
{
    http_response_code(404);
    $title = t('error.not_found');
    $description = '';
    $excludeId = null;
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/404.php';
    include APP_ROOT . '/templates/footer.php';
}
