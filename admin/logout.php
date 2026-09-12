<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
if (current_user()) {
    audit_write('auth.logout', 'user', (string) current_user()['id']);
}
logout_user();
redirect(admin_url('index.php'));
