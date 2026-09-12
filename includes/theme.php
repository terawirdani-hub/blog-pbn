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
                'layout' => $layout,
                'palette' => $palette,
            ];
            $n++;
        }
    }
    return $out;
}

function public_layout(): string
{
    $allowed = array_keys(theme_layouts());
    $value = setting('homepage_layout');
    if (in_array($value, $allowed, true)) {
        return $value;
    }
    return setting('template_style') === 'classic' ? 'newspaper' : 'magazine';
}

function public_palette(): string
{
    $allowed = array_keys(theme_palettes());
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
    $layout = public_layout();
    $palette = public_palette();
    foreach (theme_presets() as $id => $preset) {
        if ($preset['layout'] === $layout && $preset['palette'] === $palette) {
            return $id;
        }
    }
    return '01';
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
    $colors = theme_palettes()[$palette];
    return [
        'homepage_layout' => $layout,
        'color_palette' => $palette,
        'primary_color' => $colors['primary'],
        'accent_color' => $colors['accent'],
        'template_style' => $layout === 'newspaper' ? 'classic' : 'magazine',
    ];
}

function resolve_template_from_post(array $post): array
{
    if (!empty($post['homepage_layout']) && !empty($post['color_palette'])) {
        return apply_template_choice((string) $post['homepage_layout'], (string) $post['color_palette']);
    }
    $presetId = preg_replace('/\D+/', '', (string) ($post['template_preset'] ?? ''));
    $presets = theme_presets();
    if ($presetId !== '' && isset($presets[$presetId])) {
        return apply_template_choice($presets[$presetId]['layout'], $presets[$presetId]['palette']);
    }
    return apply_template_choice('magazine', 'slate');
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
