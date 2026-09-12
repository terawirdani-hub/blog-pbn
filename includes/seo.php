<?php
declare(strict_types=1);

function seo_absolute_url(string $pathOrUrl): string
{
    $pathOrUrl = trim($pathOrUrl);
    if ($pathOrUrl === '') {
        return url_path();
    }
    if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
        return $pathOrUrl;
    }
    return url_path(ltrim($pathOrUrl, '/'));
}

function seo_datetime(?string $utc): string
{
    if ($utc === null || $utc === '') {
        return gmdate('Y-m-d\TH:i:s\Z');
    }
    $ts = strtotime($utc . ' UTC');
    if ($ts === false) {
        return gmdate('Y-m-d\TH:i:s\Z');
    }
    return gmdate('Y-m-d\TH:i:s\Z', $ts);
}

function seo_robots(bool $index): string
{
    $verb = $index ? 'index' : 'noindex';
    return $verb . ', follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
}

function seo_default_image(): string
{
    $og = setting('og_default_image_path');
    if ($og !== '') {
        return seo_absolute_url($og);
    }
    $logo = setting('logo_path');
    if ($logo !== '') {
        return seo_absolute_url($logo);
    }
    $st = db()->query("SELECT featured_image, og_image FROM posts WHERE status = 'published' AND (featured_image != '' OR og_image != '') ORDER BY datetime(published_at) DESC LIMIT 1");
    $row = $st->fetch();
    if (is_array($row)) {
        $img = $row['og_image'] !== '' ? $row['og_image'] : $row['featured_image'];
        if ($img !== '') {
            return seo_absolute_url($img);
        }
    }
    return '';
}

function seo_post_image(array $post): string
{
    if (($post['og_image'] ?? '') !== '') {
        return seo_absolute_url((string) $post['og_image']);
    }
    if (($post['featured_image'] ?? '') !== '') {
        return seo_absolute_url((string) $post['featured_image']);
    }
    return seo_default_image();
}

function seo_schema_type(array $post): string
{
    $type = (string) ($post['schema_type'] ?? 'NewsArticle');
    return $type === 'BlogPosting' ? 'BlogPosting' : 'NewsArticle';
}

function seo_verification_token(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (preg_match('/\bcontent\s*=\s*["\']([^"\']+)["\']/i', $raw, $m)) {
        $raw = $m[1];
    }
    $clean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $raw) ?? '';
    return substr($clean, 0, 128);
}

function seo_home_document_title(int $page = 1): string
{
    $custom = trim(setting('home_meta_title'));
    $site = setting('site_name');
    $tagline = trim(setting('site_tagline'));
    $base = $custom !== '' ? $custom : ($tagline !== '' ? $site . ' - ' . $tagline : $site);
    if ($page > 1) {
        return $base . ' — ' . t('ui.pagination') . ' ' . $page;
    }
    return $base;
}

function seo_home_description(): string
{
    $custom = trim(setting('home_meta_description'));
    if ($custom !== '') {
        return $custom;
    }
    return trim(setting('site_tagline'));
}

function seo_json(array $data): string
{
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    return is_string($json) ? $json : '{}';
}

function seo_organization(): array
{
    $org = [
        '@type' => 'Organization',
        '@id' => url_path() . '#organization',
        'name' => setting('site_name'),
        'url' => url_path(),
    ];
    $logo = setting('logo_path');
    if ($logo !== '') {
        $org['logo'] = [
            '@type' => 'ImageObject',
            'url' => seo_absolute_url($logo),
        ];
    }
    $same = array_values(social_links());
    if ($same) {
        $org['sameAs'] = $same;
    }
    return $org;
}

function seo_website_graph(): array
{
    $siteName = setting('site_name');
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => url_path() . '#website',
                'url' => url_path(),
                'name' => $siteName,
                'description' => seo_home_description() !== '' ? seo_home_description() : setting('site_tagline'),
                'inLanguage' => locale() === 'en' ? 'en' : 'id',
                'publisher' => ['@id' => url_path() . '#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => url_path() . '?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            seo_organization(),
        ],
    ];
}

