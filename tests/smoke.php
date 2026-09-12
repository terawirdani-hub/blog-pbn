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
require $root . '/includes/theme.php';
require $root . '/includes/seo.php';

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

if (count(theme_presets()) !== 30) {
    fail('theme presets should be 30');
}
$presets = theme_presets();
if (!isset($presets['01'], $presets['12'], $presets['18'], $presets['24'], $presets['30'])) {
    fail('theme preset ids 01-30');
}
if ($presets['01']['layout'] !== 'magazine' || $presets['07']['layout'] !== 'tech' || $presets['13']['layout'] !== 'bento' || $presets['19']['layout'] !== 'newspaper' || $presets['25']['layout'] !== 'masonry') {
    fail('theme preset layout bands');
}
if ($presets['30']['palette'] !== 'mono' || $presets['06']['palette'] !== 'mono') {
    fail('theme preset palettes');
}
$applied = apply_template_id('22');
if (($applied['active_template'] ?? '') !== '22' || ($applied['homepage_layout'] ?? '') !== 'newspaper') {
    fail('apply_template_id 22');
}
foreach ($presets as $key => $preset) {
    $presetId = (string) ($preset['id'] ?? '');
    if ($presetId === '' || $presetId !== (string) $key || strlen($presetId) !== 2) {
        fail('preset id for key ' . (string) $key);
    }
    if (($preset['code'] ?? '') !== 't' . $presetId) {
        fail('preset code for ' . $presetId);
    }
}
if (active_template_id() === '') {
    fail('active_template_id should never be empty');
}
$grouped = theme_presets_by_layout();
if (count($grouped) !== 5) {
    fail('preset groups should be 5');
}
foreach ($grouped as $layoutKey => $group) {
    if (count($group) !== 6) {
        fail('layout ' . $layoutKey . ' should have 6 palettes');
    }
}
$fromCode = resolve_template_from_post(['template_preset' => 't28']);
if (($fromCode['active_template'] ?? '') !== '28' || ($fromCode['color_palette'] ?? '') !== 'violet') {
    fail('resolve template code t28');
}
$tok = seo_verification_token('<meta name="google-site-verification" content="AbC_12-3">');
if ($tok !== 'AbC_12-3') {
    fail('seo verification parse');
}
setting_set('home_meta_title', 'Home Title Smoke', $id1);
if (seo_home_document_title() !== 'Home Title Smoke') {
    fail('home meta title');
}

if (seo_absolute_url('example.com/post') !== 'https://example.com/post') {
    fail('scheme-less canonical should stay external');
}
if (normalize_site_url('domain.com/') !== 'https://domain.com') {
    fail('site url should gain a scheme and lose the trailing slash');
}
if (normalize_site_url('not a url') !== '') {
    fail('invalid site url should be dropped');
}
setting_set('site_url', 'https://pbn.example', $id1);
if (base_url() !== 'https://pbn.example' || url_path('rss.xml') !== 'https://pbn.example/rss.xml') {
    fail('configured site_url should drive absolute URLs');
}
setting_set('site_url', '', $id1);
if (seo_absolute_url('uploads/logo.png') === 'https://uploads/logo.png') {
    fail('upload path should stay local');
}

// The last-admin guard lives in SQL so concurrent deletes cannot both pass.
// Exercised on a scratch database to keep the real one untouched.
$mem = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$mem->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT NOT NULL)');
$mem->exec("INSERT INTO users (id, role) VALUES (1, 'admin'), (2, 'admin'), (3, 'editor')");
$guard = "DELETE FROM users WHERE id = ?
          AND (role <> 'admin' OR (SELECT COUNT(*) FROM users WHERE role = 'admin') > 1)";
$del = $mem->prepare($guard);
$del->execute([1]);
if ($del->rowCount() !== 1) {
    fail('deleting one of two admins should succeed');
}
$del->execute([2]);
if ($del->rowCount() !== 0) {
    fail('deleting the last admin must be refused');
}
$del->execute([3]);
if ($del->rowCount() !== 1) {
    fail('deleting an editor should succeed');
}
if ((int) $mem->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() !== 1) {
    fail('exactly one admin should remain');
}
$demote = $mem->prepare(
    "UPDATE users SET role = ? WHERE id = ?
     AND (? = 'admin' OR role <> 'admin' OR (SELECT COUNT(*) FROM users WHERE role = 'admin') > 1)"
);
$demote->execute(['editor', 2, 'editor']);
if ($demote->rowCount() !== 0) {
    fail('demoting the last admin must be refused');
}
$demote->execute(['admin', 2, 'admin']);
if ($demote->rowCount() !== 1) {
    fail('re-promoting an admin should succeed');
}

if ($failures === 0) {
    echo "OK\n";
    exit(0);
}
echo "$failures failure(s)\n";
exit(1);
