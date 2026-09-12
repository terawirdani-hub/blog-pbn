<?php
declare(strict_types=1);

function theme_layouts(): array
{
    return [
        'magazine' => [
            'label' => 'settings.layout.magazine',
            'hint' => 'settings.layout.magazine_hint',
            'sidebar' => true,
        ],
        'tech' => [
            'label' => 'settings.layout.tech',
            'hint' => 'settings.layout.tech_hint',
            'sidebar' => false,
        ],
        'bento' => [
            'label' => 'settings.layout.bento',
            'hint' => 'settings.layout.bento_hint',
            'sidebar' => false,
        ],
        'newspaper' => [
            'label' => 'settings.layout.newspaper',
            'hint' => 'settings.layout.newspaper_hint',
            'sidebar' => false,
        ],
        'masonry' => [
            'label' => 'settings.layout.masonry',
            'hint' => 'settings.layout.masonry_hint',
            'sidebar' => true,
        ],
    ];
}

function theme_palettes(): array
{
    return [
        'slate' => ['primary' => '#2563eb', 'accent' => '#0f172a', 'soft' => '#38bdf8'],
        'crimson' => ['primary' => '#e11d48', 'accent' => '#881337', 'soft' => '#fb7185'],
        'emerald' => ['primary' => '#059669', 'accent' => '#064e3b', 'soft' => '#34d399'],
        'violet' => ['primary' => '#7c3aed', 'accent' => '#4c1d95', 'soft' => '#c4b5fd'],
        'amber' => ['primary' => '#d97706', 'accent' => '#78350f', 'soft' => '#fbbf24'],
        'mono' => ['primary' => '#18181b', 'accent' => '#3f3f46', 'soft' => '#a1a1aa'],
    ];
}

/**
 * Full catalog of the 30 public presets (5 layouts x 6 palettes).
 * Keys are the stored ids; `code` is the form value (t01 .. t30).
 */
function theme_presets(): array
{
    return [
        '01' => ['id' => '01', 'code' => 't01', 'layout' => 'magazine', 'palette' => 'slate'],
        '02' => ['id' => '02', 'code' => 't02', 'layout' => 'magazine', 'palette' => 'crimson'],
        '03' => ['id' => '03', 'code' => 't03', 'layout' => 'magazine', 'palette' => 'emerald'],
        '04' => ['id' => '04', 'code' => 't04', 'layout' => 'magazine', 'palette' => 'violet'],
        '05' => ['id' => '05', 'code' => 't05', 'layout' => 'magazine', 'palette' => 'amber'],
        '06' => ['id' => '06', 'code' => 't06', 'layout' => 'magazine', 'palette' => 'mono'],
        '07' => ['id' => '07', 'code' => 't07', 'layout' => 'tech', 'palette' => 'slate'],
        '08' => ['id' => '08', 'code' => 't08', 'layout' => 'tech', 'palette' => 'crimson'],
        '09' => ['id' => '09', 'code' => 't09', 'layout' => 'tech', 'palette' => 'emerald'],
        '10' => ['id' => '10', 'code' => 't10', 'layout' => 'tech', 'palette' => 'violet'],
        '11' => ['id' => '11', 'code' => 't11', 'layout' => 'tech', 'palette' => 'amber'],
        '12' => ['id' => '12', 'code' => 't12', 'layout' => 'tech', 'palette' => 'mono'],
        '13' => ['id' => '13', 'code' => 't13', 'layout' => 'bento', 'palette' => 'slate'],
        '14' => ['id' => '14', 'code' => 't14', 'layout' => 'bento', 'palette' => 'crimson'],
        '15' => ['id' => '15', 'code' => 't15', 'layout' => 'bento', 'palette' => 'emerald'],
        '16' => ['id' => '16', 'code' => 't16', 'layout' => 'bento', 'palette' => 'violet'],
        '17' => ['id' => '17', 'code' => 't17', 'layout' => 'bento', 'palette' => 'amber'],
        '18' => ['id' => '18', 'code' => 't18', 'layout' => 'bento', 'palette' => 'mono'],
        '19' => ['id' => '19', 'code' => 't19', 'layout' => 'newspaper', 'palette' => 'slate'],
        '20' => ['id' => '20', 'code' => 't20', 'layout' => 'newspaper', 'palette' => 'crimson'],
        '21' => ['id' => '21', 'code' => 't21', 'layout' => 'newspaper', 'palette' => 'emerald'],
        '22' => ['id' => '22', 'code' => 't22', 'layout' => 'newspaper', 'palette' => 'violet'],
        '23' => ['id' => '23', 'code' => 't23', 'layout' => 'newspaper', 'palette' => 'amber'],
        '24' => ['id' => '24', 'code' => 't24', 'layout' => 'newspaper', 'palette' => 'mono'],
        '25' => ['id' => '25', 'code' => 't25', 'layout' => 'masonry', 'palette' => 'slate'],
        '26' => ['id' => '26', 'code' => 't26', 'layout' => 'masonry', 'palette' => 'crimson'],
        '27' => ['id' => '27', 'code' => 't27', 'layout' => 'masonry', 'palette' => 'emerald'],
        '28' => ['id' => '28', 'code' => 't28', 'layout' => 'masonry', 'palette' => 'violet'],
        '29' => ['id' => '29', 'code' => 't29', 'layout' => 'masonry', 'palette' => 'amber'],
        '30' => ['id' => '30', 'code' => 't30', 'layout' => 'masonry', 'palette' => 'mono'],
    ];
}

