<?php
declare(strict_types=1);

/**
 * Router for the PHP built-in server (`php -S localhost:8080 router.php`).
 * The built-in server ignores .htaccess, so mirror the production deny rules here.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . ltrim(str_replace('\\', '/', $uri), '/');

$deniedDirs = ['database', 'includes', 'lang', 'templates', 'tests', 'docs', 'scripts'];
foreach ($deniedDirs as $dir) {
    if (str_starts_with($uri, '/' . $dir . '/') || $uri === '/' . $dir) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "403 Forbidden\n";
        return true;
    }
}

$name = basename($uri);
$isDeniedFile = in_array($name, ['config.php', 'router.php', '.htaccess', 'AGENTS.md', 'MASTER_PLAN.md'], true)
    || preg_match('/\.(sqlite|sqlite-wal|sqlite-shm|md|ps1|log)$/i', $name) === 1
    || (str_starts_with($uri, '/uploads/') && preg_match('/\.php$/i', $name) === 1)
    || (str_starts_with($uri, '/admin/') && str_starts_with($name, '_'));
if ($isDeniedFile) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 Forbidden\n";
    return true;
}

$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    return false;
}
require __DIR__ . '/index.php';
