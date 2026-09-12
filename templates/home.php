<?php
declare(strict_types=1);

$intro = sanitize_post_html(setting('homepage_intro'));
$pageBase = isset($category) && is_array($category) ? 'category/' . $category['slug'] : '';
?>
<?php if (!empty($category) && is_array($category)): ?>
    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500"><?= h(t('public.categories')) ?></p>
    <h1 class="mb-8 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-4xl"><?= h($category['name']) ?></h1>
<?php elseif ($intro !== '' && (int) $page === 1): ?>
    <div class="prose prose-zinc mb-10 max-w-none dark:prose-invert"><?= $intro ?></div>
<?php endif; ?>

<?php if (empty($posts) && empty($featured)): ?>
    <p class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400"><?= h(t('ui.empty')) ?></p>
<?php endif; ?>

<?php if (!empty($featured) && is_array($featured)): ?>
    <?php
    $featUrl = url_path($featured['slug']);
    $featImg = $featured['featured_image'] !== '' ? media_url($featured['featured_image']) : '';
    $featAuthor = author_display_name($featured['author_username'] ?? null);
    $featMins = reading_minutes((string) $featured['content']);
    ?>
    <article class="group mb-10 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <a href="<?= h($featUrl) ?>" class="block overflow-hidden">
            <?php if ($featImg !== ''): ?>
                <img src="<?= h($featImg) ?>" alt="<?= h($featured['title']) ?>" class="aspect-[16/8] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
            <?php else: ?>
                <div class="aspect-[16/8] w-full bg-gradient-to-br from-zinc-200 via-zinc-100 to-zinc-300 dark:from-zinc-800 dark:via-zinc-900 dark:to-zinc-700"></div>
            <?php endif; ?>
        </a>
        <div class="p-6 sm:p-8">
            <?php if (!empty($featured['category_name'])): ?>
                <a href="<?= h(url_path('category/' . $featured['category_slug'])) ?>" class="mb-3 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" style="background: <?= h((string) $featured['category_color']) ?>22; color: <?= h((string) $featured['category_color']) ?>"><?= h($featured['category_name']) ?></a>
            <?php else: ?>
                <span class="mb-3 inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><?= h(t('public.featured')) ?></span>
            <?php endif; ?>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                <a href="<?= h($featUrl) ?>" class="hover:underline"><?= h($featured['title']) ?></a>
            </h2>
            <?php if ($featured['excerpt'] !== ''): ?>
                <p class="mt-4 max-w-3xl text-base leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($featured['excerpt']) ?></p>
            <?php endif; ?>
            <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                <span class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold text-white" style="background: <?= h(setting('primary_color', '#1f6feb')) ?>"><?= h(author_initials($featAuthor)) ?></span>
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
        <?php
        $url = url_path($item['slug']);
        $img = $item['featured_image'] !== '' ? media_url($item['featured_image']) : '';
        $author = author_display_name($item['author_username'] ?? null);
        $mins = reading_minutes((string) $item['content']);
        ?>
        <article class="group overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
            <a href="<?= h($url) ?>" class="block overflow-hidden">
                <?php if ($img !== ''): ?>
                    <img src="<?= h($img) ?>" alt="<?= h($item['title']) ?>" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105">
                <?php else: ?>
                    <div class="aspect-[16/10] w-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-800 dark:to-zinc-700"></div>
                <?php endif; ?>
            </a>
            <div class="p-5">
                <?php if (!empty($item['category_name'])): ?>
                    <a href="<?= h(url_path('category/' . $item['category_slug'])) ?>" class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide" style="background: <?= h((string) $item['category_color']) ?>22; color: <?= h((string) $item['category_color']) ?>"><?= h($item['category_name']) ?></a>
                <?php endif; ?>
                <h2 class="mt-2 text-lg font-bold leading-snug tracking-tight text-zinc-900 dark:text-white">
                    <a href="<?= h($url) ?>" class="hover:underline"><?= h($item['title']) ?></a>
                </h2>
                <?php if ($item['excerpt'] !== ''): ?>
                    <p class="mt-2 line-clamp-3 text-sm text-zinc-600 dark:text-zinc-400"><?= h($item['excerpt']) ?></p>
                <?php endif; ?>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-white" style="background: <?= h(setting('primary_color', '#1f6feb')) ?>"><?= h(author_initials($author)) ?></span>
                    <span><?= h($author) ?></span>
                    <span aria-hidden="true">·</span>
                    <time><?= h(format_post_date($item['published_at'] ?? null)) ?></time>
                    <span aria-hidden="true">·</span>
                    <span><?= h(t('public.min_read', ['n' => (string) $mins])) ?></span>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($pages) && (int) $pages > 1): ?>
    <nav class="mt-10 flex items-center justify-between text-sm font-medium" aria-label="<?= h(t('ui.pagination')) ?>">
        <?php if ((int) $page > 1): ?>
            <?php
            $prev = (int) $page === 2
                ? ($pageBase !== '' ? url_path($pageBase) : url_path())
                : ($pageBase !== '' ? url_path($pageBase . '/page/' . ((int) $page - 1)) : url_path('page/' . ((int) $page - 1)));
            ?>
            <a class="rounded-full border border-zinc-200 px-4 py-2 hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-900" href="<?= h($prev) ?>"><?= h(t('ui.previous')) ?></a>
        <?php else: ?>
            <span></span>
        <?php endif; ?>
        <span class="text-zinc-500"><?= h(t('ui.pagination')) ?> <?= (int) $page ?> / <?= (int) $pages ?></span>
        <?php if ((int) $page < (int) $pages): ?>
            <?php
            $next = $pageBase !== ''
                ? url_path($pageBase . '/page/' . ((int) $page + 1))
                : url_path('page/' . ((int) $page + 1));
            ?>
            <a class="rounded-full border border-zinc-200 px-4 py-2 hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-900" href="<?= h($next) ?>"><?= h(t('ui.next')) ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
