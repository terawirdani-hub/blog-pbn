    </div>
    <?php if (public_layout_has_sidebar()): ?>
        <?php include __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>
</div>
<?php if (setting('ad_footer_html') !== ''): ?>
    <div class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8"><?= setting('ad_footer_html') ?></div>
<?php endif; ?>
<footer class="border-t border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <p class="text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white"><?= h($siteName) ?></p>
            <?php if ($tagline !== ''): ?>
                <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($tagline) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.quick_links')) ?></p>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a class="text-zinc-700 hover:underline dark:text-zinc-300" href="<?= h(url_path()) ?>"><?= h(t('public.home')) ?></a></li>
                <?php foreach (array_slice($navCategories, 0, 8) as $cat): ?>
                    <li><a class="text-zinc-700 hover:underline dark:text-zinc-300" href="<?= h(url_path('category/' . $cat['slug'])) ?>"><?= h($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.copyright')) ?></p>
            <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                <?= h(setting('footer_text') !== '' ? setting('footer_text') : ('© ' . date('Y') . ' ' . $siteName)) ?>
            </p>
        </div>
    </div>
</footer>
</body>
</html>
