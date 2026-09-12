<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_role('admin');

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 50;
$offset = ($page - 1) * $per;
$total = (int) db()->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
$st = db()->prepare(
    'SELECT a.*, u.username FROM audit_log a
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.id DESC LIMIT ? OFFSET ?'
);
$st->bindValue(1, $per, PDO::PARAM_INT);
$st->bindValue(2, $offset, PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();
$pages = max(1, (int) ceil($total / $per));

admin_layout_start(t('audit.title'), 'audit');
?>
<h1><?= h(t('audit.title')) ?></h1>
<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th><span class="th-wrap"><?= h(t('audit.col.time')) ?> <?= tooltip('col_time') ?></span></th>
            <th><span class="th-wrap"><?= h(t('audit.col.user')) ?> <?= tooltip('col_user') ?></span></th>
            <th><span class="th-wrap"><?= h(t('audit.col.action')) ?> <?= tooltip('audit_action') ?></span></th>
            <th><span class="th-wrap"><?= h(t('audit.col.entity')) ?> <?= tooltip('col_entity') ?></span></th>
            <th><span class="th-wrap"><?= h(t('audit.col.ip')) ?> <?= tooltip('col_ip') ?></span></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="5" class="muted"><?= h(t('ui.empty')) ?></td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= h($row['created_at']) ?></td>
                <td><?= h($row['username'] ?? '—') ?></td>
                <td><?= h($row['action']) ?></td>
                <td><?= h(trim($row['entity'] . ' ' . $row['entity_id'])) ?></td>
                <td><?= h($row['ip']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted"><?= h(t('ui.pagination')) ?> <?= (int) $page ?> / <?= (int) $pages ?></p>
    <?php if ($page > 1): ?>
        <a href="<?= h(admin_url('audit.php?page=' . ($page - 1))) ?>"><?= h(t('ui.previous')) ?></a>
    <?php endif; ?>
    <?php if ($page < $pages): ?>
        <a href="<?= h(admin_url('audit.php?page=' . ($page + 1))) ?>"><?= h(t('ui.next')) ?></a>
    <?php endif; ?>
</div>
<?php admin_layout_end();