function seo_article_graph(array $post, string $canonical, string $headline, string $description, string $image): array
{
    $authorName = author_display_name($post['author_username'] ?? null);
    $type = seo_schema_type($post);
        $images = [];
        if ($image !== '') {
            $images[] = $image;
        }
        if (!empty($post['featured_image'])) {
            $images[] = seo_absolute_url((string) $post['featured_image']);
        }
        $images = array_values(array_unique($images));
    $article = [
        '@type' => $type,
        '@id' => $canonical . '#article',
        'headline' => $headline,
        'description' => $description,
        'datePublished' => seo_datetime($post['published_at'] ?? null),
        'dateModified' => seo_datetime($post['updated_at'] ?? ($post['published_at'] ?? null)),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonical,
        ],
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
        ],
        'publisher' => seo_organization(),
        'inLanguage' => locale() === 'en' ? 'en' : 'id',
    ];
    if ($images) {
        $article['image'] = $images;
    }
    if (!empty($post['focus_keyword'])) {
        $article['keywords'] = (string) $post['focus_keyword'];
    } elseif (!empty($post['seo_keywords'])) {
        $article['keywords'] = (string) $post['seo_keywords'];
    }
    $crumbs = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => setting('site_name'),
            'item' => url_path(),
        ],
    ];
    $pos = 2;
    if (!empty($post['category_name']) && !empty($post['category_slug'])) {
        $crumbs[] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => (string) $post['category_name'],
            'item' => url_path('category/' . $post['category_slug']),
        ];
        $pos++;
    }
    $crumbs[] = [
        '@type' => 'ListItem',
        'position' => $pos,
        'name' => (string) $post['title'],
        'item' => $canonical,
    ];
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            $article,
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $crumbs,
            ],
            seo_organization(),
        ],
    ];
}

function seo_context_home(?array $category, int $page, string $query = ''): array
{
    $site = setting('site_name');
    $title = seo_home_document_title(1);
    $description = seo_home_description();
    $keywords = trim(setting('home_meta_keywords'));
    $canonical = url_path();
    if (is_array($category)) {
        $title = $category['name'] . ' — ' . $site;
        $description = (string) $category['name'];
        $canonical = url_path('category/' . $category['slug']);
        if ($page > 1) {
            $canonical = url_path('category/' . $category['slug'] . '/page/' . $page);
        }
    } elseif ($query !== '') {
        $title = t('public.search') . ': ' . $query . ' — ' . $site;
        $canonical = url_path() . '?q=' . rawurlencode($query);
        if ($page > 1) {
            $canonical = url_path('page/' . $page) . '?q=' . rawurlencode($query);
        }
    } elseif ($page > 1) {
        $canonical = url_path('page/' . $page);
        $title = seo_home_document_title($page);
    }
    return [
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'canonical' => $canonical,
        'robots' => seo_robots(true),
        'og_type' => 'website',
        'og_image' => seo_default_image(),
        'jsonld' => seo_json(seo_website_graph()),
    ];
}

