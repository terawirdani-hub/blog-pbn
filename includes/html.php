<?php
declare(strict_types=1);

function sanitize_post_html(string $html): string
{
    $html = preg_replace('#<(script|iframe|object|embed|link|meta|form)[^>]*>.*?</\1>#is', '', $html) ?? '';
    $html = preg_replace('#<(script|iframe|object|embed|link|meta|form)[^>]*/>#is', '', $html) ?? '';
    $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';
    return $html;
}

function is_reserved_public_slug(string $slug): bool
{
    return in_array($slug, ['admin', 'page', 'category', 'uploads', 'assets', 'sitemap.xml', 'robots.txt', 'rss.xml', 'feed'], true);
}

function unique_post_slug(PDO $pdo, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    if (is_reserved_public_slug($slug)) {
        $slug = 'post-' . $slug;
    }
    $candidate = $slug;
    for ($i = 1; $i <= 50; $i++) {
        if ($ignoreId === null) {
            $st = $pdo->prepare('SELECT id FROM posts WHERE slug = ?');
            $st->execute([$candidate]);
        } else {
            $st = $pdo->prepare('SELECT id FROM posts WHERE slug = ? AND id != ?');
            $st->execute([$candidate, $ignoreId]);
        }
        if (!$st->fetch()) {
            return $candidate;
        }
        $candidate = $slug . '-' . ($i + 1);
    }
    return $slug . '-' . bin2hex(random_bytes(4));
}

function insert_post_with_slug(PDO $pdo, array $fields): int
{
    $sql = 'INSERT INTO posts (
        title, slug, excerpt, content, featured_image, status,
        seo_title, seo_description, seo_keywords, canonical_url, og_image,
        robots_index, published_at, author_id, created_at, updated_at, category_id,
        focus_keyword, schema_type
    ) VALUES (
        :title, :slug, :excerpt, :content, :featured_image, :status,
        :seo_title, :seo_description, :seo_keywords, :canonical_url, :og_image,
        :robots_index, :published_at, :author_id, :created_at, :updated_at, :category_id,
        :focus_keyword, :schema_type
    )';
    if (!array_key_exists('category_id', $fields)) {
        $fields['category_id'] = null;
    }
    if (!array_key_exists('focus_keyword', $fields)) {
        $fields['focus_keyword'] = $fields['seo_keywords'] ?? '';
    }
    if (!array_key_exists('schema_type', $fields)) {
        $fields['schema_type'] = 'NewsArticle';
    }
    $st = $pdo->prepare($sql);
    $slug = $fields['slug'];
    for ($i = 0; $i < 20; $i++) {
        try {
            $fields['slug'] = $slug;
            $st->execute($fields);
            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            if (!str_contains($e->getMessage(), 'UNIQUE')) {
                throw $e;
            }
            $slug = slugify((string) $fields['title']) . '-' . ($i + 2);
        }
    }
    throw new RuntimeException(t('error.slug_conflict'));
}
