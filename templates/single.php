<?php
declare(strict_types=1);

$img = $post['featured_image'] !== '' ? media_url($post['featured_image']) : '';
$author = author_display_name($post['author_username'] ?? null);
$mins = reading_minutes((string) $post['content']);
$avatar = setting('author_avatar_path');
$brand = public_palette_colors()['primary'];
?>
<nav class="mb-6 text-sm text-zinc-500" aria-label="breadcrumb">
    <a class="hover:underline" href="<?= h(url_path()) ?>"><?= h(setting('site_name')) ?></a>
    <?php if (!empty($post['category_name'])): ?>
        <span aria-hidden="true"> / </span>
        <a class="hover:underline" href="<?= h(url_path('category/' . $post['category_slug'])) ?>"><?= h($post['category_name']) ?></a>
    <?php endif; ?>
    <span aria-hidden="true"> / </span>
    <span class="text-zinc-700 dark:text-zinc-300"><?= h($post['title']) ?></span>
</nav>
<article>
    <?php if (!empty($post['category_name'])): ?>
        <a href="<?= h(url_path('category/' . $post['category_slug'])) ?>" class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" style="background: <?= h((string) $post['category_color']) ?>22; color: <?= h((string) $post['category_color']) ?>"><?= h($post['category_name']) ?></a>
    <?php endif; ?>
    <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl sm:leading-tight"><?= h($post['title']) ?></h1>
    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
        <?php if ($avatar !== ''): ?>
            <img src="<?= h(media_url($avatar)) ?>" alt="" class="h-10 w-10 rounded-full object-cover">
        <?php else: ?>
            <span class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold text-white" style="background: <?= h($brand) ?>"><?= h(author_initials($author)) ?></span>
        <?php endif; ?>
        <div>
            <p class="font-semibold text-zinc-800 dark:text-zinc-100"><?= h($author) ?></p>
            <p>
                <time><?= h(format_post_date($post['published_at'] ?? null)) ?></time>
                <span aria-hidden="true"> · </span>
                <?= h(t('public.min_read', ['n' => (string) $mins])) ?>
            </p>
        </div>
    </div>
    <?php if ($img !== ''): ?>
        <img src="<?= h($img) ?>" alt="<?= h($post['title']) ?>" class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">
    <?php endif; ?>
    <div class="prose prose-lg prose-zinc mt-10 max-w-none dark:prose-invert prose-headings:font-extrabold prose-a:text-zinc-900 dark:prose-a:text-zinc-100">
        <?= $content ?>
    </div>
</article>
