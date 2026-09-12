<?php
declare(strict_types=1);

function migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            version INTEGER PRIMARY KEY,
            applied_at TEXT NOT NULL
        )'
    );

    $applied = [];
    foreach ($pdo->query('SELECT version FROM schema_migrations') as $row) {
        $applied[(int) $row['version']] = true;
    }

    foreach (schema_versions() as $version => $sql) {
        if (isset($applied[$version])) {
            continue;
        }
        $pdo->beginTransaction();
        try {
            $pdo->exec($sql);
            $st = $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (?, ?)');
            $st->execute([$version, now_utc()]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('Migration ' . $version . ' failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

function schema_versions(): array
{
    return [
        1 => <<<'SQL'
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('admin', 'editor')),
    locale TEXT NOT NULL DEFAULT 'id' CHECK (locale IN ('id', 'en')),
    theme TEXT NOT NULL DEFAULT 'dark' CHECK (theme IN ('dark', 'light')),
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE settings (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL DEFAULT '',
    is_secret INTEGER NOT NULL DEFAULT 0,
    updated_at TEXT,
    updated_by INTEGER,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    excerpt TEXT NOT NULL DEFAULT '',
    content TEXT NOT NULL DEFAULT '',
    featured_image TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published')),
    seo_title TEXT NOT NULL DEFAULT '',
    seo_description TEXT NOT NULL DEFAULT '',
    seo_keywords TEXT NOT NULL DEFAULT '',
    canonical_url TEXT NOT NULL DEFAULT '',
    og_image TEXT NOT NULL DEFAULT '',
    robots_index INTEGER NOT NULL DEFAULT 1,
    published_at TEXT,
    author_id INTEGER,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE blogroll (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    rel TEXT NOT NULL DEFAULT 'noopener noreferrer',
    target TEXT NOT NULL DEFAULT '_blank' CHECK (target IN ('_blank', '_self')),
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    entity TEXT NOT NULL DEFAULT '',
    entity_id TEXT NOT NULL DEFAULT '',
    details TEXT NOT NULL DEFAULT '',
    ip TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    username TEXT NOT NULL,
    attempted_at TEXT NOT NULL
);
CREATE INDEX idx_posts_status_published ON posts (status, published_at);
CREATE INDEX idx_posts_slug ON posts (slug);
CREATE INDEX idx_blogroll_active_sort ON blogroll (is_active, sort_order);
CREATE INDEX idx_audit_created ON audit_log (created_at);
CREATE INDEX idx_login_attempts_lookup ON login_attempts (ip, username, attempted_at);
SQL
        ,
    ];
}
