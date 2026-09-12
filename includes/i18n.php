<?php
declare(strict_types=1);

function i18n_boot(): void
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $isAdmin = str_contains($script, '/admin/');
    $locale = 'id';
    if ($isAdmin) {
        $user = current_user();
        if ($user && in_array($user['locale'], ['id', 'en'], true)) {
            $locale = $user['locale'];
        } elseif (!empty($_COOKIE['admin_locale']) && in_array($_COOKIE['admin_locale'], ['id', 'en'], true)) {
            $locale = $_COOKIE['admin_locale'];
        } elseif (in_array(setting('site_locale', 'id'), ['id', 'en'], true)) {
            $locale = setting('site_locale', 'id');
        }
    } elseif (in_array(setting('site_locale', 'id'), ['id', 'en'], true)) {
        $locale = setting('site_locale', 'id');
    }
    $GLOBALS['i18n_locale'] = $locale;
    $GLOBALS['i18n_strings'] = load_lang($locale);
}

function load_lang(string $locale): array
{
    $file = APP_ROOT . '/lang/' . $locale . '.php';
    if (!is_file($file)) {
        $file = APP_ROOT . '/lang/id.php';
    }
    $data = require $file;
    return is_array($data) ? $data : [];
}

function locale(): string
{
    return $GLOBALS['i18n_locale'] ?? 'id';
}

function t(string $key, array $replace = []): string
{
    $strings = $GLOBALS['i18n_strings'] ?? [];
    $text = $strings[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace('{' . $k . '}', (string) $v, $text);
    }
    return $text;
}

function tooltip(string $key): string
{
    $title = t('tooltip.' . $key . '.title');
    $body = t('tooltip.' . $key . '.body');
    $label = t('ui.info');
    return '<button type="button" class="tip" aria-label="' . h($label) . ': ' . h($title) . '">'
        . '<span class="tip-mark">i</span>'
        . '<span class="tip-box" role="tooltip"><strong>' . h($title) . '</strong>' . h($body) . '</span>'
        . '</button>';
}

function field_label(string $labelKey, string $tooltipKey): string
{
    return '<span class="label-row"><span>' . h(t($labelKey)) . '</span>' . tooltip($tooltipKey) . '</span>';
}

function persist_locale_cookie(string $locale): void
{
    if (!in_array($locale, ['id', 'en'], true)) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('admin_locale', $locale, [
        'expires' => time() + 86400 * 365,
        'path' => '/',
        'secure' => $https,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}
