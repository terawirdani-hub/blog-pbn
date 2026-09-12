<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function admin_layout_start(string $title, string $active = ''): void
{
    $user = current_user();
    $theme = $user['theme'] ?? 'dark';
    if (!in_array($theme, ['dark', 'light'], true)) {
        $theme = 'dark';
    }
    $flash = flash_get();
    header('Content-Type: text/html; charset=utf-8');
    $primary = setting('primary_color', '#1f6feb');
    $accent = setting('accent_color', '#238636');
    ?>
<!DOCTYPE html>
<html lang="<?= h(locale()) ?>" data-theme="<?= h($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= h($title) ?> — <?= h(t('app.name')) ?></title>
    <link rel="stylesheet" href="<?= h(url_path('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= h(url_path('admin/assets/admin.css')) ?>">
    <style>:root { --primary: <?= h($primary) ?>; --accent: <?= h($accent) ?>; }</style>
</head>
<body class="admin">
<div class="shell">
    <aside class="side">
        <a class="brand" href="<?= h(admin_url('index.php')) ?>"><?= h(t('app.name')) ?></a>
        <?php if ($user): ?>
        <nav>
            <a class="<?= $active === 'dash' ? 'is-on' : '' ?>" href="<?= h(admin_url('index.php')) ?>"><?= h(t('nav.dashboard')) ?></a>
            <a class="<?= $active === 'posts' ? 'is-on' : '' ?>" href="<?= h(admin_url('posts.php')) ?>"><?= h(t('nav.posts')) ?></a>
            <a class="<?= $active === 'blogroll' ? 'is-on' : '' ?>" href="<?= h(admin_url('blogroll.php')) ?>"><?= h(t('nav.blogroll')) ?></a>
            <?php if ($user['role'] === 'admin'): ?>
            <a class="<?= $active === 'settings' ? 'is-on' : '' ?>" href="<?= h(admin_url('settings.php')) ?>"><?= h(t('nav.settings')) ?></a>
            <a class="<?= $active === 'users' ? 'is-on' : '' ?>" href="<?= h(admin_url('users.php')) ?>"><?= h(t('nav.users')) ?></a>
            <a class="<?= $active === 'audit' ? 'is-on' : '' ?>" href="<?= h(admin_url('audit.php')) ?>"><?= h(t('nav.audit')) ?></a>
            <a class="<?= $active === 'backup' ? 'is-on' : '' ?>" href="<?= h(admin_url('backup.php')) ?>"><?= h(t('nav.backup')) ?></a>
            <?php endif; ?>
            <a class="<?= $active === 'profile' ? 'is-on' : '' ?>" href="<?= h(admin_url('profile.php')) ?>"><?= h(t('nav.profile')) ?></a>
            <a href="<?= h(url_path()) ?>" target="_blank" rel="noopener"><?= h(t('nav.view_site')) ?></a>
        </nav>
        <p class="who"><?= h(t('ui.logged_in_as', ['name' => $user['username']])) ?></p>
        <a class="logout" href="<?= h(admin_url('logout.php')) ?>"><?= h(t('ui.logout')) ?></a>
        <?php endif; ?>
    </aside>
    <main class="main">
        <?php if ($flash): ?>
            <div class="flash flash-<?= h((string) $flash['type']) ?>"><?= h((string) $flash['message']) ?></div>
        <?php endif; ?>
    <?php
}

function admin_layout_end(): void
{
    ?>
    </main>
</div>
</body>
</html>
    <?php
}

function admin_gate(): void
{
    if (user_count() === 0) {
        $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script !== 'setup.php') {
            redirect(admin_url('setup.php'));
        }
    }
}
