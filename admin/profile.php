<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$user = require_login();

$error = '';
if (is_post()) {
    csrf_verify();
    $locale = ($_POST['locale'] ?? 'id') === 'en' ? 'en' : 'id';
    $theme = ($_POST['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';
    $pass = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    try {
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET locale = ?, theme = ?, updated_at = ? WHERE id = ?')
            ->execute([$locale, $theme, now_utc(), (int) $user['id']]);
        if ($pass !== '' || $confirm !== '') {
            if ($pass !== $confirm) {
                throw new InvalidArgumentException(t('profile.password_mismatch'));
            }
            if (strlen($pass) < 10) {
                throw new InvalidArgumentException(t('error.password_short'));
            }
            $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?')
                ->execute([password_hash($pass, PASSWORD_DEFAULT), now_utc(), (int) $user['id']]);
            flash_set('success', t('flash.password_changed'));
        } else {
            flash_set('success', t('flash.saved'));
        }
        $pdo->commit();
        persist_locale_cookie($locale);
        redirect(admin_url('profile.php'));
    } catch (InvalidArgumentException $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = $e->getMessage();
    }
}

$user = current_user() ?? $user;
admin_layout_start(t('profile.title'), 'profile');
?>
<h1><?= h(t('profile.title')) ?></h1>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post">
    <?= csrf_field() ?>
    <div class="field">
        <label><?= field_label('profile.locale', 'profile_locale') ?></label>
        <select name="locale">
            <option value="id" <?= $user['locale'] === 'id' ? 'selected' : '' ?>>Indonesia</option>
            <option value="en" <?= $user['locale'] === 'en' ? 'selected' : '' ?>>English</option>
        </select>
    </div>
    <div class="field">
        <label><?= field_label('profile.theme', 'profile_theme') ?></label>
        <select name="theme">
            <option value="dark" <?= $user['theme'] === 'dark' ? 'selected' : '' ?>><?= h(t('ui.theme.dark')) ?></option>
            <option value="light" <?= $user['theme'] === 'light' ? 'selected' : '' ?>><?= h(t('ui.theme.light')) ?></option>
        </select>
    </div>
    <div class="field">
        <label><?= field_label('profile.password', 'profile_password') ?></label>
        <input type="password" name="password" minlength="10" autocomplete="new-password">
    </div>
    <div class="field">
        <label><?= field_label('profile.password_confirm', 'profile_password') ?></label>
        <input type="password" name="password_confirm" minlength="10" autocomplete="new-password">
    </div>
    <button class="btn" type="submit"><?= h(t('ui.save')) ?></button>
</form>
<?php admin_layout_end();