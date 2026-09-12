<?php
declare(strict_types=1);

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

function media_url(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return url_path(ltrim($path, '/'));
}

function reading_minutes(string $html): int
{
    $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($text === '') {
        return 1;
    }
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $count = is_array($words) ? count($words) : 0;
    return max(1, (int) ceil($count / 200));
}

function format_post_date(?string $utc): string
{
    if ($utc === null || $utc === '') {
        return '';
    }
    $ts = strtotime($utc . ' UTC');
    if ($ts === false) {
        return substr($utc, 0, 10);
    }
    return date('M j, Y', $ts);
}

function author_display_name(?string $username = null): string
{
    $set = trim(setting('author_name'));
    if ($set !== '') {
        return $set;
    }
    if (is_string($username) && $username !== '') {
        return $username;
    }
    return setting('site_name');
}

function author_initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($parts) || $parts === []) {
        return 'A';
    }
    $first = function_exists('mb_substr') ? mb_substr($parts[0], 0, 1) : substr($parts[0], 0, 1);
    $second = '';
    if (isset($parts[1])) {
        $second = function_exists('mb_substr') ? mb_substr($parts[1], 0, 1) : substr($parts[1], 0, 1);
    }
    $out = strtoupper($first . $second);
    return $out !== '' ? $out : 'A';
}

function theme_toggle_url(): string
{
    $next = public_theme() === 'dark' ? 'light' : 'dark';
    $path = request_path();
    if ($path === '/index.php') {
        $path = '/';
    }
    $qs = $_GET;
    $qs['theme'] = $next;
    return $path . '?' . http_build_query($qs);
}

function post_list_sql(): string
{
    return 'SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.featured_image, p.published_at, p.author_id,
            p.category_id, p.seo_title, p.seo_description, p.canonical_url, p.og_image, p.robots_index,
            p.updated_at, p.focus_keyword, p.schema_type,
            c.name AS category_name, c.slug AS category_slug, c.color AS category_color,
            u.username AS author_username
         FROM posts p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN users u ON u.id = p.author_id';
}

function categories_with_counts(): array
{
    return db()->query(
        "SELECT c.id, c.name, c.slug, c.color, COUNT(p.id) AS post_count
         FROM categories c
         LEFT JOIN posts p ON p.category_id = c.id AND p.status = 'published'
         GROUP BY c.id, c.name, c.slug, c.color
         ORDER BY c.name ASC"
    )->fetchAll();
}

function trending_posts(int $limit = 5, ?int $excludeId = null): array
{
    $limit = max(1, min(10, $limit));
    $sql = post_list_sql() . " WHERE p.status = 'published'";
    $args = [];
    if ($excludeId !== null && $excludeId > 0) {
        $sql .= ' AND p.id != ?';
        $args[] = $excludeId;
    }
    $sql .= ' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT ' . $limit;
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function social_links(): array
{
    $map = [
        'twitter' => setting('social_twitter'),
        'github' => setting('social_github'),
        'linkedin' => setting('social_linkedin'),
        'instagram' => setting('social_instagram'),
        'facebook' => setting('social_facebook'),
    ];
    $out = [];
    foreach ($map as $network => $url) {
        $url = trim($url);
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $out[$network] = $url;
        }
    }
    return $out;
}

function unique_category_slug(PDO $pdo, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $candidate = $slug;
    for ($i = 1; $i <= 50; $i++) {
        if ($ignoreId === null) {
            $st = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
            $st->execute([$candidate]);
        } else {
            $st = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
            $st->execute([$candidate, $ignoreId]);
        }
        if (!$st->fetch()) {
            return $candidate;
        }
        $candidate = $slug . '-' . ($i + 1);
    }
    return $slug . '-' . bin2hex(random_bytes(3));
}

function blogroll_items(): array
{
    return db()->query('SELECT title, url, rel, target FROM blogroll WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function blogroll_rel_kind(string $rel): string
{
    return str_contains(strtolower($rel), 'nofollow') ? 'nofollow' : 'dofollow';
}

function editorial_cover(string $aspectClass, string $title, ?string $categoryName = null, ?string $categoryColor = null): void
{
    $colors = function_exists('public_palette_colors') ? public_palette_colors() : ['primary' => setting('primary_color', '#2563eb'), 'accent' => setting('accent_color', '#0f172a')];
    $c1 = $colors['primary'];
    $c2 = $colors['accent'];
    $catColor = $categoryColor ?: $c1;
    ?>
    <div class="<?= h($aspectClass) ?> relative overflow-hidden">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($c1) ?> 0%, #0f172a 48%, <?= h($c2) ?> 100%);"></div>
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, #fff 1px, transparent 1px), radial-gradient(circle at 80% 0%, #fff 1.5px, transparent 1.5px), radial-gradient(circle at 50% 80%, #fff 1px, transparent 1px); background-size: 28px 28px, 42px 42px, 36px 36px;"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
        <div class="relative flex h-full flex-col justify-end p-5 sm:p-7">
            <?php if ($categoryName): ?>
                <span class="mb-2 inline-flex w-fit rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm" style="background: <?= h($catColor) ?>"><?= h($categoryName) ?></span>
            <?php endif; ?>
            <?php if ($title !== ''): ?>
                <p class="line-clamp-2 text-lg font-extrabold leading-snug text-white drop-shadow sm:text-xl"><?= h($title) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
