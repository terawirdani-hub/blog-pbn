<?php
declare(strict_types=1);

if (empty($pages) || (int) $pages <= 1) {
    return;
}
$pageBase = isset($category) && is_array($category) ? 'category/' . $category['slug'] : '';
$q = isset($query) ? trim((string) $query) : '';
$suffix = $q !== '' ? '?q=' . rawurlencode($q) : '';
?>
<nav class="mt-10 flex items-center justify-between text-sm font-medium" aria-label="<?= h(t('ui.pagination')) ?>">
    <?php if ((int) $page > 1): ?>
        <?php
        $prev = (int) $page === 2
            ? ($pageBase !== '' ? url_path($pageBase) : url_path())
            : ($pageBase !== '' ? url_path($pageBase . '/page/' . ((int) $page - 1)) : url_path('page/' . ((int) $page - 1)));
        ?>
        <a class="rounded-full border border-zinc-200 px-4 py-2 hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-900" href="<?= h($prev . $suffix) ?>"><?= h(t('ui.previous')) ?></a>
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
        <a class="rounded-full border border-zinc-200 px-4 py-2 hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-900" href="<?= h($next . $suffix) ?>"><?= h(t('ui.next')) ?></a>
    <?php endif; ?>
</nav>
