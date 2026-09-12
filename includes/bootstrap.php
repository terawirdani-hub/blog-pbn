<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
if (!is_array($config)) {
    http_response_code(500);
    echo 'Invalid configuration.';
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', !empty($config['debug']) ? '1' : '0');
ini_set('log_errors', '1');

$tz = $config['timezone'] ?? 'UTC';
if (is_string($tz) && $tz !== '') {
    date_default_timezone_set($tz);
}

define('APP_ROOT', dirname(__DIR__));
define('APP_DEBUG', !empty($config['debug']));

$GLOBALS['app_config'] = $config;

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/frontend.php';
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/seo.php';

start_secure_session();
$pdo = db();
migrate($pdo);
ensure_setting_defaults($pdo);
seed_admin_if_no_users();
i18n_boot();
