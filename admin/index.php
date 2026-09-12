<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
admin_gate();

if (user_count() === 0) {
    redirect(admin_url('setup.php'));
}

if (!current_user()) {
    $error = '';
    if (is_post()) {
        csrf_verify();
        $ok = attempt_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
        if ($ok) {
            redirect(admin_url('index.php'));
        }
        $error = t('auth.failed');
    }
    admin_layout_start(t('auth.login_title'), '');
    ?>
    <div class="auth-wrap card">
        <h1><?= h(t('auth.login_title')) ?></h1>
        <?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="field">
                <label><?= field_label('auth.username', 'username') ?></label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="field">
                <label><?= field_label('auth.password', 'password') ?></label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button class="btn" type="submit"><?= h(t('auth.login')) ?></button>
        </form>
    </div>
    <?php
    admin_layout_end();
    exit;
}

$user = require_login();
$posts = (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$pub = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$drafts = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
$links = (int) db()->query('SELECT COUNT(*) FROM blogroll')->fetchColumn();

admin_layout_start(t('dash.title'), 'dash');
?>
<h1><?= h(t('dash.title')) ?></h1>
<p><?= h(t('dash.welcome')) ?></p>
<div class="stats">
    <div class="stat"><span><?= h(t('dash.posts')) ?></span><b><?= (int) $posts ?></b></div>
    <div class="stat"><span><?= h(t('dash.published')) ?></span><b><?= (int) $pub ?></b></div>
    <div class="stat"><span><?= h(t('dash.drafts')) ?></span><b><?= (int) $drafts ?></b></div>
    <div class="stat"><span><?= h(t('dash.links')) ?></span><b><?= (int) $links ?></b></div>
</div>
<?php
admin_layout_end();
