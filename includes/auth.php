<?php
declare(strict_types=1);

const LOGIN_WINDOW_MINUTES = 15;
const LOGIN_MAX_ATTEMPTS = 5;

function user_count(): int
{
    $n = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    return (int) $n;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $st = db()->prepare('SELECT id, username, role, locale, theme FROM users WHERE id = ?');
    $st->execute([(int) $_SESSION['user_id']]);
    $row = $st->fetch();
    return $row ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect(admin_url('index.php'));
    }
    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo t('error.forbidden');
        exit;
    }
    return $user;
}

function admin_url(string $page = 'index.php'): string
{
    return url_path('admin/' . ltrim($page, '/'));
}

function login_is_locked(string $username): bool
{
    $since = gmdate('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    $st = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND username = ? AND attempted_at >= ?'
    );
    $st->execute([client_ip(), strtolower($username), $since]);
    return (int) $st->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function login_record_failure(string $username): void
{
    $st = db()->prepare(
        'INSERT INTO login_attempts (ip, username, attempted_at) VALUES (?, ?, ?)'
    );
    $st->execute([client_ip(), strtolower($username), now_utc()]);
}

function login_clear_failures(string $username): void
{
    $st = db()->prepare('DELETE FROM login_attempts WHERE ip = ? AND username = ?');
    $st->execute([client_ip(), strtolower($username)]);
}

function login_prune(): void
{
    $since = gmdate('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    $st = db()->prepare('DELETE FROM login_attempts WHERE attempted_at < ?');
    $st->execute([$since]);
}

function attempt_login(string $username, string $password): bool
{
    login_prune();
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }
    if (login_is_locked($username)) {
        return false;
    }
    $st = db()->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
    $st->execute([$username]);
    $user = $st->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        login_record_failure($username);
        return false;
    }
    login_clear_failures($username);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    audit_write('auth.login', 'user', (string) $user['id']);
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?: '/',
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}

function create_user(string $username, string $password, string $role, string $locale = 'id'): int
{
    $username = trim($username);
    if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
        throw new InvalidArgumentException(t('error.username_format'));
    }
    if (strlen($password) < 10) {
        throw new InvalidArgumentException(t('error.password_short'));
    }
    if (!in_array($role, ['admin', 'editor'], true)) {
        throw new InvalidArgumentException(t('error.invalid_role'));
    }
    if (!in_array($locale, ['id', 'en'], true)) {
        $locale = 'id';
    }
    $now = now_utc();
    $st = db()->prepare(
        'INSERT INTO users (username, password_hash, role, locale, theme, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    try {
        $st->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $role,
            $locale,
            'dark',
            $now,
            $now,
        ]);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            throw new InvalidArgumentException(t('error.username_taken'));
        }
        throw $e;
    }
    return (int) db()->lastInsertId();
}

function admin_count(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
}

function ensure_default_admin(bool $resetPassword = false): void
{
    $username = 'admin';
    $password = 'password123';
    $st = db()->prepare('SELECT id FROM users WHERE username = ?');
    $st->execute([$username]);
    $existing = $st->fetch();
    $now = now_utc();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($existing) {
        if ($resetPassword) {
            db()->prepare('UPDATE users SET password_hash = ?, role = ?, updated_at = ? WHERE id = ?')
                ->execute([$hash, 'admin', $now, (int) $existing['id']]);
            db()->prepare('DELETE FROM login_attempts WHERE username = ?')->execute([strtolower($username)]);
        }
        return;
    }
    db()->prepare(
        'INSERT INTO users (username, password_hash, role, locale, theme, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$username, $hash, 'admin', 'id', 'dark', $now, $now]);
    db()->prepare('DELETE FROM login_attempts WHERE username = ?')->execute([strtolower($username)]);
}
