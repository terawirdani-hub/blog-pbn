<?php
declare(strict_types=1);
$links = blogroll_items();
?>
<aside class="sidebar">
    <?php if (setting('ad_sidebar_html') !== ''): ?>
        <div class="ad-slot"><?= setting('ad_sidebar_html') ?></div>
    <?php endif; ?>
    <?php if ($links): ?>
        <section class="widget">
            <h2><?= h(t('public.blogroll')) ?></h2>
            <ul>
                <?php foreach ($links as $link): ?>
                    <li>
                        <a href="<?= h($link['url']) ?>" target="<?= h($link['target']) ?>" rel="<?= h($link['rel']) ?>">
                            <?= h($link['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</aside>
