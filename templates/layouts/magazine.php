<?php
declare(strict_types=1);
include __DIR__ . '/../partials/home-intro.php';
$brand = public_palette_colors()['primary'];
?>
<?php if (!empty($featured) && is_array($featured)): ?>
    <?php
    $featUrl = url_path($featured['slug']);
    $featImg = $featured['featured_image'] !== '' ? media_url($featured['featured_image']) : '';
    $featAuthor = author_display_name($featured['author_username'] ?? null);
    $featMins = reading_minutes((string) $featured['content']);
    ?>
    <article class="group mb-10 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:grid lg:grid-cols-2">
        <a href="<?= h($featUrl) ?>" class="block overflow-hidden">
            <?php if ($featImg !== ''): ?>
                <img src="<?= h($featImg) ?>" alt="<?= h($featured['title']) ?>" class="aspect-video h-full w-full object-cover transition duration-500 group-hover:scale-[1.03] lg:aspect-auto lg:min-h-[22rem]">
            <?php else: ?>
                <?php editorial_cover('aspect-video w-full lg:min-h-[22rem]', '', $featured['category_name'] ?? t('public.featured'), $featured['category_color'] ?? null); ?>
            <?php endif; ?>
        </a>
        <div class="flex flex-col justify-center p-6 sm:p-8">
            <?php if (!empty($featured['category_name'])): ?>
                <a href="<?= h(url_path('category/' . $featured['category_slug'])) ?>" class="mb-3 inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" style="background: <?= h((string) $featured['category_color']) ?>22; color: <?= h((string) $featured['category_color']) ?>"><?= h($featured['category_name']) ?></a>
            <?php else: ?>
                <span class="mb-3 inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide text-white" style="background: <?= h($brand) ?>"><?= h(t('public.featured')) ?></span>
            <?php endif; ?>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                <a href="<?= h($featUrl) ?>" class="hover:underline"><?= h($featured['title']) ?></a>
            </h2>
            <?php if ($featured['excerpt'] !== ''): ?>
                <p class="mt-4 max-w-3xl text-base leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($featured['excerpt']) ?></p>
            <?php endif; ?>
            <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                <span class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold text-white" style="background: <?= h($brand) ?>"><?= h(author_initials($featAuthor)) ?></span>
                <span class="font-medium text-zinc-700 dark:text-zinc-200"><?= h($featAuthor) ?></span>
                <span aria-hidden="true">·</span>
                <time><?= h(format_post_date($featured['published_at'] ?? null)) ?></time>
                <span aria-hidden="true">·</span>
                <span><?= h(t('public.min_read', ['n' => (string) $featMins])) ?></span>
            </div>
        </div>
    </article>
<?php endif; ?>

<?php if (!empty($posts)): ?>
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <?php foreach ($posts as $item): ?>
        <?php $variant = 'grid'; include __DIR__ . '/../partials/post-card.php'; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
