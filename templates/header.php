<?php
declare(strict_types=1);

$theme = public_theme();
$style = setting('template_style', 'classic') === 'magazine' ? 'magazine' : 'classic';
$primary = setting('primary_color', '#1f6feb');
$accent = setting('accent_color', '#238636');
$siteName = setting('site_name');
$tagline = setting('site_tagline');
$logo = setting('logo_path');
$favicon = setting('favicon_path');
$toggleTo = $theme === 'dark' ? 'light' : 'dark';
$toggleUrl = request_path() . '?theme=' . $toggleTo;
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= h(locale()) ?>" data-theme="<?= h($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(($title ?? $siteName) . ' — ' . $siteName) ?></title>
    <?php if (!empty($description)): ?>
        <meta name="description" content="<?= h($description) ?>">
    <?php endif; ?>
    <?php if (!empty($index)): ?>
        <meta name="robots" content="<?= h($index) ?>">
    <?php endif; ?>
    <?php if (!empty($canonical)): ?>
        <link rel="canonical" href="<?= h($canonical) ?>">
    <?php endif; ?>
    <?php if (!empty($og)): ?>
        <meta property="og:image" content="<?= h(str_starts_with($og, 'http') ? $og : url_path($og)) ?>">
    <?php endif; ?>
    <?php if ($favicon !== ''): ?>
        <link rel="icon" href="<?= h(url_path($favicon)) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= h(url_path('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= h(url_path('assets/css/public.css')) ?>">
    <style>:root { --primary: <?= h($primary) ?>; --accent: <?= h($accent) ?>; }</style>
    <?= setting('tracking_head_html') ?>
</head>
<body class="public style-<?= h($style) ?>">
<?= setting('tracking_body_html') ?>
<header class="site-header">
    <div class="wrap head-row">
        <a class="logo" href="<?= h(url_path()) ?>">
            <?php if ($logo !== ''): ?>
                <img src="<?= h(url_path($logo)) ?>" alt="<?= h($siteName) ?>">
            <?php else: ?>
                <?= h($siteName) ?>
            <?php endif; ?>
        </a>
        <?php if ($tagline !== ''): ?><span class="tagline"><?= h($tagline) ?></span><?php endif; ?>
        <nav class="head-nav">
            <a href="<?= h(url_path()) ?>"><?= h(t('public.home')) ?></a>
            <a href="<?= h($toggleUrl) ?>"><?= h(t('public.theme_toggle')) ?></a>
        </nav>
    </div>
</header>
<?php if (setting('ad_header_html') !== ''): ?>
    <div class="wrap ad-slot"><?= setting('ad_header_html') ?></div>
<?php endif; ?>
<div class="wrap layout">
    <div class="content">
