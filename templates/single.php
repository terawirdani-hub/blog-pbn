<?php
declare(strict_types=1);
?>
<article class="single">
    <h1><?= h($post['title']) ?></h1>
    <?php if (!empty($post['published_at'])): ?>
        <p class="muted"><?= h(t('public.published')) ?>: <?= h(substr((string) $post['published_at'], 0, 10)) ?></p>
    <?php endif; ?>
    <?php if ($post['featured_image'] !== ''): ?>
        <img class="hero" src="<?= h(url_path($post['featured_image'])) ?>" alt="">
    <?php endif; ?>
    <div class="body"><?= $content ?></div>
</article>
