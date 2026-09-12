<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function admin_icon(string $name): string
{
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a.75.75 0 011.06 0L21.219 12M4.5 9.75V19.5A2.25 2.25 0 006.75 21.75h10.5A2.25 2.25 0 0019.5 19.5V9.75"/>',
        'posts' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
        'categories' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>',
        'blogroll' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
        'audit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'backup' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3"/>',
        'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
        'external' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18v4.5M18 6l-7.5 7.5M8.25 6.75H6A2.25 2.25 0 003.75 9v9A2.25 2.25 0 006 20.25h9A2.25 2.25 0 0017.25 18v-2.25"/>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>',
    ];
    $path = $icons[$name] ?? $icons['home'];
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="nav-ico" aria-hidden="true">' . $path . '</svg>';
}

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
    $logo = setting('logo_path');
    $drafts = 0;
    if ($user) {
        $drafts = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
    }
    $crumbs = [t('nav.dashboard')];
    if ($active !== '' && $active !== 'dash') {
        $map = [
            'posts' => t('nav.posts'),
            'categories' => t('nav.categories'),
            'blogroll' => t('nav.blogroll'),
            'settings' => t('nav.settings'),
            'users' => t('nav.users'),
            'audit' => t('nav.audit'),
            'backup' => t('nav.backup'),
            'profile' => t('nav.profile'),
        ];
        if (isset($map[$active])) {
            $crumbs[] = $map[$active];
        }
    }
    ?>
<!DOCTYPE html>
<html lang="<?= h(locale()) ?>" data-theme="<?= h($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= h($title) ?> — <?= h(t('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(url_path('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= h(url_path('admin/assets/admin.css')) ?>">
    <style>:root { --primary: <?= h($primary) ?>; --accent: <?= h($accent) ?>; }</style>
</head>
<body class="admin">
<?php if (!$user): ?>
<div class="auth-screen">
    <main class="auth-panel">
        <?php if ($flash): ?>
            <div class="flash flash-<?= h((string) $flash['type']) ?>"><?= h((string) $flash['message']) ?></div>
        <?php endif; ?>
