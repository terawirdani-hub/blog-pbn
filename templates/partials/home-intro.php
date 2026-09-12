<?php
declare(strict_types=1);

$intro = sanitize_post_html(setting('homepage_intro'));
?>
<?php if (!empty($query)): ?>
    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"><?= h(t('public.search')) ?></p>
    <h1 class="mb-8 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white"><?= h((string) $query) ?></h1>
<?php elseif (!empty($category) && is_array($category)): ?>
    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"><?= h(t('public.categories')) ?></p>
    <h1 class="mb-8 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-4xl"><?= h($category['name']) ?></h1>
<?php elseif ($intro !== '' && (int) $page === 1): ?>
    <div class="prose prose-zinc mb-10 max-w-none dark:prose-invert"><?= $intro ?></div>
<?php endif; ?>

<?php if (empty($posts) && empty($featured)): ?>
    <p class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400"><?= h(t('ui.empty')) ?></p>
<?php endif; ?>
