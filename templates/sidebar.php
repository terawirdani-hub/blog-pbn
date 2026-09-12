<?php
declare(strict_types=1);

$links = blogroll_items();
$authorName = author_display_name();
$authorBio = trim(setting('author_bio'));
$authorAvatar = setting('author_avatar_path');
$socials = social_links();
$cats = categories_with_counts();
$trending = trending_posts(5, isset($excludeId) ? (int) $excludeId : null);
$brand = setting('primary_color', '#1f6feb');
?>
<aside class="lg:col-span-3">
    <div class="space-y-6 lg:sticky lg:top-24">
        <section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <?php if ($authorAvatar !== ''): ?>
                    <img src="<?= h(media_url($authorAvatar)) ?>" alt="<?= h($authorName) ?>" class="h-16 w-16 rounded-full object-cover ring-2 ring-zinc-100 dark:ring-zinc-800">
                <?php else: ?>
                    <div class="flex h-16 w-16 items-center justify-center rounded-full text-lg font-bold text-white" style="background: <?= h($brand) ?>"><?= h(author_initials($authorName)) ?></div>
                <?php endif; ?>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.author')) ?></p>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white"><?= h($authorName) ?></h2>
                </div>
            </div>
            <?php if ($authorBio !== ''): ?>
                <p class="mt-4 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400"><?= h($authorBio) ?></p>
            <?php endif; ?>
            <?php if ($socials): ?>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($socials as $network => $url): ?>
                        <a href="<?= h($url) ?>" target="_blank" rel="noopener noreferrer" class="rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold capitalize text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"><?= h($network) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($trending): ?>
        <section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.trending')) ?></h2>
            <ol class="mt-4 space-y-4">
                <?php foreach ($trending as $i => $item): ?>
                    <li>
                        <a href="<?= h(url_path($item['slug'])) ?>" class="group flex gap-3">
                            <span class="w-6 shrink-0 text-lg font-extrabold text-zinc-300 dark:text-zinc-600"><?= (int) $i + 1 ?></span>
                            <?php if ($item['featured_image'] !== ''): ?>
                                <img src="<?= h(media_url($item['featured_image'])) ?>" alt="" class="h-14 w-20 shrink-0 rounded-lg object-cover">
                            <?php else: ?>
                                <div class="h-14 w-20 shrink-0 rounded-lg bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-800 dark:to-zinc-700"></div>
                            <?php endif; ?>
                            <span class="min-w-0 text-sm font-semibold leading-snug text-zinc-800 group-hover:underline dark:text-zinc-100"><?= h($item['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
        <?php endif; ?>

        <?php if ($cats): ?>
        <section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.categories')) ?></h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($cats as $cat): ?>
                    <a href="<?= h(url_path('category/' . $cat['slug'])) ?>" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold" style="background: <?= h($cat['color']) ?>22; color: <?= h($cat['color']) ?>">
                        <?= h($cat['name']) ?>
                        <span class="rounded-full bg-white/80 px-1.5 text-[10px] dark:bg-zinc-950/40"><?= (int) $cat['post_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (setting('ad_sidebar_html') !== ''): ?>
            <div class="overflow-hidden rounded-2xl"><?= setting('ad_sidebar_html') ?></div>
        <?php endif; ?>

        <?php if ($links): ?>
        <section class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500"><?= h(t('public.partners')) ?></h2>
            <ul class="mt-4 space-y-3">
                <?php foreach ($links as $link): ?>
                    <?php $kind = blogroll_rel_kind((string) $link['rel']); ?>
                    <li>
                        <a href="<?= h($link['url']) ?>" target="<?= h($link['target']) ?>" rel="<?= h($link['rel']) ?>" class="group flex items-center justify-between gap-2 text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <?= h($link['title']) ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18v4.5M18 6l-7.5 7.5M8.25 6.75H6A2.25 2.25 0 003.75 9v9A2.25 2.25 0 006 20.25h9A2.25 2.25 0 0017.25 18v-2.25"/></svg>
                            </span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-800"><?= h(t('public.' . $kind)) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>
</aside>