/**
 * Presets grouped per layout, in catalog order, for the admin picker.
 */
function theme_presets_by_layout(): array
{
    $groups = [];
    foreach (theme_presets() as $preset) {
        $groups[$preset['layout']][] = $preset;
    }
    return $groups;
}

function normalize_template_id(string $value): string
{
    $n = (int) preg_replace('/\D+/', '', $value);
    if ($n < 1 || $n > 30) {
        return '';
    }
    return sprintf('%02d', $n);
}

function active_template_id(): string
{
    $id = normalize_template_id(setting('active_template'));
    if ($id !== '') {
        return $id;
    }
    $layout = setting('homepage_layout');
    $palette = setting('color_palette');
    foreach (theme_presets() as $preset) {
        if ($preset['layout'] === $layout && $preset['palette'] === $palette) {
            return $preset['id'];
        }
    }
    return '01';
}

function active_template_preset(): array
{
    $presets = theme_presets();
    $id = active_template_id();
    return $presets[$id] ?? $presets['01'];
}

function public_layout(): string
{
    $preset = active_template_preset();
    $allowed = array_keys(theme_layouts());
    if (in_array($preset['layout'], $allowed, true)) {
        return $preset['layout'];
    }
    $value = setting('homepage_layout');
    if (in_array($value, $allowed, true)) {
        return $value;
    }
    return setting('template_style') === 'classic' ? 'newspaper' : 'magazine';
}

function public_palette(): string
{
    $preset = active_template_preset();
    $allowed = array_keys(theme_palettes());
    if (in_array($preset['palette'], $allowed, true)) {
        return $preset['palette'];
    }
    $value = setting('color_palette');
    if (in_array($value, $allowed, true)) {
        return $value;
    }
    return 'slate';
}

function public_palette_colors(): array
{
    $palettes = theme_palettes();
    $key = public_palette();
    return $palettes[$key] ?? $palettes['slate'];
}

function public_layout_has_sidebar(?string $layout = null): bool
{
    $layout = $layout ?? public_layout();
    $all = theme_layouts();
    return !empty($all[$layout]['sidebar']);
}

function current_template_preset_id(): string
{
    return active_template_id();
}

function normalize_homepage_layout(string $value): string
{
    $legacy = [
        'classic' => 'newspaper',
        'magazine' => 'magazine',
    ];
    if (isset($legacy[$value])) {
        $value = $legacy[$value];
    }
    $allowed = array_keys(theme_layouts());
    return in_array($value, $allowed, true) ? $value : 'magazine';
}

function normalize_color_palette(string $value): string
{
    $allowed = array_keys(theme_palettes());
    return in_array($value, $allowed, true) ? $value : 'slate';
}

function apply_template_choice(string $layout, string $palette): array
{
    $layout = normalize_homepage_layout($layout);
    $palette = normalize_color_palette($palette);
    $id = '01';
    foreach (theme_presets() as $preset) {
        if ($preset['layout'] === $layout && $preset['palette'] === $palette) {
            $id = $preset['id'];
            break;
        }
    }
    return apply_template_id($id);
}

function apply_template_id(string $id): array
{
    $id = normalize_template_id($id);
    $presets = theme_presets();
    if ($id === '' || !isset($presets[$id])) {
        $id = '01';
    }
    $preset = $presets[$id];
    $layout = $preset['layout'];
    $palette = $preset['palette'];
    $colors = theme_palettes()[$palette];
    return [
        'active_template' => $id,
        'homepage_layout' => $layout,
        'color_palette' => $palette,
        'primary_color' => $colors['primary'],
        'accent_color' => $colors['accent'],
        'template_style' => $layout === 'newspaper' ? 'classic' : 'magazine',
    ];
}

function resolve_template_from_post(array $post): array
{
    $fromSelect = normalize_template_id((string) ($post['template_preset'] ?? $post['active_template'] ?? ''));
    if ($fromSelect !== '') {
        return apply_template_id($fromSelect);
    }
    if (!empty($post['homepage_layout']) && !empty($post['color_palette'])) {
        return apply_template_choice((string) $post['homepage_layout'], (string) $post['color_palette']);
    }
    return apply_template_id('01');
}

function public_shell_class(): string
{
    $layout = public_layout();
    if ($layout === 'tech') {
        return 'mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:py-14';
    }
    if (public_layout_has_sidebar($layout)) {
        return 'mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-8 sm:px-6 lg:grid-cols-10 lg:gap-12 lg:px-8 lg:py-12';
    }
    return 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12';
}

function public_main_class(): string
{
    return public_layout_has_sidebar() ? 'min-w-0 lg:col-span-7' : 'min-w-0';
}
