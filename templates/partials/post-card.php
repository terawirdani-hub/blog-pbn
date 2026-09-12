<?php
declare(strict_types=1);

$variant = $variant ?? 'grid';
$url = url_path($item['slug']);
$img = $item['featured_image'] !== '' ? media_url($item['featured_image']) : '';
$author = author_display_name($item['author_username'] ?? null);
$mins = reading_minutes((string) $item['content']);
$brand = public_palette_colors()['primary'];
$showExcerpt = $variant !== 'compact';
$imageClass = $variant === 'row' ? 'h-28 w-40 shrink-0 object-cover sm:h-32 sm:w-48' : 'aspect-video w-full object-cover';
$coverClass = $variant === 'row' ? 'h-28 w-40 shrink-0 sm:h-32 sm:w-48' : 'aspect-video w-full';
$cardClass = 'group overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900';
if ($variant === 'row') {
    $cardClass = 'group flex overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900';
}
if ($variant === 'newspaper') {
    $cardClass = 'break-inside-avoid border-b border-zinc-300 pb-5 dark:border-zinc-700';
}
if ($variant === 'masonry') {
    $cardClass .= ' mb-5 break-inside-avoid';
    $imageClass = 'w-full object-cover';
    $coverClass = $variant === 'masonry' ? 'min-h-[10rem] w-full' : $coverClass;
}
?>
<article class="<?= $cardClass ?>">
    <a href="<?= h($url) ?>" class="<?= $variant === 'row' ? 'block shrink-0 overflow-hidden' : 'block overflow-hidden' ?>">
        <?php if ($img !== ''): ?>
            <img src="<?= h($img) ?>" alt="<?= h($item['title']) ?>" class="<?= $imageClass ?> transition duration-500 group-hover:scale-[1.03] <?= $variant === 'masonry' ? 'max-h-72' : '' ?>">
        <?php else: ?>
            <?php editorial_cover($coverClass, $variant === 'grid' || $variant === 'bento' || $variant === 'masonry' ? (string) $item['title'] : '', $item['category_name'] ?? null, $item['category_color'] ?? null); ?>
        <?php endif; ?>
    </a>
    <div class="<?= $variant === 'newspaper' ? 'pt-3' : 'p-5' ?>">
        <?php if (!empty($item['category_name'])): ?>
            <a href="<?= h(url_path('category/' . $item['category_slug'])) ?>" class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide" style="background: <?= h((string) $item['category_color']) ?>22; color: <?= h((string) $item['category_color']) ?>"><?= h($item['category_name']) ?></a>
        <?php endif; ?>
        <h2 class="mt-2 font-bold leading-snug tracking-tight text-zinc-900 dark:text-white <?= $variant === 'newspaper' ? 'font-serif text-xl' : 'text-lg' ?>">
            <a href="<?= h($url) ?>" class="hover:underline"><?= h($item['title']) ?></a>
        </h2>
        <?php if ($showExcerpt && $item['excerpt'] !== ''): ?>
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($item['excerpt']) ?></p>
        <?php endif; ?>
        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
            <?php if (setting('author_avatar_path') !== ''): ?>
                <img src="<?= h(media_url(setting('author_avatar_path'))) ?>" alt="" class="h-7 w-7 rounded-full object-cover ring-2 ring-white dark:ring-zinc-800">
            <?php else: ?>
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-white" style="background: <?= h($brand) ?>"><?= h(author_initials($author)) ?></span>
            <?php endif; ?>
            <span class="font-medium text-zinc-700 dark:text-zinc-300"><?= h($author) ?></span>
            <span aria-hidden="true">·</span>
            <time><?= h(format_post_date($item['published_at'] ?? null)) ?></time>
            <span aria-hidden="true">·</span>
            <span><?= h(t('public.min_read', ['n' => (string) $mins])) ?></span>
        </div>
    </div>
</article>
