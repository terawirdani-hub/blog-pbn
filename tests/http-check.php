<?php
declare(strict_types=1);

$base = 'http://localhost:8080';
$cookie = sys_get_temp_dir() . '/turbo-pbn-http.cookie';
@unlink($cookie);
$fail = 0;
function hit(string $url, array $opts = []): array
{
    global $cookie, $fail;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if (!empty($opts['post'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['post']);
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr((string) $raw, 0, $hs);
    $body = substr((string) $raw, $hs);
    if (isset($opts['expect']) && $code !== $opts['expect']) {
        fwrite(STDERR, "FAIL $url expected {$opts['expect']} got $code\n");
        $GLOBALS['fail']++;
    }
    if (!empty($opts['contains']) && !str_contains($body . $headers, $opts['contains'])) {
        fwrite(STDERR, "FAIL $url missing {$opts['contains']}\n");
        $GLOBALS['fail']++;
    }
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
}

function csrf(string $html): string
{
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

hit($base . '/this-slug-does-not-exist', ['expect' => 404]);
// The built-in server ignores .htaccess, so router.php must deny these itself.
foreach ([
    '/database/data.sqlite',
    '/includes/db.php',
    '/config.php',
    '/router.php',
    '/admin/_init.php',
    '/templates/header.php',
    '/templates/partials/post-card.php',
] as $blocked) {
    hit($base . $blocked, ['expect' => 403]);
}
// Array-shaped query parameters must not raise "Array to string conversion".
hit($base . '/?q[]=x', ['expect' => 200]);
hit($base . '/?page=0', ['expect' => 200]);
hit($base . '/page/1', ['expect' => 302]);
hit($base . '/sitemap.xml', ['expect' => 200, 'contains' => 'urlset']);
hit($base . '/rss.xml', ['expect' => 200, 'contains' => '<rss']);
hit($base . '/', ['expect' => 200, 'contains' => 'max-image-preview:large']);
hit($base . '/', ['expect' => 200, 'contains' => 'application/ld+json']);
hit($base . '/robots.txt', ['expect' => 200, 'contains' => 'User-agent']);

$login = hit($base . '/admin/index.php', ['expect' => 200, 'contains' => 'csrf_token']);
$token = csrf($login['body']);
$auth = hit($base . '/admin/index.php', [
    'post' => [
        'csrf_token' => $token,
        'username' => 'admin',
        'password' => 'password123',
    ],
]);
if ($auth['code'] !== 302) {
    fwrite(STDERR, "login expected 302 got {$auth['code']}\n");
    $fail++;
}
foreach ([
    '/admin/index.php',
    '/admin/posts.php',
    '/admin/post-edit.php',
    '/admin/categories.php',
    '/admin/blogroll.php',
    '/admin/users.php',
    '/admin/profile.php',
    '/admin/audit.php',
    '/admin/backup.php',
] as $adminPage) {
    hit($base . $adminPage, ['expect' => 200]);
}
hit($base . '/', ['expect' => 200]);
foreach (['general', 'appearance', 'author', 'ads', 'tracking', 'seo'] as $settingsTab) {
    hit($base . '/admin/settings.php?tab=' . $settingsTab, ['expect' => 200]);
}
// Indonesian alias must land on the appearance tab, not fall back to general.
$alias = hit($base . '/admin/settings.php?tab=tampilan', ['expect' => 200, 'contains' => 'name="save_appearance"']);
if (substr_count($alias['body'], '<option value="t') !== 30) {
    fwrite(STDERR, "FAIL appearance tab should render 30 presets\n");
    $fail++;
}

$before = preg_match('/value="(t\d\d)" selected/', $alias['body'], $sel) ? $sel[1] : 't01';
$save = hit($base . '/admin/settings.php', [
    'post' => [
        'csrf_token' => csrf($alias['body']),
        'tab' => 'appearance',
        'template_preset' => 't28',
        'public_theme' => 'light',
        'save_appearance' => '1',
    ],
]);
if ($save['code'] !== 302 || !str_contains($save['headers'], 'status=success')) {
    fwrite(STDERR, "FAIL appearance save should redirect with status=success\n");
    $fail++;
}
$saved = hit($base . '/admin/settings.php?tab=appearance&status=success', ['expect' => 200, 'contains' => 'flash-success']);
if (!preg_match('/value="t28" selected/', $saved['body'])) {
    fwrite(STDERR, "FAIL saved preset should stay selected\n");
    $fail++;
}
hit($base . '/', ['expect' => 200, 'contains' => 'data-layout="masonry"']);
hit($base . '/', ['expect' => 200, 'contains' => 'data-palette="violet"']);
hit($base . '/admin/settings.php', [
    'post' => [
        'csrf_token' => csrf($saved['body']),
        'tab' => 'appearance',
        'template_preset' => $before,
        'public_theme' => 'light',
        'save_appearance' => '1',
    ],
]);

if ($fail === 0) {
    echo "HTTP OK\n";
    exit(0);
}
echo "HTTP $fail failure(s)\n";
exit(1);
