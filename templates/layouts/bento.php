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
<div class="grid grid-cols-1 gap-4 md:grid-cols-6 md:grid-rows-2">
    <?php foreach ($items as $i => $item): ?>
        <?php
        $span = 'md:col-span-2';
        if ($i === 0) {
            $span = 'md:col-span-4 md:row-span-2';
        } elseif ($i === 1 || $i === 2) {
            $span = 'md:col-span-2';
        }
        $variant = $i === 0 ? 'bento' : 'grid';
        ?>
        <div class="<?= $span ?>">
            <?php include __DIR__ . '/../partials/post-card.php'; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
