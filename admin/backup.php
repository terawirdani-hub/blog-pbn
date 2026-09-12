<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$user = require_role('admin');

$error = '';
if (is_post() && ($_POST['action'] ?? '') === 'export_json') {
    csrf_verify();
    $export = ['version' => 1, 'exported_at' => now_utc(), 'settings' => []];
    $st = db()->query('SELECT key, value, is_secret FROM settings');
    foreach ($st as $row) {
        if ((int) $row['is_secret'] === 1) {
            continue;
        }
        $export['settings'][$row['key']] = $row['value'];
    }
    audit_write('backup.export_json', 'settings', 'config');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="turbo-pbn-settings.json"');
    echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (is_post() && ($_POST['action'] ?? '') === 'export_sqlite') {
    csrf_verify();
    $path = app_config('database_path');
    if (!is_string($path) || !is_file($path)) {
        $error = t('error.generic');
    } else {
        db()->exec('PRAGMA wal_checkpoint(FULL)');
        audit_write('backup.export_sqlite', 'database', 'data.sqlite');
        header('Content-Type: application/vnd.sqlite3');
        header('Content-Disposition: attachment; filename="data.sqlite"');
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
        exit;
    }
}

if (is_post() && ($_POST['action'] ?? '') === 'import') {
    csrf_verify();
    $tmp = $_FILES['json']['tmp_name'] ?? '';
    if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
        $error = t('backup.invalid');
    } else {
        $raw = file_get_contents($tmp);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($data) || !isset($data['settings']) || !is_array($data['settings'])) {
            $error = t('backup.invalid');
        } else {
            $defaults = setting_defaults();
            $applied = [];
            $pdo = db();
            $pdo->beginTransaction();
            try {
                foreach ($data['settings'] as $key => $value) {
                    if (!is_string($key) || !isset($defaults[$key])) {
                        continue;
                    }
                    if ($defaults[$key]['secret']) {
                        continue;
                    }
                    if (!is_string($value) && !is_numeric($value)) {
                        continue;
                    }
                    setting_set($key, (string) $value, (int) $user['id']);
                    $applied[] = $key;
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                settings_clear_cache();
                error_log($e->getMessage());
                $error = t('error.generic');
            }
            if ($error === '') {
                audit_write('backup.import_json', 'settings', 'config', ['keys' => $applied]);
                flash_set('success', t('flash.imported'));
                redirect(admin_url('backup.php'));
            }
        }
    }
}

admin_layout_start(t('backup.title'), 'backup');
?>
<h1><?= h(t('backup.title')) ?></h1>
<p><?= h(t('backup.help')) ?></p>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<div class="card">
    <form method="post" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="export_json">
        <button class="btn" type="submit"><?= h(t('backup.export')) ?> <?= tooltip('backup_export') ?></button>
    </form>
    <form method="post" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="export_sqlite">
        <button class="btn secondary" type="submit"><?= h(t('backup.sqlite')) ?> <?= tooltip('backup_sqlite') ?></button>
    </form>
</div>
<form class="card" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="import">
    <div class="field">
        <label><?= field_label('backup.file', 'backup_import') ?></label>
        <input type="file" name="json" accept="application/json,.json" required>
    </div>
    <button class="btn" type="submit"><?= h(t('ui.import')) ?></button>
</form>
<?php admin_layout_end();