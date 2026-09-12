<?php
declare(strict_types=1);
include __DIR__ . '/../partials/home-intro.php';
$items = [];
if (!empty($featured) && is_array($featured)) {
    $items[] = $featured;
}
if (!empty($posts)) {
    $items = array_merge($items, $posts);
}
$lead = $items[0] ?? null;
$rest = array_slice($items, 1);
?>
<?php if ($lead): ?>
    <?php $item = $lead; $variant = 'newspaper'; ?>
    <div class="mb-8 border-y-4 border-zinc-900 py-6 dark:border-white">
        <?php include __DIR__ . '/../partials/post-card.php'; ?>
    </div>
<?php endif; ?>
<?php if ($rest): ?>
<div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:divide-x md:divide-zinc-200 dark:md:divide-zinc-800">
    <?php foreach (array_chunk($rest, max(1, (int) ceil(count($rest) / 3))) as $col): ?>
        <div class="space-y-6 md:px-4 first:md:pl-0 last:md:pr-0">
            <?php foreach ($col as $item): ?>
                <?php $variant = 'newspaper'; include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
