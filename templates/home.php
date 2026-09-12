<?php
declare(strict_types=1);

$layoutFile = APP_ROOT . '/templates/layouts/' . public_layout() . '.php';
if (!is_file($layoutFile)) {
    $layoutFile = APP_ROOT . '/templates/layouts/magazine.php';
}
include $layoutFile;
