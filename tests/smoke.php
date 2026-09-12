<?php
declare(strict_types=1);

/**
 * CLI smoke checks: syntax, i18n key parity, schema + slug uniqueness.
 * Usage: php tests/smoke.php
 */

$root = dirname(__DIR__);
$failures = 0;

function fail(string $msg): void
{
    global $failures;
    $failures++;
    fwrite(STDERR, "FAIL: $msg\n");
}

$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($phpFiles as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $path = $file->getPathname();
        if (str_contains($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR)) {
            continue;
        }
        $out = [];
        $code = 0;
        exec('php -l ' . escapeshellarg($path), $out, $code);
        if ($code !== 0) {
            fail('syntax ' . $path . ' ' . implode(' ', $out));
        }
    }
}

$id = require $root . '/lang/id.php';
$en = require $root . '/lang/en.php';
$idKeys = array_keys($id);
$enKeys = array_keys($en);
sort($idKeys);
sort($enKeys);
$missingEn = array_diff($idKeys, $enKeys);
$missingId = array_diff($enKeys, $idKeys);
if ($missingEn) {
    fail('en missing keys: ' . implode(',', $missingEn));
}
if ($missingId) {
    fail('id missing keys: ' . implode(',', $missingId));
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'turbo-pbn-smoke-' . bin2hex(random_bytes(4));
mkdir($tmp);
$GLOBALS['app_config'] = [
    'timezone' => 'UTC',
    'database_path' => $tmp . DIRECTORY_SEPARATOR . 'data.sqlite',
    'uploads_path' => $tmp,
    'uploads_url' => 'uploads',
    'debug' => true,
];
define('APP_ROOT', $root);
define('APP_DEBUG', true);
date_default_timezone_set('UTC');
require $root . '/includes/helpers.php';
require $root . '/includes/db.php';
require $root . '/includes/schema.php';
require $root . '/includes/csrf.php';
require $root . '/includes/i18n.php';
require $root . '/includes/settings.php';
require $root . '/includes/audit.php';
require $root . '/includes/auth.php';
require $root . '/includes/upload.php';
require $root . '/includes/html.php';
require $root . '/includes/frontend.php';

session_start();
$pdo = db();
migrate($pdo);
ensure_setting_defaults($pdo);
$GLOBALS['i18n_locale'] = 'en';
$GLOBALS['i18n_strings'] = load_lang('en');

$id1 = create_user('admin_test', 'password1234', 'admin', 'en');
$id2 = create_user('editor_test', 'password1234', 'editor', 'id');
if ($id1 < 1 || $id2 < 1) {
    fail('create_user');
}
try {
    create_user('admin_test', 'password1234', 'admin', 'en');
    fail('duplicate username should fail');
} catch (InvalidArgumentException $e) {
    // expected
}

$slug = unique_post_slug($pdo, 'Hello World');
$pid = insert_post_with_slug($pdo, [
    'title' => 'Hello World',
    'slug' => $slug,
    'excerpt' => '',
    'content' => '<p>Hi</p><script>alert(1)</script>',
    'featured_image' => '',
    'status' => 'published',
    'seo_title' => '',
    'seo_description' => '',
    'seo_keywords' => '',
    'canonical_url' => '',
    'og_image' => '',
    'robots_index' => 1,
    'published_at' => now_utc(),
    'author_id' => $id1,
    'created_at' => now_utc(),
    'updated_at' => now_utc(),
]);
$slug2 = unique_post_slug($pdo, 'Hello World');
if ($slug2 === $slug) {
    fail('slug uniqueness');
}
$clean = sanitize_post_html('<p>Hi</p><script>alert(1)</script>');
if (str_contains($clean, 'script')) {
    fail('sanitize script');
}

$pub = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
if ($pub !== 1) {
    fail('published count');
}

setting_set('site_name', 'Smoke Site', $id1);
if (setting('site_name') !== 'Smoke Site') {
    fail('settings roundtrip');
}

if ($failures === 0) {
    echo "OK\n";
    exit(0);
}
echo "$failures failure(s)\n";
exit(1);
