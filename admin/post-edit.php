<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$user = require_role('admin', 'editor');

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$post = null;
if ($id > 0) {
    $st = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch() ?: null;
    if (!$post) {
        http_response_code(404);
        echo t('error.not_found');
        exit;
    }
}

$error = '';
if (is_post()) {
    csrf_verify();
    $title = trim((string) ($_POST['title'] ?? ''));
    $slugInput = trim((string) ($_POST['slug'] ?? ''));
    $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
    $content = sanitize_post_html((string) ($_POST['content'] ?? ''));
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
    $seoDesc = trim((string) ($_POST['seo_description'] ?? ''));
    $seoKeys = trim((string) ($_POST['seo_keywords'] ?? ''));
    $canonical = trim((string) ($_POST['canonical_url'] ?? ''));
    $robots = isset($_POST['robots_index']) ? 1 : 0;
    $image = $post['featured_image'] ?? '';
    $og = trim((string) ($_POST['og_image'] ?? ($post['og_image'] ?? '')));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    if ($categoryId > 0) {
        $cst = db()->prepare('SELECT id FROM categories WHERE id = ?');
        $cst->execute([$categoryId]);
        if (!$cst->fetch()) {
            $categoryId = 0;
        }
    }

    if ($title === '') {
        $error = t('ui.required');
    } else {
        try {
            if (!empty($_FILES['featured_image']) && is_array($_FILES['featured_image'])) {
                $stored = store_uploaded_image($_FILES['featured_image'], 'post');
                if ($stored !== '') {
                    $image = $stored;
                }
            }
            if (!empty($_FILES['og_image_file']) && is_array($_FILES['og_image_file'])) {
                $storedOg = store_uploaded_image($_FILES['og_image_file'], 'og');
                if ($storedOg !== '') {
                    $og = $storedOg;
                }
            }
            $now = now_utc();
            $baseSlug = $slugInput !== '' ? $slugInput : $title;
            $publishedAt = $post['published_at'] ?? null;
            if ($status === 'published' && ($publishedAt === null || $publishedAt === '')) {
                $publishedAt = $now;
            }
            if ($status === 'draft') {
                // keep original published_at if it existed; still hidden from public
                $publishedAt = $post['published_at'] ?? $publishedAt;
            }

            if ($post) {
                $slug = unique_post_slug(db(), $baseSlug, $id);
                $st = db()->prepare(
                    'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, featured_image=?, status=?,
                     seo_title=?, seo_description=?, seo_keywords=?, canonical_url=?, og_image=?,
                     robots_index=?, published_at=?, updated_at=?, category_id=? WHERE id=?'
                );
                $st->execute([
                    $title, $slug, $excerpt, $content, $image, $status,
                    $seoTitle, $seoDesc, $seoKeys, $canonical, $og,
                    $robots, $publishedAt, $now, $categoryId > 0 ? $categoryId : null, $id,
                ]);
                $action = $status === 'published' ? 'post.publish' : 'post.update';
                audit_write($action, 'post', (string) $id, ['title' => $title, 'status' => $status]);
            } else {
                $slug = unique_post_slug(db(), $baseSlug);
                $newId = insert_post_with_slug(db(), [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'featured_image' => $image,
                    'status' => $status,
                    'seo_title' => $seoTitle,
                    'seo_description' => $seoDesc,
                    'seo_keywords' => $seoKeys,
                    'canonical_url' => $canonical,
                    'og_image' => $og,
                    'robots_index' => $robots,
                    'published_at' => $status === 'published' ? $publishedAt : null,
                    'author_id' => (int) $user['id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'category_id' => $categoryId > 0 ? $categoryId : null,
                ]);
                audit_write($status === 'published' ? 'post.publish' : 'post.create', 'post', (string) $newId, ['title' => $title]);
            }
            flash_set('success', t('flash.saved'));
            redirect(admin_url('posts.php'));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $error = t('error.generic');
        }
    }
    $post = array_merge($post ?? [], [
        'title' => $title,
        'slug' => $slugInput,
        'excerpt' => $excerpt,
        'content' => $content,
        'status' => $status,
        'seo_title' => $seoTitle,
        'seo_description' => $seoDesc,
        'seo_keywords' => $seoKeys,
        'canonical_url' => $canonical,
        'og_image' => $og,
        'robots_index' => $robots,
        'featured_image' => $image,
        'category_id' => $categoryId,
    ]);
}

$pageTitle = $post ? t('posts.edit') : t('posts.new');
$categories = db()->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
admin_layout_start($pageTitle, 'posts');
?>
<h1><?= h($pageTitle) ?></h1>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($id): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>
    <div class="field">
        <label><?= field_label('posts.field.title', 'post_title') ?></label>
        <input type="text" name="title" required value="<?= h($post['title'] ?? '') ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.slug', 'post_slug') ?></label>
        <input type="text" name="slug" value="<?= h($post['slug'] ?? '') ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.excerpt', 'post_excerpt') ?></label>
        <textarea name="excerpt"><?= h($post['excerpt'] ?? '') ?></textarea>
    </div>
    <div class="field full">
        <label><?= field_label('posts.field.content', 'post_content') ?></label>
        <textarea name="content" style="min-height:260px"><?= h($post['content'] ?? '') ?></textarea>
    </div>
    <div class="field">
        <label><?= field_label('posts.field.image', 'post_image') ?></label>
        <?php if (!empty($post['featured_image'])): ?>
            <p class="muted"><?= h($post['featured_image']) ?></p>
        <?php endif; ?>
        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.status', 'post_status') ?></label>
        <select name="status">
            <option value="draft" <?= (($post['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>><?= h(t('posts.status.draft')) ?></option>
            <option value="published" <?= (($post['status'] ?? '') === 'published') ? 'selected' : '' ?>><?= h(t('posts.status.published')) ?></option>
        </select>
    </div>
    <div class="field">
        <label><?= field_label('posts.field.category', 'category') ?></label>
        <select name="category_id">
            <option value="0">—</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= (int) ($post['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label><?= field_label('posts.field.seo_title', 'seo_title') ?></label>
        <input type="text" name="seo_title" value="<?= h($post['seo_title'] ?? '') ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.seo_description', 'seo_description') ?></label>
        <textarea name="seo_description"><?= h($post['seo_description'] ?? '') ?></textarea>
    </div>
    <div class="field">
        <label><?= field_label('posts.field.seo_keywords', 'seo_keywords') ?></label>
        <input type="text" name="seo_keywords" value="<?= h($post['seo_keywords'] ?? '') ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.canonical', 'canonical') ?></label>
        <input type="url" name="canonical_url" value="<?= h($post['canonical_url'] ?? '') ?>">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.og_image', 'og_image') ?></label>
        <input type="text" name="og_image" value="<?= h($post['og_image'] ?? '') ?>">
        <input type="file" name="og_image_file" accept="image/jpeg,image/png,image/gif,image/webp">
    </div>
    <div class="field">
        <label><?= field_label('posts.field.robots', 'post_robots') ?></label>
        <input type="checkbox" name="robots_index" value="1" <?= ($post === null || (int) ($post['robots_index'] ?? 1) === 1) ? 'checked' : '' ?>>
    </div>
    <button class="btn" type="submit"><?= h(t('ui.save')) ?></button>
    <a class="btn ghost" href="<?= h(admin_url('posts.php')) ?>"><?= h(t('ui.cancel')) ?></a>
</form>
<?php admin_layout_end();