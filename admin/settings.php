<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$user = require_role('admin');

$tab = (string) ($_GET['tab'] ?? 'general');
$allowedTabs = ['general', 'appearance', 'ads', 'tracking', 'seo'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'general';
}

$error = '';
if (is_post()) {
    csrf_verify();
    $tab = (string) ($_POST['tab'] ?? 'general');
    if (!in_array($tab, $allowedTabs, true)) {
        $tab = 'general';
    }
    try {
        $changed = [];
        $uid = (int) $user['id'];
        if ($tab === 'general') {
            $map = [
                'site_name' => trim((string) ($_POST['site_name'] ?? '')),
                'site_tagline' => trim((string) ($_POST['site_tagline'] ?? '')),
                'site_locale' => ($_POST['site_locale'] ?? 'id') === 'en' ? 'en' : 'id',
                'homepage_intro' => (string) ($_POST['homepage_intro'] ?? ''),
                'footer_text' => trim((string) ($_POST['footer_text'] ?? '')),
                'posts_per_page' => (string) max(5, min(50, (int) ($_POST['posts_per_page'] ?? 10))),
                'comments_enabled' => isset($_POST['comments_enabled']) ? '1' : '0',
            ];
            foreach ($map as $k => $v) {
                if (setting($k) !== $v) {
                    $changed[] = $k;
                }
                setting_set($k, $v, $uid);
            }
        } elseif ($tab === 'appearance') {
            $primary = (string) ($_POST['primary_color'] ?? '#1f6feb');
            $accent = (string) ($_POST['accent_color'] ?? '#238636');
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary)) {
                $primary = '#1f6feb';
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
                $accent = '#238636';
            }
            $style = ($_POST['template_style'] ?? 'classic') === 'magazine' ? 'magazine' : 'classic';
            $publicTheme = ($_POST['public_theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
            $logo = setting('logo_path');
            $favicon = setting('favicon_path');
            if (!empty($_FILES['logo']) && is_array($_FILES['logo'])) {
                $stored = store_uploaded_image($_FILES['logo'], 'logo');
                if ($stored !== '') {
                    $logo = $stored;
                }
            }
            if (!empty($_FILES['favicon']) && is_array($_FILES['favicon'])) {
                $stored = store_uploaded_image($_FILES['favicon'], 'fav');
                if ($stored !== '') {
                    $favicon = $stored;
                }
            }
            $map = [
                'primary_color' => $primary,
                'accent_color' => $accent,
                'template_style' => $style,
                'public_theme' => $publicTheme,
                'logo_path' => $logo,
                'favicon_path' => $favicon,
            ];
            foreach ($map as $k => $v) {
                if (setting($k) !== $v) {
                    $changed[] = $k;
                }
                setting_set($k, $v, $uid);
            }
        } elseif ($tab === 'ads') {
            foreach (['ad_header_html', 'ad_sidebar_html', 'ad_in_article_html', 'ad_footer_html'] as $k) {
                $v = (string) ($_POST[$k] ?? '');
                if (setting($k) !== $v) {
                    $changed[] = $k;
                }
                setting_set($k, $v, $uid);
            }
        } elseif ($tab === 'tracking') {
            foreach (['tracking_head_html', 'tracking_body_html'] as $k) {
                $posted = (string) ($_POST[$k] ?? '');
                if (trim($posted) === '') {
                    continue;
                }
                if (setting($k) !== $posted) {
                    $changed[] = $k;
                }
                setting_set($k, $posted, $uid);
            }
        } else {
            $robots = (string) ($_POST['robots_txt'] ?? '');
            if (setting('robots_txt') !== $robots) {
                $changed[] = 'robots_txt';
            }
            setting_set('robots_txt', $robots, $uid);
        }
        if ($changed) {
            audit_write('settings.update', 'settings', $tab, ['keys' => $changed]);
        }
        flash_set('success', t('flash.saved'));
        redirect(admin_url('settings.php?tab=' . rawurlencode($tab)));
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = t('error.generic');
    }
}

function tab_link(string $tab, string $current): string
{
    $cls = $tab === $current ? 'is-on' : '';
    return '<a class="' . $cls . '" href="' . h(admin_url('settings.php?tab=' . $tab)) . '">' . h(t('settings.tab.' . $tab)) . '</a>';
}

admin_layout_start(t('settings.title'), 'settings');
?>
<h1><?= h(t('settings.title')) ?></h1>
<nav class="tabs">
    <?= tab_link('general', $tab) ?>
    <?= tab_link('appearance', $tab) ?>
    <?= tab_link('ads', $tab) ?>
    <?= tab_link('tracking', $tab) ?>
    <?= tab_link('seo', $tab) ?>