function seo_context_post(array $post): array
{
    $site = setting('site_name');
    $headline = ($post['seo_title'] ?? '') !== '' ? (string) $post['seo_title'] : (string) $post['title'];
    $description = ($post['seo_description'] ?? '') !== '' ? (string) $post['seo_description'] : (string) $post['excerpt'];
    if ($description === '') {
        $plain = trim(html_entity_decode(strip_tags((string) $post['content']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $description = function_exists('mb_substr') ? mb_substr($plain, 0, 160) : substr($plain, 0, 160);
    }
    $self = url_path((string) $post['slug']);
    $canonical = ($post['canonical_url'] ?? '') !== '' ? seo_absolute_url((string) $post['canonical_url']) : $self;
    $index = (int) ($post['robots_index'] ?? 1) === 1;
    $image = seo_post_image($post);
    return [
        'title' => $headline . ($headline !== $site ? ' — ' . $site : ''),
        'description' => $description,
        'canonical' => $canonical,
        'robots' => seo_robots($index),
        'og_type' => 'article',
        'og_image' => $image,
        'keywords' => trim((string) (($post['focus_keyword'] ?? '') !== '' ? $post['focus_keyword'] : ($post['seo_keywords'] ?? ''))),
        'jsonld' => seo_json(seo_article_graph($post, $canonical, $headline, $description, $image)),
    ];
}

function seo_context_404(): array
{
    return [
        'title' => t('error.not_found') . ' — ' . setting('site_name'),
        'description' => t('public.404'),
        'canonical' => seo_absolute_url(ltrim(request_path(), '/')),
        'robots' => seo_robots(false),
        'og_type' => 'website',
        'og_image' => seo_default_image(),
        'jsonld' => seo_json(seo_website_graph()),
    ];
}

function seo_print_head(array $seo): void
{
    $site = setting('site_name');
    $title = (string) ($seo['title'] ?? $site);
    $desc = (string) ($seo['description'] ?? '');
    $canonical = (string) ($seo['canonical'] ?? url_path());
    $robots = (string) ($seo['robots'] ?? seo_robots(true));
    $ogType = (string) ($seo['og_type'] ?? 'website');
    $image = (string) ($seo['og_image'] ?? '');
    $keywords = trim((string) ($seo['keywords'] ?? setting('home_meta_keywords')));
    $locale = locale() === 'en' ? 'en_US' : 'id_ID';
    echo '<title>' . h($title) . "</title>\n";
    if ($desc !== '') {
        echo '<meta name="description" content="' . h($desc) . "\">\n";
    }
    if ($keywords !== '') {
        echo '<meta name="keywords" content="' . h($keywords) . "\">\n";
    }
    $google = setting('google_site_verification');
    if ($google !== '') {
        echo '<meta name="google-site-verification" content="' . h($google) . "\">\n";
    }
    $bing = setting('bing_site_verification');
    if ($bing !== '') {
        echo '<meta name="msvalidate.01" content="' . h($bing) . "\">\n";
    }
    echo '<meta name="robots" content="' . h($robots) . "\">\n";
    echo '<link rel="canonical" href="' . h($canonical) . "\">\n";
    echo '<link rel="alternate" type="application/rss+xml" title="' . h($site) . '" href="' . h(url_path('rss.xml')) . "\">\n";
    echo '<meta property="og:title" content="' . h($title) . "\">\n";
    echo '<meta property="og:description" content="' . h($desc) . "\">\n";
    echo '<meta property="og:url" content="' . h($canonical) . "\">\n";
    echo '<meta property="og:site_name" content="' . h($site) . "\">\n";
    echo '<meta property="og:type" content="' . h($ogType) . "\">\n";
    echo '<meta property="og:locale" content="' . h($locale) . "\">\n";
    if ($image !== '') {
        echo '<meta property="og:image" content="' . h($image) . "\">\n";
        echo "<meta property=\"og:image:width\" content=\"1200\">\n";
        echo "<meta property=\"og:image:height\" content=\"630\">\n";
    }
    echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo '<meta name="twitter:title" content="' . h($title) . "\">\n";
    echo '<meta name="twitter:description" content="' . h($desc) . "\">\n";
    if ($image !== '') {
        echo '<meta name="twitter:image" content="' . h($image) . "\">\n";
    }
    if (!empty($seo['jsonld'])) {
        echo '<script type="application/ld+json">' . $seo['jsonld'] . "</script>\n";
    }
}

function seo_xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function seo_output_sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $rows = db()->query(
        "SELECT slug, title, featured_image, og_image, updated_at, published_at
         FROM posts WHERE status = 'published'
         ORDER BY datetime(published_at) DESC, id DESC"
    )->fetchAll();
    $cats = db()->query('SELECT slug, updated_at FROM categories ORDER BY name ASC')->fetchAll();
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    echo '<url><loc>' . seo_xml_escape(url_path()) . '</loc><lastmod>' . seo_xml_escape(gmdate('Y-m-d\TH:i:s\Z')) . "</lastmod></url>\n";
    foreach ($cats as $cat) {
        $last = $cat['updated_at'] ?? '';
        echo '<url><loc>' . seo_xml_escape(url_path('category/' . $cat['slug'])) . '</loc>';
        if ($last) {
            echo '<lastmod>' . seo_xml_escape(seo_datetime((string) $last)) . '</lastmod>';
        }
        echo "</url>\n";
    }
    foreach ($rows as $row) {
        $loc = url_path($row['slug']);
        $last = $row['updated_at'] ?: $row['published_at'];
        $img = $row['og_image'] !== '' ? $row['og_image'] : $row['featured_image'];
        echo '<url><loc>' . seo_xml_escape($loc) . '</loc>';
        if ($last) {
            echo '<lastmod>' . seo_xml_escape(seo_datetime((string) $last)) . '</lastmod>';
        }
        if ($img !== '') {
            echo '<image:image><image:loc>' . seo_xml_escape(seo_absolute_url($img)) . '</image:loc>';
            echo '<image:title>' . seo_xml_escape((string) $row['title']) . '</image:title></image:image>';
        }
        echo "</url>\n";
    }
    echo '</urlset>';
}

function seo_output_rss(): void
{
    header('Content-Type: application/rss+xml; charset=utf-8');
    $site = setting('site_name');
    $tagline = setting('site_tagline');
    $rows = db()->query(
        post_list_sql() . " WHERE p.status = 'published' ORDER BY datetime(p.published_at) DESC, p.id DESC LIMIT 50"
    )->fetchAll();
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
    echo "<channel>\n";
    echo '<title>' . seo_xml_escape($site) . "</title>\n";
    echo '<link>' . seo_xml_escape(url_path()) . "</link>\n";
    echo '<description>' . seo_xml_escape($tagline !== '' ? $tagline : $site) . "</description>\n";
    echo '<language>' . seo_xml_escape(locale() === 'en' ? 'en' : 'id') . "</language>\n";
    echo '<lastBuildDate>' . seo_xml_escape(gmdate(DATE_RSS)) . "</lastBuildDate>\n";
    echo '<atom:link href="' . seo_xml_escape(url_path('rss.xml')) . "\" rel=\"self\" type=\"application/rss+xml\"/>\n";
    foreach ($rows as $row) {
        $link = url_path($row['slug']);
        $desc = $row['seo_description'] !== '' ? $row['seo_description'] : $row['excerpt'];
        $pub = $row['published_at'] ? gmdate(DATE_RSS, strtotime($row['published_at'] . ' UTC') ?: time()) : gmdate(DATE_RSS);
        $author = author_display_name($row['author_username'] ?? null);
        echo "<item>\n";
        echo '<title>' . seo_xml_escape((string) $row['title']) . "</title>\n";
        echo '<link>' . seo_xml_escape($link) . "</link>\n";
        echo '<guid isPermaLink="true">' . seo_xml_escape($link) . "</guid>\n";
        echo '<pubDate>' . seo_xml_escape($pub) . "</pubDate>\n";
        echo '<dc:creator>' . seo_xml_escape($author) . "</dc:creator>\n";
        if ($desc !== '') {
            echo '<description>' . seo_xml_escape($desc) . "</description>\n";
        }
        echo '<content:encoded><![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', sanitize_post_html((string) $row['content'])) . "]]></content:encoded>\n";
        echo "</item>\n";
    }
    echo "</channel>\n</rss>";
}
