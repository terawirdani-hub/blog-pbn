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

function theme_presets(): array
{
    $out = [];
    $n = 1;
    foreach (array_keys(theme_layouts()) as $layout) {
        foreach (array_keys(theme_palettes()) as $palette) {
            $id = sprintf('%02d', $n);
            $out[$id] = [
                'id' => $id,
                'n' => $n,
                'layout' => $layout,
                'palette' => $palette,
            ];
            $n++;
        }
    }
    return $out;
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
    foreach (theme_presets() as $presetId => $preset) {
        if ($preset['layout'] === $layout && $preset['palette'] === $palette) {
            return $presetId;
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
    foreach (theme_presets() as $presetId => $preset) {
        if ($preset['layout'] === $layout && $preset['palette'] === $palette) {
            $id = $presetId;
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
