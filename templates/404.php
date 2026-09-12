<?php
declare(strict_types=1);
?>
<div class="rounded-2xl border border-zinc-200 bg-white px-8 py-16 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <p class="text-sm font-semibold uppercase tracking-widest text-zinc-400">404</p>
    <h1 class="mt-3 text-3xl font-extrabold text-zinc-900 dark:text-white"><?= h(t('error.not_found')) ?></h1>
    <p class="mx-auto mt-4 max-w-md text-zinc-600 dark:text-zinc-400"><?= h(t('public.404')) ?></p>
    <a href="<?= h(url_path()) ?>" class="mt-8 inline-flex rounded-full bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900"><?= h(t('public.home')) ?></a>
</div>
