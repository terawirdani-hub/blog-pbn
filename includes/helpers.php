<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

/**
 * Reads a request value as a string. Arrays (`?tab[]=x`) fall back to the
 * default instead of raising an "Array to string conversion" warning.
 */
function request_str(mixed $value, string $default = ''): string
{
    if (is_string($value)) {
        return $value;
    }
    if (is_int($value) || is_float($value) || is_bool($value)) {
        return (string) $value;
    }
    return $default;
}

function app_config(?string $key = null)
{
    $config = $GLOBALS['app_config'] ?? [];
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? null;
}

function redirect(string $url): void
{
    header('Location: ' . $url, true, 302);
    exit;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return is_string($ip) ? substr($ip, 0, 45) : '0.0.0.0';
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function slugify(string $text): string
{
    $text = trim($text);
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if (is_string($converted) && $converted !== '') {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'post';
    }
    return substr($text, 0, 80);
}

/** Normalizes an operator-entered site URL, or returns '' when unusable. */
function normalize_site_url(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value) !== 1) {
        $value = 'https://' . $value;
    }
    $value = rtrim($value, '/');
    return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
}

/** First value of a possibly comma-separated proxy header. */
function forwarded_header(string $name): string
{
    $raw = $_SERVER[$name] ?? '';
    if (!is_string($raw) || $raw === '') {
        return '';
    }
    return trim(explode(',', $raw)[0]);
}

function base_url(): string
{
    if (function_exists('setting')) {
        try {
            $configured = normalize_site_url(setting('site_url'));
        } catch (Throwable $e) {
            $configured = '';
        }
        if ($configured !== '') {
            return $configured;
        }
    }
    $forwardedProto = strtolower(forwarded_header('HTTP_X_FORWARDED_PROTO'));
    if ($forwardedProto === 'https' || $forwardedProto === 'http') {
        $scheme = $forwardedProto;
    } else {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');
        $scheme = $https ? 'https' : 'http';
    }
    $host = forwarded_header('HTTP_X_FORWARDED_HOST');
    if ($host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    }
    if (!is_string($host) || preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host) !== 1) {
        $host = 'localhost';
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = str_replace('\\', '/', dirname($script));
    if (str_contains($dir, '/admin')) {
        $dir = preg_replace('#/admin.*$#', '', $dir) ?? '';
    }
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }
    return rtrim($scheme . '://' . $host . $dir, '/');
}

function url_path(string $path = ''): string
{
    $base = base_url();
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function request_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script !== '/' && $script !== '.' && $script !== '' && str_starts_with($path, $script)) {
        $path = substr($path, strlen($script)) ?: '/';
    }
    if ($path === '') {
        return '/';
    }
    return '/' . ltrim($path, '/');
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
