<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_role('admin');

$error = '';
if (is_post()) {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create') {
            $id = create_user(
                (string) ($_POST['username'] ?? ''),
                (string) ($_POST['password'] ?? ''),
                ($_POST['role'] ?? 'editor') === 'admin' ? 'admin' : 'editor',
                ($_POST['locale'] ?? 'id') === 'en' ? 'en' : 'id'
            );
            audit_write('user.create', 'user', (string) $id);
            flash_set('success', t('flash.created'));
            redirect(admin_url('users.php'));
        }
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $st = db()->prepare('SELECT id, role FROM users WHERE id = ?');
            $st->execute([$id]);
            $target = $st->fetch();
            if ($target && $target['role'] === 'admin' && admin_count() <= 1) {
                throw new InvalidArgumentException(t('users.delete_last_admin'));
            }
            if ($target) {
                db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                audit_write('user.delete', 'user', (string) $id);
            }
            flash_set('success', t('flash.deleted'));
            redirect(admin_url('users.php'));
        }
        if ($action === 'role') {
            $id = (int) ($_POST['id'] ?? 0);
            $role = ($_POST['role'] ?? 'editor') === 'admin' ? 'admin' : 'editor';
            $st = db()->prepare('SELECT id, role FROM users WHERE id = ?');
            $st->execute([$id]);
            $target = $st->fetch();
            if ($target && $target['role'] === 'admin' && $role !== 'admin' && admin_count() <= 1) {
                throw new InvalidArgumentException(t('users.delete_last_admin'));
            }
            db()->prepare('UPDATE users SET role = ?, updated_at = ? WHERE id = ?')->execute([$role, now_utc(), $id]);
            audit_write('user.role', 'user', (string) $id, ['role' => $role]);
            flash_set('success', t('flash.saved'));
            redirect(admin_url('users.php'));
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = t('error.generic');
    }
}

$rows = db()->query('SELECT id, username, role, locale, created_at FROM users ORDER BY id')->fetchAll();
admin_layout_start(t('users.title'), 'users');
?>
<h1><?= h(t('users.title')) ?></h1>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <h2><?= h(t('users.new')) ?></h2>
    <div class="row">
        <div class="field">
            <label><?= field_label('users.field.username', 'username') ?></label>
            <input type="text" name="username" required maxlength="32">
        </div>
        <div class="field">
            <label><?= field_label('users.field.password', 'password') ?></label>
            <input type="password" name="password" required minlength="10" autocomplete="new-password">
        </div>
        <div class="field">
            <label><?= field_label('users.field.role', 'user_role') ?></label>
            <select name="role">
                <option value="editor"><?= h(t('users.role.editor')) ?></option>
                <option value="admin"><?= h(t('users.role.admin')) ?></option>
            </select>
        </div>
        <div class="field">
            <label><?= field_label('users.field.locale', 'user_locale') ?></label>
            <select name="locale">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
    <button class="btn" type="submit"><?= h(t('ui.add')) ?></button>
</form>
<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th><span class="th-wrap"><?= h(t('users.col.username')) ?> <?= tooltip('username') ?></span></th>
            <th><span class="th-wrap"><?= h(t('users.col.role')) ?> <?= tooltip('col_role') ?></span></th>
            <th><?= h(t('ui.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= h($row['username']) ?></td>
                <td>
                    <form method="post" class="row" style="align-items:center">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="role">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <select name="role">
                            <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>><?= h(t('users.role.admin')) ?></option>
                            <option value="editor" <?= $row['role'] === 'editor' ? 'selected' : '' ?>><?= h(t('users.role.editor')) ?></option>
                        </select>
                        <button class="btn ghost" type="submit"><?= h(t('ui.save')) ?></button>
                    </form>
                </td>
                <td>
                    <form method="post" onsubmit="return confirm('<?= h(t('ui.confirm_delete')) ?>')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn danger" type="submit"><?= h(t('ui.delete')) ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php admin_layout_end();