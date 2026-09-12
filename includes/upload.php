<?php
declare(strict_types=1);

function store_uploaded_image(array $file, string $prefix = 'img'): string
{
    return store_uploaded_media($file, $prefix, false);
}

function store_uploaded_branding(array $file, string $prefix = 'brand'): string
{
    return store_uploaded_media($file, $prefix, true);
}

function store_uploaded_media(array $file, string $prefix, bool $allowBranding): string
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
    $client = strtolower((string) ($file['name'] ?? ''));
    $extGuess = pathinfo($client, PATHINFO_EXTENSION);
    $ext = detect_upload_extension($tmp, $extGuess, $allowBranding);
    if ($ext === null) {
        throw new RuntimeException(t('error.upload_type'));
    }
    if ($ext === 'svg') {
        $raw = (string) file_get_contents($tmp);
        if ($raw === '' || preg_match('/<script|onload\s*=|onerror\s*=|javascript:|data:text\/html/i', $raw)) {
            throw new RuntimeException(t('error.upload_type'));
        }
    }
    $dir = app_config('uploads_path');
    if (!is_string($dir) || $dir === '') {
        throw new RuntimeException(t('error.upload_failed'));
    }
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $name = $prefix . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException(t('error.upload_failed'));
    }
    $urlBase = app_config('uploads_url') ?: 'uploads';
    return trim((string) $urlBase, '/') . '/' . $name;
}

function detect_upload_extension(string $tmp, string $extGuess, bool $allowBranding): ?string
{
    $mime = '';
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = (string) finfo_file($f, $tmp);
            finfo_close($f);
        }
    }
    $info = @getimagesize($tmp);
    $imgMime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (isset($map[$imgMime])) {
        return $map[$imgMime];
    }
    if (isset($map[$mime])) {
        return $map[$mime];
    }
    if (!$allowBranding) {
        return null;
    }
    $head = (string) file_get_contents($tmp, false, null, 0, 256);
    $isIco = str_starts_with($head, "\x00\x00\x01\x00") || str_starts_with($head, "\x00\x00\x02\x00");
    $icoMime = in_array($mime, ['image/x-icon', 'image/vnd.microsoft.icon', 'application/octet-stream'], true);
    if ($isIco && ($extGuess === 'ico' || $icoMime)) {
        return 'ico';
    }
    $isSvg = str_contains($head, '<svg') || $mime === 'image/svg+xml' || $mime === 'text/xml';
    if ($isSvg && ($extGuess === 'svg' || $mime === 'image/svg+xml' || str_contains($head, '<svg'))) {
        return 'svg';
    }
    return null;
}

function delete_local_upload(string $relative): void
{
    $relative = str_replace('\\', '/', $relative);
    if ($relative === '' || str_contains($relative, '..')) {
        return;
    }
    $base = trim((string) (app_config('uploads_url') ?: 'uploads'), '/');
    if (!str_starts_with($relative, $base . '/')) {
        return;
    }
    $dir = app_config('uploads_path');
    if (!is_string($dir)) {
        return;
    }
    $full = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . basename($relative);
    $root = realpath($dir);
    $resolved = realpath($full);
    if ($root && $resolved && str_starts_with($resolved, $root) && is_file($resolved)) {
        unlink($resolved);
    }
}

function favicon_type(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return match ($ext) {
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'image/png',
    };
}