<?php else: ?>
<div class="shell">
    <aside class="side">
        <a class="brand" href="<?= h(admin_url('index.php')) ?>">
            <?php if ($logo !== ''): ?>
                <img src="<?= h(media_url($logo)) ?>" alt="">
            <?php endif; ?>
            <span><?= h(setting('site_name')) ?></span>
        </a>
        <p class="side-kicker"><?= h(t('app.name')) ?></p>
        <nav>
            <a class="<?= $active === 'dash' ? 'is-on' : '' ?>" href="<?= h(admin_url('index.php')) ?>"><?= admin_icon('home') ?><span><?= h(t('nav.dashboard')) ?></span></a>
            <a class="<?= $active === 'posts' ? 'is-on' : '' ?>" href="<?= h(admin_url('posts.php')) ?>"><?= admin_icon('posts') ?><span><?= h(t('nav.posts')) ?></span><?php if ($drafts > 0): ?><em class="badge"><?= (int) $drafts ?></em><?php endif; ?></a>
            <a class="<?= $active === 'categories' ? 'is-on' : '' ?>" href="<?= h(admin_url('categories.php')) ?>"><?= admin_icon('categories') ?><span><?= h(t('nav.categories')) ?></span></a>
            <a class="<?= $active === 'blogroll' ? 'is-on' : '' ?>" href="<?= h(admin_url('blogroll.php')) ?>"><?= admin_icon('blogroll') ?><span><?= h(t('nav.blogroll')) ?></span></a>
            <?php if ($user['role'] === 'admin'): ?>
            <a class="<?= $active === 'settings' ? 'is-on' : '' ?>" href="<?= h(admin_url('settings.php')) ?>"><?= admin_icon('settings') ?><span><?= h(t('nav.settings')) ?></span></a>
            <a class="<?= $active === 'users' ? 'is-on' : '' ?>" href="<?= h(admin_url('users.php')) ?>"><?= admin_icon('users') ?><span><?= h(t('nav.users')) ?></span></a>
            <a class="<?= $active === 'audit' ? 'is-on' : '' ?>" href="<?= h(admin_url('audit.php')) ?>"><?= admin_icon('audit') ?><span><?= h(t('nav.audit')) ?></span></a>
            <a class="<?= $active === 'backup' ? 'is-on' : '' ?>" href="<?= h(admin_url('backup.php')) ?>"><?= admin_icon('backup') ?><span><?= h(t('nav.backup')) ?></span></a>
            <?php endif; ?>
            <a class="<?= $active === 'profile' ? 'is-on' : '' ?>" href="<?= h(admin_url('profile.php')) ?>"><?= admin_icon('profile') ?><span><?= h(t('nav.profile')) ?></span></a>
        </nav>
        <a class="logout" href="<?= h(admin_url('logout.php')) ?>"><?= admin_icon('logout') ?><span><?= h(t('ui.logout')) ?></span></a>
    </aside>
    <div class="workspace">
        <header class="topbar">
            <nav class="crumbs" aria-label="breadcrumb">
                <?php foreach ($crumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span class="crumbs-sep">/</span><?php endif; ?>
                    <span><?= h($crumb) ?></span>
                <?php endforeach; ?>
            </nav>
            <div class="topbar-end">
                <a class="btn ghost" href="<?= h(url_path()) ?>" target="_blank" rel="noopener"><?= admin_icon('external') ?><?= h(t('nav.view_site')) ?></a>
                <a class="profile-chip" href="<?= h(admin_url('profile.php')) ?>">
                    <span class="avatar"><?= h(strtoupper(substr($user['username'], 0, 1))) ?></span>
                    <span>
                        <strong><?= h($user['username']) ?></strong>
                        <small><?= h(t('users.role.' . $user['role'])) ?></small>
                    </span>
                </a>
            </div>
        </header>
        <main class="main">
            <?php if ($flash): ?>
                <div class="flash flash-<?= h((string) $flash['type']) ?>"><?= h((string) $flash['message']) ?></div>
            <?php endif; ?>
<?php
    endif;
}

function admin_layout_end(): void
{
    $user = current_user();
    ?>
    <?php if ($user): ?>
        </main>
    </div>
</div>
    <?php else: ?>
    </main>
</div>
    <?php endif; ?>
<script src="<?= h(url_path('admin/assets/admin.js')) ?>"></script>
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

function media_uploader(string $name, string $currentPath, string $labelKey, string $tipKey, bool $compact = false): void
{
    $id = 'upl-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    $has = $currentPath !== '';
    ?>
    <div class="media-field" data-uploader>
        <label><?= field_label($labelKey, $tipKey) ?></label>
        <div class="media-row">
            <div class="media-preview <?= $compact ? 'is-fav' : 'is-logo' ?>" data-preview>
                <?php if ($has): ?>
                    <img src="<?= h(media_url($currentPath)) ?>" alt="">
                <?php else: ?>
                    <span class="media-empty"><?= h(t('ui.preview')) ?></span>
                <?php endif; ?>
            </div>
            <div class="media-actions">
                <input type="file" id="<?= h($id) ?>" name="<?= h($name) ?>" accept=".png,.jpg,.jpeg,.svg,.ico,.webp,image/png,image/jpeg,image/svg+xml,image/x-icon,image/webp" data-live-file>
                <div class="media-btns">
                    <label class="btn ghost" for="<?= h($id) ?>"><?= h($has ? t('ui.replace') : t('ui.change')) ?></label>
                    <?php if ($has): ?>
                        <label class="btn danger ghost"><input type="checkbox" name="remove_<?= h($name) ?>" value="1"> <?= h(t('ui.remove')) ?></label>
                    <?php endif; ?>
                </div>
                <p class="muted"><?= h(t('settings.media_hint')) ?></p>
            </div>
        </div>
    </div>
    <?php
}
