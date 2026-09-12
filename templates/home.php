<?php
declare(strict_types=1);
$intro = sanitize_post_html(setting('homepage_intro'));
if ($intro !== ''): ?>
    <div class="intro"><?= $intro ?></div>
<?php endif; ?>
<?php if (!$posts): ?>
    <p class="muted"><?= h(t('ui.empty')) ?></p>
<?php endif; ?>
<div class="post-list">
    <?php foreach ($posts as $item): ?>
        <article class="card-post">
            <?php if ($item['featured_image'] !== ''): ?>
                <a href="<?= h(url_path($item['slug'])) ?>">
                    <img src="<?= h(url_path($item['featured_image'])) ?>" alt="">
                </a>
            <?php endif; ?>
            <h2><a href="<?= h(url_path($item['slug'])) ?>"><?= h($item['title']) ?></a></h2>
            <?php if (!empty($item['published_at'])): ?>
                <p class="muted"><?= h(t('public.published')) ?>: <?= h(substr((string) $item['published_at'], 0, 10)) ?></p>
            <?php endif; ?>
            <?php if ($item['excerpt'] !== ''): ?>
                <p><?= h($item['excerpt']) ?></p>
            <?php endif; ?>
            <a class="more" href="<?= h(url_path($item['slug'])) ?>"><?= h(t('public.read_more')) ?></a>
        </article>
    <?php endforeach; ?>
</div>
<?php if ($pages > 1): ?>
    <nav class="pager">
        <?php if ($page > 1): ?>
            <a href="<?= h($page === 2 ? url_path() : url_path('page/' . ($page - 1))) ?>"><?= h(t('ui.previous')) ?></a>
        <?php endif; ?>
        <span><?= h(t('ui.pagination')) ?> <?= (int) $page ?> / <?= (int) $pages ?></span>
        <?php if ($page < $pages): ?>
            <a href="<?= h(url_path('page/' . ($page + 1))) ?>"><?= h(t('ui.next')) ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
