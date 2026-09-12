<?php
declare(strict_types=1);

/**
 * Ensure the default admin account exists.
 * Usage: php scripts/seed-admin.php [--reset]
 *   --reset  also sets password back to password123 if admin already exists
 */
$reset = in_array('--reset', $argv, true);
require dirname(__DIR__) . '/includes/bootstrap.php';
ensure_default_admin($reset);
fwrite(STDOUT, "Default admin is ready.\n");
fwrite(STDOUT, "Login: /admin  (also /admin/index.php)\n");
fwrite(STDOUT, "Username: admin\n");
fwrite(STDOUT, "Password: password123\n");
