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

if (preg_match('#^/page/(\d+)$#', $path, $m)) {
    $page = (int) $m[1];
    if ($page <= 1) {
        redirect(url_path());
    }
    render_home($page);
    exit;
}

if ($path === '/' || $path === '/index.php') {
    render_home(1);
    exit;
}

$slug = ltrim($path, '/');
if (str_contains($slug, '/') || $slug === '') {
    render_404();
    exit;
}

$st = db()->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published' LIMIT 1");
$st->execute([$slug]);
$post = $st->fetch();
if (!$post) {
    render_404();
    exit;
}

render_single($post);

function public_theme(): string
{
    $allowed = ['light', 'dark'];
    if (!empty($_GET['theme']) && in_array($_GET['theme'], $allowed, true)) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('public_theme', $_GET['theme'], [
            'expires' => time() + 86400 * 365,
            'path' => '/',
            'secure' => $https,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        return $_GET['theme'];
    }
    if (!empty($_COOKIE['public_theme']) && in_array($_COOKIE['public_theme'], $allowed, true)) {
        return $_COOKIE['public_theme'];
    }
    $def = setting('public_theme', 'light');
    return in_array($def, $allowed, true) ? $def : 'light';
}

function blogroll_items(): array
{
    return db()->query('SELECT title, url, rel, target FROM blogroll WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function render_home(int $page): void
{
    $per = max(5, min(50, (int) setting('posts_per_page', '10')));
    $total = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $pages = max(1, (int) ceil($total / $per));
    if ($page > $pages) {
        render_404();
        return;
    }
    $offset = ($page - 1) * $per;
    $st = db()->query(
        "SELECT title, slug, excerpt, featured_image, published_at FROM posts
         WHERE status = 'published'
         ORDER BY datetime(published_at) DESC, id DESC
         LIMIT " . $per . ' OFFSET ' . $offset
    );
    $posts = $st->fetchAll();
    $title = setting('site_name');
    $description = setting('site_tagline');
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/home.php';
    include APP_ROOT . '/templates/footer.php';
}

function render_single(array $post): void
{
    $title = $post['seo_title'] !== '' ? $post['seo_title'] : $post['title'];
    $description = $post['seo_description'] !== '' ? $post['seo_description'] : $post['excerpt'];
    $canonical = $post['canonical_url'] !== '' ? $post['canonical_url'] : url_path($post['slug']);
    $og = $post['og_image'] !== '' ? $post['og_image'] : $post['featured_image'];
    $index = (int) $post['robots_index'] === 1 ? 'index,follow' : 'noindex,follow';
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
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/single.php';
    include APP_ROOT . '/templates/footer.php';
}

function render_404(): void
{
    http_response_code(404);
    $title = t('error.not_found');
    $description = '';
    include APP_ROOT . '/templates/header.php';
    include APP_ROOT . '/templates/404.php';
    include APP_ROOT . '/templates/footer.php';
}
