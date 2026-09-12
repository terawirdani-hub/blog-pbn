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
hit($base . '/admin/index.php', ['expect' => 200]);
hit($base . '/admin/posts.php', ['expect' => 200]);
hit($base . '/', ['expect' => 200]);
hit($base . '/admin/settings.php', ['expect' => 200]);

if ($fail === 0) {
    echo "HTTP OK\n";
    exit(0);
}
echo "HTTP $fail failure(s)\n";
exit(1);
