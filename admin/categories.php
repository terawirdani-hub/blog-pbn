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
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        audit_write('category.delete', 'category', (string) $id);
        flash_set('success', t('flash.deleted'));
        redirect(admin_url('categories.php'));
    }
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $slugIn = trim((string) ($_POST['slug'] ?? ''));
    $color = (string) ($_POST['color'] ?? '#2563eb');
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#2563eb';
    }
    if ($name === '') {
        $error = t('ui.required');
    } else {
        $now = now_utc();
        $slug = unique_category_slug(db(), $slugIn !== '' ? $slugIn : $name, $id > 0 ? $id : null);
        if ($id > 0) {
            db()->prepare('UPDATE categories SET name=?, slug=?, color=?, updated_at=? WHERE id=?')
                ->execute([$name, $slug, $color, $now, $id]);
            audit_write('category.update', 'category', (string) $id, ['name' => $name]);
        } else {
            db()->prepare('INSERT INTO categories (name, slug, color, created_at, updated_at) VALUES (?, ?, ?, ?, ?)')
                ->execute([$name, $slug, $color, $now, $now]);
            audit_write('category.create', 'category', (string) db()->lastInsertId(), ['name' => $name]);
        }
        flash_set('success', t('flash.saved'));
        redirect(admin_url('categories.php'));
    }
}

$edit = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $st = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}
$rows = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

admin_layout_start(t('categories.title'), 'categories');
?>
<h1><?= h(t('categories.title')) ?></h1>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
        <div class="field">
            <label><?= field_label('categories.field.name', 'cat_name') ?></label>
            <input type="text" name="name" required value="<?= h($edit['name'] ?? '') ?>">
        </div>
        <div class="field">
            <label><?= field_label('categories.field.slug', 'cat_slug') ?></label>
            <input type="text" name="slug" value="<?= h($edit['slug'] ?? '') ?>">
        </div>
        <div class="field">
            <label><?= field_label('categories.field.color', 'cat_color') ?></label>
            <input type="color" name="color" value="<?= h($edit['color'] ?? '#2563eb') ?>">
        </div>
    </div>
    <button class="btn" type="submit"><?= h($edit ? t('ui.save') : t('ui.add')) ?></button>
</form>
<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th><span class="th-wrap"><?= h(t('categories.field.name')) ?> <?= tooltip('cat_name') ?></span></th>
            <th><span class="th-wrap"><?= h(t('categories.field.slug')) ?> <?= tooltip('cat_slug') ?></span></th>
            <th><?= h(t('ui.actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="3" class="muted"><?= h(t('ui.empty')) ?></td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= h($row['name']) ?></td>
                <td>/category/<?= h($row['slug']) ?></td>
                <td>
                    <a href="<?= h(admin_url('categories.php?edit=' . (int) $row['id'])) ?>"><?= h(t('ui.edit')) ?></a>
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