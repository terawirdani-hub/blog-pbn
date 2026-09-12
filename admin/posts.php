<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_role('admin', 'editor');

if (is_post() && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $st = db()->prepare('DELETE FROM posts WHERE id = ?');
    $st->execute([$id]);
    audit_write('post.delete', 'post', (string) $id);
    flash_set('success', t('flash.deleted'));
    redirect(admin_url('posts.php'));
}

$status = (string) ($_GET['status'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT id, title, slug, status, updated_at FROM posts WHERE 1=1';
$args = [];
if ($status === 'draft' || $status === 'published') {
    $sql .= ' AND status = ?';
    $args[] = $status;
}
if ($q !== '') {
    $sql .= ' AND (title LIKE ? OR slug LIKE ?)';
    $args[] = '%' . $q . '%';
    $args[] = '%' . $q . '%';
}
$sql .= ' ORDER BY updated_at DESC';
$st = db()->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

admin_layout_start(t('posts.title'), 'posts');
?>
<div class="row" style="justify-content:space-between;align-items:center">
    <h1><?= h(t('posts.title')) ?></h1>
    <a class="btn" href="<?= h(admin_url('post-edit.php')) ?>"><?= h(t('posts.new')) ?></a>
</div>
<form class="card row" method="get">
    <div class="field">
        <label><?= field_label('ui.search', 'post_title') ?></label>
        <input type="search" name="q" value="<?= h($q) ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.filter.status', 'filter_status') ?></label>
        <select name="status">
            <option value=""><?= h(t('ui.filter')) ?></option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>><?= h(t('posts.status.published')) ?></option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= h(t('posts.status.draft')) ?></option>
        </select>
    </div>
    <button class="btn secondary" type="submit"><?= h(t('ui.filter')) ?></button>
</form>
<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th><span class="th-wrap"><?= h(t('posts.col.title')) ?> <?= tooltip('col_title') ?></span></th>
            <th><span class="th-wrap"><?= h(t('posts.col.status')) ?> <?= tooltip('col_status') ?></span></th>
            <th><span class="th-wrap"><?= h(t('posts.col.slug')) ?> <?= tooltip('col_slug') ?></span></th>
            <th><span class="th-wrap"><?= h(t('posts.col.updated')) ?> <?= tooltip('col_updated') ?></span></th>
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
                <td><?= h(t('posts.status.' . $row['status'])) ?></td>
                <td>/<?= h($row['slug']) ?></td>
                <td><?= h($row['updated_at']) ?></td>
                <td>
                    <a href="<?= h(admin_url('post-edit.php?id=' . (int) $row['id'])) ?>"><?= h(t('ui.edit')) ?></a>
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