<?php
declare(strict_types=1);

$theme = public_theme();
$colors = public_palette_colors();
$primary = $colors['primary'];
$accent = $colors['accent'];
$siteName = setting('site_name');
$tagline = setting('site_tagline');
$logo = setting('logo_path');
$favicon = setting('favicon_path');
$toggleUrl = theme_toggle_url();
$navCategories = categories_with_counts();
$isDark = $theme === 'dark';
$layout = public_layout();
$palette = public_palette();
$searchQ = trim((string) ($_GET['q'] ?? ''));
if (!isset($seo) || !is_array($seo)) {
    $seo = [
        'title' => $title ?? $siteName,
        'description' => $description ?? '',
        'canonical' => $canonical ?? url_path(),
        'robots' => $index ?? seo_robots(true),
        'og_type' => 'website',
        'og_image' => isset($og) ? seo_absolute_url((string) $og) : seo_default_image(),
        'jsonld' => seo_json(seo_website_graph()),
    ];
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= h(locale()) ?>" class="<?= $isDark ? 'dark' : '' ?>" data-theme="<?= h($theme) ?>" data-layout="<?= h($layout) ?>" data-palette="<?= h($palette) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php seo_print_head($seo); ?>
    <?php if ($favicon !== ''): ?>
        <link rel="icon" type="<?= h(favicon_type($favicon)) ?>" href="<?= h(media_url($favicon)) ?>">
        <link rel="apple-touch-icon" href="<?= h(media_url($favicon)) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Source+Serif+4:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: {
              sans: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
              serif: ['Source Serif 4', 'Georgia', 'serif'],
            },
            maxWidth: { '7xl': '80rem' }
          }
        }
      }
    </script>
    <style>
      :root { --brand: <?= h($primary) ?>; --accent: <?= h($accent) ?>; --soft: <?= h($colors['soft']) ?>; }
      html { scroll-behavior: smooth; }
      .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
      [data-layout="newspaper"] .font-serif { font-family: 'Source Serif 4', Georgia, serif; }
    </style>
    <?= setting('tracking_head_html') ?>
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased dark:bg-zinc-950 dark:text-zinc-200">
<?= setting('tracking_body_html') ?>
<header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/75 backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-950/75">
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="<?= h(url_path()) ?>" class="group flex min-w-0 items-center gap-3">
            <?php if ($logo !== ''): ?>
                <img src="<?= h(media_url($logo)) ?>" alt="<?= h($siteName) ?>" class="h-9 w-auto max-w-[160px] object-contain">
            <?php else: ?>
                <span class="truncate text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-xl"><?= h($siteName) ?></span>
            <?php endif; ?>
        </a>
        <nav class="hidden min-w-0 flex-1 items-center gap-1 overflow-x-auto md:flex" aria-label="<?= h(t('public.categories')) ?>">
            <a href="<?= h(url_path()) ?>" class="shrink-0 rounded-full px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"><?= h(t('public.home')) ?></a>
            <?php foreach ($navCategories as $cat): ?>
                <a href="<?= h(url_path('category/' . $cat['slug'])) ?>" class="shrink-0 rounded-full px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"><?= h($cat['name']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="ml-auto flex items-center gap-2">
            <form action="<?= h(url_path()) ?>" method="get" role="search" class="hidden sm:block">
                <label class="sr-only" for="q"><?= h(t('ui.search')) ?></label>
                <input id="q" type="search" name="q" value="<?= h($searchQ) ?>" placeholder="<?= h(t('ui.search')) ?>"
                       class="w-36 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-800 outline-none focus:w-48 focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 lg:w-44">
            </form>
            <a href="<?= h($toggleUrl) ?>"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
               title="<?= h(t('public.theme_toggle')) ?>"
               aria-label="<?= h(t('public.theme_toggle')) ?>">
                <?php if ($isDark): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M4.219 4.219l1.591 1.591M18.19 18.19l1.59 1.591M3 12h2.25M18.75 12H21M4.219 19.781l1.591-1.591M18.19 5.81l1.59-1.591M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <?php endif; ?>
            </a>
        </div>
    </div>
    <?php if ($navCategories): ?>
    <nav class="flex gap-2 overflow-x-auto border-t border-zinc-100 px-4 py-2 md:hidden dark:border-zinc-800" aria-label="<?= h(t('public.categories')) ?>">
        <a href="<?= h(url_path()) ?>" class="shrink-0 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><?= h(t('public.home')) ?></a>
        <?php foreach ($navCategories as $cat): ?>
            <a href="<?= h(url_path('category/' . $cat['slug'])) ?>" class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold text-zinc-600 dark:text-zinc-300" style="background: <?= h($cat['color']) ?>22; color: <?= h($cat['color']) ?>"><?= h($cat['name']) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
</header>
<?php if (setting('ad_header_html') !== ''): ?>
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8"><?= setting('ad_header_html') ?></div>
<?php endif; ?>
<div class="<?= h(public_shell_class()) ?>">
    <div class="<?= h(public_main_class()) ?>">
