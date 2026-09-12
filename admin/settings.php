<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$user = require_role('admin');

$tab = (string) ($_GET['tab'] ?? 'general');
$allowedTabs = ['general', 'appearance', 'author', 'ads', 'tracking', 'seo'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'general';
}

function apply_branding_from_post(int $uid, array &$changed): void
{
    $logo = setting('logo_path');
    $favicon = setting('favicon_path');
    if (!empty($_POST['remove_logo'])) {
        delete_local_upload($logo);
        $logo = '';
    }
    if (!empty($_POST['remove_favicon'])) {
        delete_local_upload($favicon);
        $favicon = '';
    }
    if (!empty($_FILES['logo']) && is_array($_FILES['logo'])) {
        $stored = store_uploaded_branding($_FILES['logo'], 'logo');
        if ($stored !== '') {
            if ($logo !== '') {
                delete_local_upload($logo);
            }
            $logo = $stored;
        }
    }
    if (!empty($_FILES['favicon']) && is_array($_FILES['favicon'])) {
        $stored = store_uploaded_branding($_FILES['favicon'], 'favicon');
        if ($stored !== '') {
            if ($favicon !== '') {
                delete_local_upload($favicon);
            }
            $favicon = $stored;
        }
    }
    foreach (['logo_path' => $logo, 'favicon_path' => $favicon] as $k => $v) {
        if (setting($k) !== $v) {
            $changed[] = $k;
        }
        setting_set($k, $v, $uid);
    }
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
            apply_branding_from_post($uid, $changed);
        } elseif ($tab === 'appearance') {
            $publicTheme = ($_POST['public_theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
            $map = resolve_template_from_post($_POST);
            $map['public_theme'] = $publicTheme;
            foreach ($map as $k => $v) {
                if (setting($k) !== $v) {
                    $changed[] = $k;
                }
                setting_set($k, $v, $uid);
            }
            apply_branding_from_post($uid, $changed);
        } elseif ($tab === 'author') {
            $avatar = setting('author_avatar_path');
            if (!empty($_POST['remove_author_avatar'])) {
                delete_local_upload($avatar);
                $avatar = '';
            }
            if (!empty($_FILES['author_avatar']) && is_array($_FILES['author_avatar'])) {
                $stored = store_uploaded_image($_FILES['author_avatar'], 'author');
                if ($stored !== '') {
                    if ($avatar !== '') {
                        delete_local_upload($avatar);
                    }
                    $avatar = $stored;
                }
            }
            $socialKeys = ['social_twitter', 'social_github', 'social_linkedin', 'social_instagram', 'social_facebook'];
            $map = [
                'author_name' => trim((string) ($_POST['author_name'] ?? '')),
                'author_bio' => trim((string) ($_POST['author_bio'] ?? '')),
                'author_avatar_path' => $avatar,
            ];
            foreach ($socialKeys as $k) {
                $url = trim((string) ($_POST[$k] ?? ''));
                if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
                    $url = '';
                }
                $map[$k] = $url;
            }
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
        $error = $e instanceof RuntimeException ? $e->getMessage() : t('error.generic');
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
    <?= tab_link('author', $tab) ?>
    <?= tab_link('ads', $tab) ?>
    <?= tab_link('tracking', $tab) ?>
    <?= tab_link('seo', $tab) ?>
</nav>
<?php if ($error): ?><p class="flash flash-error"><?= h($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="tab" value="<?= h($tab) ?>">
    <?php if ($tab === 'general'): ?>
        <div class="settings-grid">
            <section class="card">
                <h2><?= h(t('settings.card.identity')) ?></h2>
                <div class="field"><label><?= field_label('settings.site_name', 'site_name') ?></label>
                    <input type="text" name="site_name" required value="<?= h(setting('site_name')) ?>"></div>
                <div class="field"><label><?= field_label('settings.site_tagline', 'site_tagline') ?></label>
                    <input type="text" name="site_tagline" value="<?= h(setting('site_tagline')) ?>"></div>
                <div class="field"><label><?= field_label('settings.site_locale', 'site_locale') ?></label>
                    <select name="site_locale">
                        <option value="id" <?= setting('site_locale') === 'id' ? 'selected' : '' ?>>Indonesia</option>
                        <option value="en" <?= setting('site_locale') === 'en' ? 'selected' : '' ?>>English</option>
                    </select></div>
                <?php media_uploader('logo', setting('logo_path'), 'settings.logo', 'logo'); ?>
                <?php media_uploader('favicon', setting('favicon_path'), 'settings.favicon', 'favicon', true); ?>
            </section>
            <section class="card">
                <h2><?= h(t('settings.card.content')) ?></h2>
                <div class="field"><label><?= field_label('settings.homepage_intro', 'homepage_intro') ?></label>
                    <textarea name="homepage_intro"><?= h(setting('homepage_intro')) ?></textarea></div>
                <div class="field"><label><?= field_label('settings.posts_per_page', 'posts_per_page') ?></label>
                    <input type="number" name="posts_per_page" min="5" max="50" value="<?= h(setting('posts_per_page', '10')) ?>"></div>
                <div class="field"><label><?= field_label('settings.footer_text', 'footer_text') ?></label>
                    <input type="text" name="footer_text" value="<?= h(setting('footer_text')) ?>"></div>
                <div class="field"><label><?= field_label('settings.comments', 'comments') ?></label>
                    <input type="checkbox" name="comments_enabled" value="1" <?= setting('comments_enabled') === '1' ? 'checked' : '' ?> disabled>
                    <span class="muted"><?= h(t('settings.comments')) ?></span></div>
            </section>
        </div>
    <?php elseif ($tab === 'appearance'): ?>
        <div class="settings-grid">
            <section class="card" style="grid-column: 1 / -1">
                <h2><?= h(t('settings.card.templates')) ?></h2>
                <div class="field"><label><?= field_label('settings.template_preset', 'template_preset') ?></label>
                    <select name="template_preset" id="template-preset">
                        <?php foreach (theme_presets() as $id => $preset): ?>
                            <option value="<?= h($id) ?>" <?= current_template_preset_id() === $id ? 'selected' : '' ?>
                                    data-layout="<?= h($preset['layout']) ?>" data-palette="<?= h($preset['palette']) ?>">
                                <?= h(t('settings.preset_item', [
                                    'n' => $id,
                                    'layout' => t('settings.layout.' . $preset['layout']),
                                    'palette' => t('settings.palette.' . $preset['palette']),
                                ])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="muted"><?= h(t('settings.layout_help')) ?></p>
                <div class="tpl-grid" data-layout-grid>
                    <?php foreach (theme_layouts() as $key => $meta): ?>
                        <label class="tpl-opt <?= public_layout() === $key ? 'is-on' : '' ?>">
                            <input type="radio" name="homepage_layout" value="<?= h($key) ?>" <?= public_layout() === $key ? 'checked' : '' ?>>
                            <strong><?= h(t($meta['label'])) ?></strong>
                            <span><?= h(t($meta['hint'])) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="muted" style="margin-top:1rem"><?= h(t('settings.palette_help')) ?></p>
                <div class="palette-grid" data-palette-grid>
                    <?php foreach (theme_palettes() as $key => $swatch): ?>
                        <label class="palette-opt <?= public_palette() === $key ? 'is-on' : '' ?>">
                            <input type="radio" name="color_palette" value="<?= h($key) ?>" <?= public_palette() === $key ? 'checked' : '' ?>>
                            <span class="palette-bar" style="background: linear-gradient(90deg, <?= h($swatch['accent']) ?>, <?= h($swatch['primary']) ?>, <?= h($swatch['soft']) ?>);"></span>
                            <?= h(t('settings.palette.' . $key)) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="field"><label><?= field_label('settings.public_theme', 'public_theme') ?></label>
                    <select name="public_theme">
                        <option value="light" <?= setting('public_theme') === 'light' ? 'selected' : '' ?>><?= h(t('ui.theme.light')) ?></option>
                        <option value="dark" <?= setting('public_theme') === 'dark' ? 'selected' : '' ?>><?= h(t('ui.theme.dark')) ?></option>
                    </select></div>
            </section>
            <section class="card">
                <h2><?= h(t('settings.card.identity')) ?></h2>
                <?php media_uploader('logo', setting('logo_path'), 'settings.logo', 'logo'); ?>
                <?php media_uploader('favicon', setting('favicon_path'), 'settings.favicon', 'favicon', true); ?>
            </section>
        </div>
    <?php elseif ($tab === 'author'): ?>
        <section class="card">
            <div class="field"><label><?= field_label('settings.author_name', 'author_name') ?></label>
                <input type="text" name="author_name" value="<?= h(setting('author_name')) ?>"></div>
            <div class="field"><label><?= field_label('settings.author_bio', 'author_bio') ?></label>
                <textarea name="author_bio"><?= h(setting('author_bio')) ?></textarea></div>
            <?php media_uploader('author_avatar', setting('author_avatar_path'), 'settings.author_avatar', 'author_avatar', true); ?>
            <div class="field"><label><?= field_label('settings.social_twitter', 'social_twitter') ?></label>
                <input type="url" name="social_twitter" value="<?= h(setting('social_twitter')) ?>"></div>
            <div class="field"><label><?= field_label('settings.social_github', 'social_github') ?></label>
                <input type="url" name="social_github" value="<?= h(setting('social_github')) ?>"></div>
            <div class="field"><label><?= field_label('settings.social_linkedin', 'social_linkedin') ?></label>
                <input type="url" name="social_linkedin" value="<?= h(setting('social_linkedin')) ?>"></div>
            <div class="field"><label><?= field_label('settings.social_instagram', 'social_instagram') ?></label>
                <input type="url" name="social_instagram" value="<?= h(setting('social_instagram')) ?>"></div>
            <div class="field"><label><?= field_label('settings.social_facebook', 'social_facebook') ?></label>
                <input type="url" name="social_facebook" value="<?= h(setting('social_facebook')) ?>"></div>
        </section>
    <?php elseif ($tab === 'ads'): ?>
        <section class="card">
            <div class="field"><label><?= field_label('settings.ad_header', 'ad_header') ?></label>
                <textarea name="ad_header_html"><?= h(setting('ad_header_html')) ?></textarea></div>
            <div class="field"><label><?= field_label('settings.ad_sidebar', 'ad_sidebar') ?></label>
                <textarea name="ad_sidebar_html"><?= h(setting('ad_sidebar_html')) ?></textarea></div>
            <div class="field"><label><?= field_label('settings.ad_in_article', 'ad_in_article') ?></label>
                <textarea name="ad_in_article_html"><?= h(setting('ad_in_article_html')) ?></textarea></div>
            <div class="field"><label><?= field_label('settings.ad_footer', 'ad_footer') ?></label>
                <textarea name="ad_footer_html"><?= h(setting('ad_footer_html')) ?></textarea></div>
        </section>
    <?php elseif ($tab === 'tracking'): ?>
        <section class="card">
            <p class="muted"><?= h(t('settings.masked_secret')) ?></p>
            <div class="field"><label><?= field_label('settings.tracking_head', 'tracking_head') ?></label>
                <textarea name="tracking_head_html" placeholder="••••••••"></textarea></div>
            <div class="field"><label><?= field_label('settings.tracking_body', 'tracking_body') ?></label>
                <textarea name="tracking_body_html" placeholder="••••••••"></textarea></div>
        </section>
    <?php else: ?>
        <section class="card">
            <h2><?= h(t('settings.card.seo')) ?></h2>
            <p class="muted"><?= h(t('settings.seo_feeds')) ?></p>
            <p><a href="<?= h(url_path('sitemap.xml')) ?>" target="_blank" rel="noopener">sitemap.xml</a>
               · <a href="<?= h(url_path('rss.xml')) ?>" target="_blank" rel="noopener">rss.xml</a></p>
            <div class="field"><label><?= field_label('settings.robots', 'robots_txt') ?></label>
                <textarea name="robots_txt" style="min-height:200px"><?= h(setting('robots_txt')) ?></textarea></div>
        </section>
    <?php endif; ?>
    <p><button class="btn" type="submit"><?= h(t('ui.save')) ?></button></p>
</form>
<?php admin_layout_end();