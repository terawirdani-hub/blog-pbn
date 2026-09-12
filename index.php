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
    seo_output_sitemap();
    exit;
}

if ($path === '/rss.xml' || $path === '/feed') {
    seo_output_rss();
    exit;
}

if (preg_match('#^/category/([a-z0-9\-]+)(?:/page/(\d+))?$#', $path, $m)) {
    $page = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 1;
    if ($page <= 1 && isset($m[2]) && $m[2] !== '') {
        redirect(url_path('category/' . $m[1]));
    }
    render_home($page, $m[1], '');
    exit;
}

if (preg_match('#^/page/(\d+)$#', $path, $m)) {
    $page = (int) $m[1];
    if ($page <= 1) {
        $q = trim(request_str($_GET['q'] ?? ''));
        redirect($q !== '' ? url_path() . '?q=' . rawurlencode($q) : url_path());
    }
    render_home($page, null, trim(request_str($_GET['q'] ?? '')));
    exit;
}

if ($path === '/' || $path === '/index.php') {
    render_home(1, null, trim(request_str($_GET['q'] ?? '')));
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

function render_home(int $page, ?string $categorySlug, string $query = ''): void
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
        $query = '';
    }
    $query = trim($query);
    $needle = str_replace(['%', '_'], '', $query);
    if ($needle === '') {
        // A query made only of LIKE wildcards would otherwise match everything.
        $query = '';
    }
    if ($query !== '') {
        $like = '%' . $needle . '%';
        $where .= ' AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.seo_title LIKE ? OR p.focus_keyword LIKE ?)';
        array_push($args, $like, $like, $like, $like);
    }

    $countSql = 'SELECT COUNT(*) FROM posts p' . $where;
    $countSt = db()->prepare($countSql);
    $countSt->execute($args);
    $total = (int) $countSt->fetchColumn();

    $showFeatured = $category === null && $page === 1 && $total > 0 && $query === '';
    $feedTotal = ($category === null && $query === '') ? max(0, $total - ($total > 0 ? 1 : 0)) : $total;
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
        if ($category === null && $query === '' && $total > 0) {
            $offset = 1 + ($page - 1) * $per;
        }
        $listSql = post_list_sql() . $where . ' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT ' . $per . ' OFFSET ' . $offset;
        $listSt = db()->prepare($listSql);
        $listSt->execute($args);
        $posts = $listSt->fetchAll();
    }

    $seo = seo_context_home(is_array($category) ? $category : null, $page, $query);
    $title = $seo['title'];
    $description = $seo['description'];
    $excludeId = null;
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/home.php';
    include APP_ROOT . '/templates/footer.php';
}

function render_single(array $post): void
{
    $seo = seo_context_post($post);
    $title = $seo['title'];
    $description = $seo['description'];
    $canonical = $seo['canonical'];
    $og = $seo['og_image'];
    $index = $seo['robots'];
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
    $seo = seo_context_404();
    $title = $seo['title'];
    $description = $seo['description'];
    $excludeId = null;
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/404.php';
    include APP_ROOT . '/templates/footer.php';
}
