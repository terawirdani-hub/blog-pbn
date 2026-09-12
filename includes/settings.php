<?php
declare(strict_types=1);

function setting_defaults(): array
{
    return [
        'site_name' => ['value' => 'Turbo PBN', 'secret' => 0],
        'site_tagline' => ['value' => '', 'secret' => 0],
        'site_locale' => ['value' => 'id', 'secret' => 0],
        'public_theme' => ['value' => 'light', 'secret' => 0],
        'template_style' => ['value' => 'magazine', 'secret' => 0],
        'homepage_layout' => ['value' => 'magazine', 'secret' => 0],
        'color_palette' => ['value' => 'slate', 'secret' => 0],
        'active_template' => ['value' => '', 'secret' => 0],
        'primary_color' => ['value' => '#2563eb', 'secret' => 0],
        'accent_color' => ['value' => '#0f172a', 'secret' => 0],
        'logo_path' => ['value' => '', 'secret' => 0],
        'favicon_path' => ['value' => '', 'secret' => 0],
        'homepage_intro' => ['value' => '', 'secret' => 0],
        'posts_per_page' => ['value' => '10', 'secret' => 0],
        'footer_text' => ['value' => '', 'secret' => 0],
        'ad_header_html' => ['value' => '', 'secret' => 0],
        'ad_sidebar_html' => ['value' => '', 'secret' => 0],
        'ad_in_article_html' => ['value' => '', 'secret' => 0],
        'ad_footer_html' => ['value' => '', 'secret' => 0],
        'tracking_head_html' => ['value' => '', 'secret' => 1],
        'tracking_body_html' => ['value' => '', 'secret' => 1],
        'robots_txt' => ['value' => "User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: {base}/sitemap.xml\n", 'secret' => 0],
        'home_meta_title' => ['value' => '', 'secret' => 0],
        'home_meta_description' => ['value' => '', 'secret' => 0],
        'home_meta_keywords' => ['value' => '', 'secret' => 0],
        'og_default_image_path' => ['value' => '', 'secret' => 0],
        'google_site_verification' => ['value' => '', 'secret' => 0],
        'bing_site_verification' => ['value' => '', 'secret' => 0],
        'comments_enabled' => ['value' => '0', 'secret' => 0],
        'author_name' => ['value' => '', 'secret' => 0],
        'author_bio' => ['value' => '', 'secret' => 0],
        'author_avatar_path' => ['value' => '', 'secret' => 0],
        'social_twitter' => ['value' => '', 'secret' => 0],
        'social_github' => ['value' => '', 'secret' => 0],
        'social_linkedin' => ['value' => '', 'secret' => 0],
        'social_instagram' => ['value' => '', 'secret' => 0],
        'social_facebook' => ['value' => '', 'secret' => 0],
    ];
}

function ensure_setting_defaults(PDO $pdo): void
{
    $st = $pdo->prepare(
        'INSERT OR IGNORE INTO settings (key, value, is_secret, updated_at) VALUES (?, ?, ?, ?)'
    );
    $now = now_utc();
    foreach (setting_defaults() as $key => $meta) {
        $st->execute([$key, $meta['value'], $meta['secret'], $now]);
    }
}

function settings_all(): array
{
    if (!isset($GLOBALS['_settings_map']) || !is_array($GLOBALS['_settings_map'])) {
        $map = [];
        foreach (db()->query('SELECT key, value FROM settings') as $row) {
            $map[$row['key']] = $row['value'];
        }
        $GLOBALS['_settings_map'] = $map;
    }
    return $GLOBALS['_settings_map'];
}

function settings_clear_cache(): void
{
    unset($GLOBALS['_settings_map']);
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    if (isset($all[$key]) && is_string($all[$key])) {
        return $all[$key];
    }
    $defaults = setting_defaults();
    if (isset($defaults[$key])) {
        return (string) $defaults[$key]['value'];
    }
    return $default;
}

function setting_set(string $key, string $value, ?int $userId = null): void
{
    $defaults = setting_defaults();
    $secret = isset($defaults[$key]) ? (int) $defaults[$key]['secret'] : 0;
    $st = db()->prepare(
        'INSERT INTO settings (key, value, is_secret, updated_at, updated_by)
         VALUES (?, ?, ?, ?, ?)
         ON CONFLICT(key) DO UPDATE SET
            value = excluded.value,
            is_secret = excluded.is_secret,
            updated_at = excluded.updated_at,
            updated_by = excluded.updated_by'
    );
    $st->execute([$key, $value, $secret, now_utc(), $userId]);
    settings_clear_cache();
}
