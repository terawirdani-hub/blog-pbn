<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
admin_gate();

if (user_count() > 0) {
    redirect(admin_url('index.php'));
}

$error = '';
if (is_post()) {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $locale = (string) ($_POST['locale'] ?? 'id');
    try {
        $id = create_user($username, $password, 'admin', $locale);
        persist_locale_cookie($locale);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        audit_write('auth.setup', 'user', (string) $id);
        $now = now_utc();
        $slug = unique_post_slug(db(), 'hello-world');
        insert_post_with_slug(db(), [
            'title' => 'Hello World',
            'slug' => $slug,
            'excerpt' => 'Welcome to Turbo PBN.',
            'content' => '<p>This is your first post. Edit or replace it from Admin → Posts.</p>',
            'featured_image' => '',
            'status' => 'published',
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'canonical_url' => '',
            'og_image' => '',
            'robots_index' => 1,
            'published_at' => $now,
            'author_id' => $id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        flash_set('success', t('flash.created'));
        redirect(admin_url('index.php'));
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = t('error.generic');
    }
}

admin_layout_start(t('auth.setup_title'), '');
?>
<div class="auth-wrap card">
    <h1><?= h(t('auth.setup_title')) ?></h1>
    <p class="muted"><?= h(t('auth.setup_help')) ?></p>
    <?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="field">
            <label><?= field_label('auth.username', 'username') ?></label>
            <input type="text" name="username" required maxlength="32" autocomplete="username">
        </div>
        <div class="field">
            <label><?= field_label('auth.password', 'password') ?></label>
            <input type="password" name="password" required minlength="10" autocomplete="new-password">
        </div>
        <div class="field">
            <label><?= field_label('users.field.locale', 'user_locale') ?></label>
            <select name="locale">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
            </select>
        </div>
        <button class="btn" type="submit"><?= h(t('auth.create_admin')) ?></button>
    </form>
</div>
<?php admin_layout_end();