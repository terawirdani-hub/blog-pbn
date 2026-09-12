<?php
declare(strict_types=1);

function audit_write(string $action, string $entity = '', string $entityId = '', $details = null): void
{
    $user = current_user();
    $json = '';
    if ($details !== null) {
        $encoded = json_encode($details, JSON_UNESCAPED_UNICODE);
        $json = is_string($encoded) ? $encoded : '';
    }
    $st = db()->prepare(
        'INSERT INTO audit_log (user_id, action, entity, entity_id, details, ip, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $st->execute([
        $user['id'] ?? null,
        $action,
        $entity,
        $entityId,
        $json,
        client_ip(),
        now_utc(),
    ]);
}
