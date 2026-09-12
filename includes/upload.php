<?php
declare(strict_types=1);

function store_uploaded_image(array $file, string $prefix = 'img'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $tmp = $file['tmp_name'] ?? '';
    if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        throw new RuntimeException(t('error.upload_size'));
    }
    $info = @getimagesize($tmp);
    if ($info === false) {
        throw new RuntimeException(t('error.upload_type'));
    }
    $mime = $info['mime'] ?? '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($map[$mime])) {
        throw new RuntimeException(t('error.upload_type'));
    }
    $dir = app_config('uploads_path');
    if (!is_string($dir) || $dir === '') {
        throw new RuntimeException(t('error.upload_failed'));
    }
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $name = $prefix . '-' . bin2hex(random_bytes(8)) . '.' . $map[$mime];
    $dest = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $urlBase = app_config('uploads_url') ?: 'uploads';
    return trim((string) $urlBase, '/') . '/' . $name;
}