</nav>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form class="card" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="tab" value="<?= h($tab) ?>">
    <?php if ($tab === 'general'): ?>
        <div class="field"><label><?= field_label('settings.site_name', 'site_name') ?></label>
            <input type="text" name="site_name" required value="<?= h(setting('site_name')) ?>"></div>
        <div class="field"><label><?= field_label('settings.site_tagline', 'site_tagline') ?></label>
            <input type="text" name="site_tagline" value="<?= h(setting('site_tagline')) ?>"></div>
        <div class="field"><label><?= field_label('settings.site_locale', 'site_locale') ?></label>
            <select name="site_locale">
                <option value="id" <?= setting('site_locale') === 'id' ? 'selected' : '' ?>>Indonesia</option>
                <option value="en" <?= setting('site_locale') === 'en' ? 'selected' : '' ?>>English</option>
            </select></div>
        <div class="field"><label><?= field_label('settings.homepage_intro', 'homepage_intro') ?></label>
            <textarea name="homepage_intro"><?= h(setting('homepage_intro')) ?></textarea></div>
        <div class="field"><label><?= field_label('settings.posts_per_page', 'posts_per_page') ?></label>
            <input type="number" name="posts_per_page" min="5" max="50" value="<?= h(setting('posts_per_page', '10')) ?>"></div>
        <div class="field"><label><?= field_label('settings.footer_text', 'footer_text') ?></label>
            <input type="text" name="footer_text" value="<?= h(setting('footer_text')) ?>"></div>
        <div class="field"><label><?= field_label('settings.comments', 'comments') ?></label>
            <input type="checkbox" name="comments_enabled" value="1" <?= setting('comments_enabled') === '1' ? 'checked' : '' ?> disabled>
            <span class="muted"><?= h(t('settings.comments')) ?></span></div>
    <?php elseif ($tab === 'appearance'): ?>
        <div class="field"><label><?= field_label('settings.template_style', 'template_style') ?></label>
            <select name="template_style">
                <option value="classic" <?= setting('template_style') === 'classic' ? 'selected' : '' ?>><?= h(t('settings.style.classic')) ?></option>
                <option value="magazine" <?= setting('template_style') === 'magazine' ? 'selected' : '' ?>><?= h(t('settings.style.magazine')) ?></option>
            </select></div>
        <div class="field"><label><?= field_label('settings.public_theme', 'public_theme') ?></label>
            <select name="public_theme">
                <option value="light" <?= setting('public_theme') === 'light' ? 'selected' : '' ?>><?= h(t('ui.theme.light')) ?></option>
                <option value="dark" <?= setting('public_theme') === 'dark' ? 'selected' : '' ?>><?= h(t('ui.theme.dark')) ?></option>
            </select></div>
        <div class="field"><label><?= field_label('settings.primary_color', 'primary_color') ?></label>
            <input type="color" name="primary_color" value="<?= h(setting('primary_color', '#1f6feb')) ?>"></div>
        <div class="field"><label><?= field_label('settings.accent_color', 'accent_color') ?></label>
            <input type="color" name="accent_color" value="<?= h(setting('accent_color', '#238636')) ?>"></div>
        <div class="field"><label><?= field_label('settings.logo', 'logo') ?></label>
            <?php if (setting('logo_path')): ?><p class="muted"><?= h(setting('logo_path')) ?></p><?php endif; ?>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp"></div>
        <div class="field"><label><?= field_label('settings.favicon', 'favicon') ?></label>
            <?php if (setting('favicon_path')): ?><p class="muted"><?= h(setting('favicon_path')) ?></p><?php endif; ?>
            <input type="file" name="favicon" accept="image/jpeg,image/png,image/gif,image/webp"></div>
    <?php elseif ($tab === 'ads'): ?>
        <div class="field"><label><?= field_label('settings.ad_header', 'ad_header') ?></label>
            <textarea name="ad_header_html"><?= h(setting('ad_header_html')) ?></textarea></div>
        <div class="field"><label><?= field_label('settings.ad_sidebar', 'ad_sidebar') ?></label>
            <textarea name="ad_sidebar_html"><?= h(setting('ad_sidebar_html')) ?></textarea></div>
        <div class="field"><label><?= field_label('settings.ad_in_article', 'ad_in_article') ?></label>
            <textarea name="ad_in_article_html"><?= h(setting('ad_in_article_html')) ?></textarea></div>
        <div class="field"><label><?= field_label('settings.ad_footer', 'ad_footer') ?></label>
            <textarea name="ad_footer_html"><?= h(setting('ad_footer_html')) ?></textarea></div>
    <?php elseif ($tab === 'tracking'): ?>
        <p class="muted"><?= h(t('settings.masked_secret')) ?></p>
        <div class="field"><label><?= field_label('settings.tracking_head', 'tracking_head') ?></label>
            <textarea name="tracking_head_html" placeholder="••••••••"><?= setting('tracking_head_html') !== '' ? '' : '' ?></textarea></div>
        <div class="field"><label><?= field_label('settings.tracking_body', 'tracking_body') ?></label>
            <textarea name="tracking_body_html" placeholder="••••••••"></textarea></div>
    <?php else: ?>
        <div class="field"><label><?= field_label('settings.robots', 'robots_txt') ?></label>
            <textarea name="robots_txt" style="min-height:200px"><?= h(setting('robots_txt')) ?></textarea></div>
    <?php endif; ?>
    <button class="btn" type="submit"><?= h(t('ui.save')) ?></button>
</form>
<?php admin_layout_end();