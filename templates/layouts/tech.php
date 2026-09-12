<?php
declare(strict_types=1);
include __DIR__ . '/../partials/home-intro.php';
$brand = public_palette_colors()['primary'];
$stack = [];
if (!empty($featured) && is_array($featured)) {
    $stack[] = $featured;
}
if (!empty($posts)) {
    $stack = array_merge($stack, $posts);
}
?>
<?php foreach ($stack as $i => $item): ?>
    <?php
    $url = url_path($item['slug']);
    $author = author_display_name($item['author_username'] ?? null);
    $mins = reading_minutes((string) $item['content']);
    $img = $item['featured_image'] !== '' ? media_url($item['featured_image']) : '';
    ?>
    <article class="<?= $i === 0 ? 'mb-12 border-b border-zinc-200 pb-12 dark:border-zinc-800' : 'mb-10 border-b border-zinc-100 pb-10 dark:border-zinc-800' ?>">
        <?php if (!empty($item['category_name'])): ?>
            <a href="<?= h(url_path('category/' . $item['category_slug'])) ?>" class="text-xs font-bold uppercase tracking-[0.18em]" style="color: <?= h((string) $item['category_color']) ?>"><?= h($item['category_name']) ?></a>
        <?php endif; ?>
        <h2 class="mt-3 font-extrabold tracking-tight text-zinc-900 dark:text-white <?= $i === 0 ? 'text-4xl sm:text-5xl' : 'text-2xl' ?>">
            <a href="<?= h($url) ?>" class="hover:underline"><?= h($item['title']) ?></a>
        </h2>
        <p class="mt-3 text-sm text-zinc-500"><?= h($author) ?> · <time><?= h(format_post_date($item['published_at'] ?? null)) ?></time> · <?= h(t('public.min_read', ['n' => (string) $mins])) ?></p>
        <?php if ($i === 0 && $img !== ''): ?>
            <a href="<?= h($url) ?>"><img src="<?= h($img) ?>" alt="<?= h($item['title']) ?>" class="mt-6 aspect-video w-full rounded-xl object-cover"></a>
        <?php elseif ($i === 0): ?>
            <div class="mt-6 overflow-hidden rounded-xl"><?php editorial_cover('aspect-video w-full', '', $item['category_name'] ?? null, $item['category_color'] ?? null); ?></div>
        <?php endif; ?>
        <?php if ($item['excerpt'] !== ''): ?>
            <p class="mt-5 text-lg leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($item['excerpt']) ?></p>
        <?php endif; ?>
        <p class="mt-4"><a href="<?= h($url) ?>" class="text-sm font-semibold hover:underline" style="color: <?= h($brand) ?>"><?= h(t('public.read_more')) ?></a></p>
    </article>
<?php endforeach; ?>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
