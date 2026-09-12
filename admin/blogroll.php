<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_role('admin', 'editor');

$error = '';
if (is_post()) {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM blogroll WHERE id = ?')->execute([$id]);
        audit_write('blogroll.delete', 'blogroll', (string) $id);
        flash_set('success', t('flash.deleted'));
        redirect(admin_url('blogroll.php'));
    }
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    $url = trim((string) ($_POST['url'] ?? ''));
    $rel = trim((string) ($_POST['rel'] ?? 'noopener noreferrer'));
    $target = ($_POST['target'] ?? '_blank') === '_self' ? '_self' : '_blank';
    $sort = (int) ($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    if ($title === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $error = t('ui.required');
    } else {
        $now = now_utc();
        if ($id > 0) {
            db()->prepare(
                'UPDATE blogroll SET title=?, url=?, rel=?, target=?, sort_order=?, is_active=?, updated_at=? WHERE id=?'
            )->execute([$title, $url, $rel, $target, $sort, $active, $now, $id]);
            audit_write('blogroll.update', 'blogroll', (string) $id, ['url' => $url]);
        } else {
            db()->prepare(
                'INSERT INTO blogroll (title, url, rel, target, sort_order, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$title, $url, $rel, $target, $sort, $active, $now, $now]);
            audit_write('blogroll.create', 'blogroll', (string) db()->lastInsertId(), ['url' => $url]);
        }
        flash_set('success', t('flash.saved'));
        redirect(admin_url('blogroll.php'));
    }
}

$edit = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $st = db()->prepare('SELECT * FROM blogroll WHERE id = ?');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}

$rows = db()->query('SELECT * FROM blogroll ORDER BY sort_order ASC, id ASC')->fetchAll();
admin_layout_start(t('blogroll.title'), 'blogroll');
?>
<h1><?= h(t('blogroll.title')) ?></h1>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
        <div class="field">
            <label><?= field_label('blogroll.field.title', 'blog_title') ?></label>
            <input type="text" name="title" required value="<?= h($edit['title'] ?? '') ?>">
        </div>
        <div class="field">
            <label><?= field_label('blogroll.field.url', 'blog_url') ?></label>
            <input type="url" name="url" required value="<?= h($edit['url'] ?? '') ?>">
        </div>
    </div>
    <div class="row">
        <div class="field">
            <label><?= field_label('blogroll.field.rel', 'blog_rel') ?></label>
            <input type="text" name="rel" value="<?= h($edit['rel'] ?? 'noopener noreferrer') ?>">
        </div>
        <div class="field">
            <label><?= field_label('blogroll.field.target', 'blog_target') ?></label>
            <select name="target">
                <option value="_blank" <?= (($edit['target'] ?? '_blank') === '_blank') ? 'selected' : '' ?>>_blank</option>
                <option value="_self" <?= (($edit['target'] ?? '') === '_self') ? 'selected' : '' ?>>_self</option>
            </select>
        </div>
        <div class="field">
            <label><?= field_label('blogroll.field.sort', 'blog_sort') ?></label>
            <input type="number" name="sort_order" value="<?= h((string) ($edit['sort_order'] ?? '0')) ?>">
        </div>
        <div class="field">
            <label><?= field_label('blogroll.field.active', 'blog_active') ?></label>
            <input type="checkbox" name="is_active" value="1" <?= !isset($edit['is_active']) || (int) $edit['is_active'] === 1 ? 'checked' : '' ?>>
        </div>
    </div>
    <button class="btn" type="submit"><?= h($edit ? t('ui.save') : t('ui.add')) ?></button>
</form>
<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th><span class="th-wrap"><?= h(t('blogroll.col.title')) ?> <?= tooltip('col_title') ?></span></th>
            <th><span class="th-wrap"><?= h(t('blogroll.col.url')) ?> <?= tooltip('blog_url') ?></span></th>
            <th><span class="th-wrap"><?= h(t('blogroll.col.order')) ?> <?= tooltip('col_order') ?></span></th>
            <th><span class="th-wrap"><?= h(t('blogroll.col.active')) ?> <?= tooltip('col_active') ?></span></th>
            <th><?= h(t('ui.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="5" class="muted"><?= h(t('ui.empty')) ?></td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= h($row['title']) ?></td>
                <td><?= h($row['url']) ?></td>
                <td><?= (int) $row['sort_order'] ?></td>
                <td><?= (int) $row['is_active'] ? h(t('ui.yes')) : h(t('ui.no')) ?></td>
                <td>
                    <a href="<?= h(admin_url('blogroll.php?edit=' . (int) $row['id'])) ?>"><?= h(t('ui.edit')) ?></a>
                    <form method="post" style="display:inline" onsubmit="return confirm('<?= h(t('ui.confirm_delete')) ?>')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn ghost" type="submit"><?= h(t('ui.delete')) ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php admin_layout_end();