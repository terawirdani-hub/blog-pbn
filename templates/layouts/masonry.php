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
?>
<?php if ($items): ?>
<div class="columns-1 gap-5 sm:columns-2">
    <?php foreach ($items as $item): ?>
        <?php $variant = 'masonry'; include __DIR__ . '/../partials/post-card.php'; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
