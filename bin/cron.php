<?php

define("ROOT_DIR", dirname(__DIR__));

$languageConfig = json_decode(file_get_contents(ROOT_DIR . '/config/languages.json'), true);
$categoryConfig = json_decode(file_get_contents(ROOT_DIR . '/config/categories.json'), true);

$languages = array_keys($languageConfig);

$translations = [];
foreach ($languageConfig as $lang => $strings) {
    $translations[$lang] = $strings;
    $translations[$lang]['categories'] = [];
    foreach ($categoryConfig as $slug => $catData) {
        $translations[$lang]['categories'][$slug] = $catData[$lang]['name'] ?? ucwords(str_replace('-', ' ', $slug));
    }
}

function buildHreflangHtml(string $path, string $baseUrl, array $languages): string
{
    $tags = '';
    foreach ($languages as $lang) {
        $url = $baseUrl . '/' . $lang . ($path !== '' ? '/' . $path : '');
        $tags .= '<link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($url) . '" />' . "\n    ";
    }
    $defaultUrl = $baseUrl . ($path !== '' ? '/' . $path : '/');
    $tags .= '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultUrl) . '" />';
    return $tags;
}

function buildHreflangSitemap(string $path, string $baseUrl, array $languages): string
{
    $links = '';
    foreach ($languages as $lang) {
        $url = $baseUrl . '/' . $lang . ($path !== '' ? '/' . $path : '');
        $links .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($url) . '" />' . "\n";
    }
    $defaultUrl = $baseUrl . ($path !== '' ? '/' . $path : '/');
    $links .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultUrl) . '" />';
    return $links;
}

function precompress(string $filePath): void
{
    $content = file_get_contents($filePath);
    // Gzip (max compression, built-in PHP)
    $gz = gzencode($content, 9);
    if ($gz !== false) {
        file_put_contents($filePath . '.gz', $gz);
    }
    // Brotli (max compression via CLI, skip if unavailable)
    static $hasBrotli = null;
    if ($hasBrotli === null) {
        exec('which brotli 2>/dev/null', $out, $code);
        $hasBrotli = $code === 0;
    }
    if ($hasBrotli) {
        exec('brotli -q 11 -f ' . escapeshellarg($filePath) . ' -o ' . escapeshellarg($filePath . '.br'));
    }
}

function minifyCss(string $css): string
{
    // Remove comments
    $css = preg_replace('/\/\*.*?\*\//s', '', $css);
    // Remove whitespace around selectors and properties
    $css = preg_replace('/\s*([{}:;,>~+])\s*/', '$1', $css);
    // Collapse remaining whitespace
    $css = preg_replace('/\s+/', ' ', $css);
    return trim($css);
}

function minifyJs(string $js): string
{
    // Remove multi-line comments
    $js = preg_replace('/\/\*.*?\*\//s', '', $js);
    // Remove single-line comments (not inside strings, not URLs)
    $js = preg_replace('#(^|[^:\'"])//[^\n]*#m', '$1', $js);
    // Collapse whitespace to single spaces
    $js = preg_replace('/[ \t]+/', ' ', $js);
    // Remove whitespace around newlines and collapse blank lines
    $js = preg_replace('/\s*\n\s*/', "\n", $js);
    $js = preg_replace('/\n+/', "\n", $js);
    return trim($js);
}

function minifyHtml(string $html): string
{
    // Remove HTML comments (but preserve conditional comments like <!--[if)
    $html = preg_replace('/<!--(?!\[).*?-->/s', '', $html);
    // Collapse whitespace between tags
    $html = preg_replace('/>\s+</', '> <', $html);
    // Remove leading/trailing whitespace on each line, then collapse newlines
    $html = preg_replace('/^\s+/m', '', $html);
    $html = preg_replace('/\s+$/m', '', $html);
    $html = preg_replace('/\n+/', "\n", $html);
    return trim($html);
}

function markdownToHtml(string $markdown): string
{
    $lines = explode("\n", $markdown);
    $html = '';
    $inList = false;
    $paragraph = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            if ($paragraph !== '') {
                $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
                $paragraph = '';
            }
            if ($inList) {
                $html .= "</ul>\n";
                $inList = false;
            }
            continue;
        }

        if (str_starts_with($trimmed, '## ')) {
            if ($paragraph !== '') {
                $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
                $paragraph = '';
            }
            if ($inList) {
                $html .= "</ul>\n";
                $inList = false;
            }
            $html .= '<h2>' . htmlspecialchars(substr($trimmed, 3)) . "</h2>\n";
            continue;
        }

        if (str_starts_with($trimmed, '# ')) {
            if ($paragraph !== '') {
                $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
                $paragraph = '';
            }
            if ($inList) {
                $html .= "</ul>\n";
                $inList = false;
            }
            $html .= '<h1>' . htmlspecialchars(substr($trimmed, 2)) . "</h1>\n";
            continue;
        }

        if (str_starts_with($trimmed, '- ')) {
            if ($paragraph !== '') {
                $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
                $paragraph = '';
            }
            if (!$inList) {
                $html .= "<ul>\n";
                $inList = true;
            }
            $html .= '<li>' . htmlspecialchars(substr($trimmed, 2)) . "</li>\n";
            continue;
        }

        if ($inList) {
            $html .= "</ul>\n";
            $inList = false;
        }
        if ($paragraph !== '') {
            $paragraph .= ' ' . $trimmed;
        } else {
            $paragraph = $trimmed;
        }
    }

    if ($paragraph !== '') {
        $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
    }
    if ($inList) {
        $html .= "</ul>\n";
    }

    return $html;
}

function extractTitle(string $markdown, string $fallback): string
{
    if (preg_match('/^# (.+)$/m', $markdown, $matches)) {
        return $matches[1];
    }
    return $fallback;
}

function extractDescription(string $markdown): string
{
    $lines = explode("\n", $markdown);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '- ')) {
            continue;
        }
        if (strlen($trimmed) <= 120) {
            return rtrim($trimmed);
        }
        $cut = substr($trimmed, 0, 120);
        $lastSpace = strrpos($cut, ' ');
        if ($lastSpace === false) {
            $text = $cut;
        } else {
            $text = substr($cut, 0, $lastSpace);
        }
        $text = rtrim($text);
        if (preg_match('/[.!?]$/', $text)) {
            return $text;
        }
        return $text . '...';
    }
    return '';
}

function getRelatedPosts(array $currentPost, array $allPosts, int $maxResults = 5): array
{
    $scored = [];
    $currentTags = $currentPost['tags'] ?? [];

    foreach ($allPosts as $post) {
        if ($post['slug'] === $currentPost['slug']) {
            continue;
        }
        $points = 0.0;
        if ($post['category'] === $currentPost['category']) {
            $points += 25;
        }
        $sharedTags = array_intersect($currentTags, $post['tags'] ?? []);
        $points += count($sharedTags) * 5;
        $sharedWords = array_intersect($currentPost['title_words'] ?? [], $post['title_words'] ?? []);
        $points += count($sharedWords);
        if ($points < 1) {
            continue;
        }
        $points += mt_rand(0, 1000) / 1000;
        $scored[] = ['post' => $post, 'score' => $points];
    }

    usort($scored, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return array_slice(array_column($scored, 'post'), 0, $maxResults);
}

function buildListingEntries(array $posts, string $entryTemplate, string $tagLinkTemplate, string $lang): string
{
    global $translations;
    $entries = '';
    foreach ($posts as $post) {
        $slug = $post['slug'];
        $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
        if (!is_file($mdFile)) {
            $mdFile = ROOT_DIR . '/posts/' . $slug . '/en.md';
        }
        if (!is_file($mdFile)) {
            continue;
        }
        $markdown = file_get_contents($mdFile);

        $title = extractTitle($markdown, $slug);
        $description = extractDescription($markdown);

        $tagsHtml = '';
        foreach ($post['tags'] ?? [] as $tag) {
            $tagSlug = htmlspecialchars(str_replace(' ', '-', $tag));
            $tagLink = str_replace('###LANG###', $lang, $tagLinkTemplate);
            $tagLink = str_replace('###TAG###', $tagSlug, $tagLink);
            $tagsHtml .= $tagLink;
        }

        $entry = $entryTemplate;
        $entry = str_replace('###LANG###', $lang, $entry);
        $entry = str_replace('###POST_CATEGORY###', htmlspecialchars($post['category'] . '/' . $slug), $entry);
        $entry = str_replace('###POST_TITLE###', htmlspecialchars($title), $entry);
        $entry = str_replace('###POST_DESCRIPTION###', htmlspecialchars($description), $entry);
        $entry = str_replace('###POST_MORE###', htmlspecialchars($translations[$lang]['more']), $entry);
        $entry = str_replace('###POST_TAGS###', $tagsHtml, $entry);
        $entry = str_replace('###POST_DATE###', htmlspecialchars($post['date']), $entry);

        $viewFile = ROOT_DIR . '/output/' . $post['category'] . '/' . $slug . '/viewcount.txt';
        $viewCount = is_file($viewFile) ? (int)file_get_contents($viewFile) : 0;
        $entry = str_replace('###POST_VIEWS###', (string)$viewCount, $entry);

        $entries .= $entry . "\n";
    }
    return $entries;
}

function generateFeeds(
    array $posts,
    string $outputDir,
    string $feedTitle,
    string $feedDescription,
    string $selfPath,
    string $lang,
    string $baseUrl,
    string $rssTemplate,
    string $rssEntryTemplate,
    string $atomTemplate,
    string $atomEntryTemplate
): void {
    $rssEntries = '';
    $atomEntries = '';
    $latestDate = $posts[0]['date'] ?? date('Y-m-d');

    foreach ($posts as $post) {
        $slug = $post['slug'];
        $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
        if (!is_file($mdFile)) {
            $mdFile = ROOT_DIR . '/posts/' . $slug . '/en.md';
        }
        if (!is_file($mdFile)) {
            continue;
        }
        $markdown = file_get_contents($mdFile);
        $title = extractTitle($markdown, $slug);
        $description = extractDescription($markdown);
        $link = $baseUrl . '/' . $lang . '/' . $post['category'] . '/' . $slug;

        $rssEntry = $rssEntryTemplate;
        $rssEntry = str_replace('###POST_TITLE###', htmlspecialchars($title), $rssEntry);
        $rssEntry = str_replace('###POST_LINK###', htmlspecialchars($link), $rssEntry);
        $rssEntry = str_replace('###POST_DATE###', date('r', strtotime($post['date'])), $rssEntry);
        $rssEntry = str_replace('###POST_DESCRIPTION###', htmlspecialchars($description), $rssEntry);
        $rssEntries .= $rssEntry;

        $atomEntry = $atomEntryTemplate;
        $atomEntry = str_replace('###POST_TITLE###', htmlspecialchars($title), $atomEntry);
        $atomEntry = str_replace('###POST_LINK###', htmlspecialchars($link), $atomEntry);
        $atomEntry = str_replace('###POST_DATE###', $post['date'] . 'T00:00:00+00:00', $atomEntry);
        $atomEntry = str_replace('###POST_DESCRIPTION###', htmlspecialchars($description), $atomEntry);
        $atomEntries .= $atomEntry;
    }

    $feedLink = $baseUrl . '/' . $lang . $selfPath;

    $rss = $rssTemplate;
    $rss = str_replace('###FEED_TITLE###', htmlspecialchars($feedTitle), $rss);
    $rss = str_replace('###FEED_LINK###', htmlspecialchars($feedLink), $rss);
    $rss = str_replace('###FEED_DESCRIPTION###', htmlspecialchars($feedDescription), $rss);
    $rss = str_replace('###LANG###', $lang, $rss);
    $rss = str_replace('###FEED_SELF###', htmlspecialchars($feedLink . '/feed.rss'), $rss);
    $rss = str_replace('###FEED_ENTRIES###', $rssEntries, $rss);
    file_put_contents($outputDir . '/' . $lang . '.rss', $rss);
    precompress($outputDir . '/' . $lang . '.rss');

    $atom = $atomTemplate;
    $atom = str_replace('###FEED_TITLE###', htmlspecialchars($feedTitle), $atom);
    $atom = str_replace('###FEED_LINK###', htmlspecialchars($feedLink), $atom);
    $atom = str_replace('###FEED_SELF###', htmlspecialchars($feedLink . '/feed.atom'), $atom);
    $atom = str_replace('###FEED_UPDATED###', $latestDate . 'T00:00:00+00:00', $atom);
    $atom = str_replace('###FEED_ENTRIES###', $atomEntries, $atom);
    file_put_contents($outputDir . '/' . $lang . '.atom', $atom);
    precompress($outputDir . '/' . $lang . '.atom');
}

// Load per-language templates
$mainTemplates = [];
$entryTemplates = [];
$listingTemplates = [];
$notFoundTemplates = [];
$imprintTemplates = [];
$canceledTemplates = [];
$thankYouTemplates = [];
$postDateTemplates = [];
$relatedPostsTemplates = [];
foreach ($languages as $lang) {
    $mainTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/main.html');
    $entryTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/post-listing-entry.html');
    $listingTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/post-listing.html');
    $notFoundTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/404.html');
    $imprintTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/imprint.html');
    $canceledTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/canceled.html');
    $thankYouTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/thank-you.html');
    $postDateTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/post-date.html');
    $relatedPostsTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/related-posts.html');
}
$linkListTemplates = [];
foreach ($languages as $lang) {
    $linkListTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/link-list.html');
}
$linkListEntryTemplate = file_get_contents(ROOT_DIR . '/resources/link-list-entry.html');
$relatedPostEntryTemplate = file_get_contents(ROOT_DIR . '/resources/related-post-entry.html');
$tagLinkTemplate = file_get_contents(ROOT_DIR . '/resources/tag-link.html');
$statisticsTemplate = file_get_contents(ROOT_DIR . '/resources/en/statistics.html');
$statisticsPostRowTemplate = file_get_contents(ROOT_DIR . '/resources/en/statistics-post-row.html');
$statisticsCategoryRowTemplate = file_get_contents(ROOT_DIR . '/resources/en/statistics-category-view-row.html');
$sitemapTemplate = file_get_contents(ROOT_DIR . '/resources/sitemap.xml');
$sitemapEntryTemplate = file_get_contents(ROOT_DIR . '/resources/sitemap-entry.xml');
$rssTemplate = file_get_contents(ROOT_DIR . '/resources/rss.xml');
$rssEntryTemplate = file_get_contents(ROOT_DIR . '/resources/rss-entry.xml');
$atomTemplate = file_get_contents(ROOT_DIR . '/resources/atom.xml');
$atomEntryTemplate = file_get_contents(ROOT_DIR . '/resources/atom-entry.xml');

// Aside HTML per language (shown on individual posts and static pages, not on listing pages)
$asideTemplates = [
    'en' => '<aside>
        <h2>Who am I?</h2>
        <picture>
            <source type="image/avif" srcset="/bjoern.avif">
            <source type="image/webp" srcset="/bjoern.webp">
            <img src="/bjoern.jpg" alt="Björn Büttner" title="Björn Büttner">
        </picture>
        <p>My name is Björn and I go by Idrinth online. I have been a software developer for over a decade and I\'m still spending way too much time modding games.</p>
        <p>I enjoy talking with people, so this is a way I can provide another platform next to my discord.</p>
        <p>If you need to reach out, please do so via discord and don\'t send random emails.</p>
        <a id="donate-button" href="https://www.paypal.com/donate/?hosted_button_id=YHS42UYG8QN84">
            <picture>
                <source type="image/avif" srcset="/donate-en.avif">
                <source type="image/webp" srcset="/donate-en.webp">
                <img src="/donate-en.png" alt="Donate with Paypal">
            </picture>
        </a>
    </aside>',
    'de' => '<aside>
        <h2>Wer bin ich?</h2>
        <picture>
            <source type="image/avif" srcset="/bjoern.avif">
            <source type="image/webp" srcset="/bjoern.webp">
            <img src="/bjoern.jpg" alt="Björn Büttner" title="Björn Büttner">
        </picture>
        <p>Mein Name ist Björn und im Internet bin ich als Idrinth bekannt. Ich bin seit über einem Jahrzehnt Softwareentwickler und verbringe immer noch viel zu viel Zeit mit dem Modden von Spielen.</p>
        <p>Ich unterhalte mich gerne mit Menschen, daher ist dies eine weitere Plattform neben meinem Discord.</p>
        <p>Wenn du mich erreichen möchtest, nutze bitte Discord und schicke keine zufälligen E-Mails.</p>
        <a id="donate-button" href="https://www.paypal.com/donate/?hosted_button_id=GBETUCHKXQ4B6">
            <picture>
                <source type="image/avif" srcset="/donate-de.avif">
                <source type="image/webp" srcset="/donate-de.webp">
                <img src="/donate-de.png" alt="Mit Paypal spenden">
            </picture>
        </a>
    </aside>',
    'fr' => '<aside>
        <h2>Qui suis-je ?</h2>
        <picture>
            <source type="image/avif" srcset="/bjoern.avif">
            <source type="image/webp" srcset="/bjoern.webp">
            <img src="/bjoern.jpg" alt="Björn Büttner" title="Björn Büttner">
        </picture>
        <p>Je m\'appelle Björn et je suis connu sous le nom d\'Idrinth en ligne. Je suis développeur logiciel depuis plus d\'une décennie et je passe toujours beaucoup trop de temps à modder des jeux.</p>
        <p>J\'aime discuter avec les gens, c\'est donc une autre plateforme en plus de mon Discord.</p>
        <p>Si vous avez besoin de me contacter, veuillez le faire via Discord et n\'envoyez pas d\'e-mails au hasard.</p>
        <a id="donate-button" href="https://www.paypal.com/donate/?hosted_button_id=CV8XUFS34LRXW">
            <picture>
                <source type="image/avif" srcset="/donate-fr.avif">
                <source type="image/webp" srcset="/donate-fr.webp">
                <img src="/donate-fr.png" alt="Faites un don via PayPal">
            </picture>
        </a>
    </aside>',
];

// Minify and write CSS/JS to public directory
file_put_contents(ROOT_DIR . '/public/styles.css', minifyCss(file_get_contents(ROOT_DIR . '/resources/styles.css')));
precompress(ROOT_DIR . '/public/styles.css');
file_put_contents(ROOT_DIR . '/public/scripts.js', minifyJs(file_get_contents(ROOT_DIR . '/resources/scripts.js')));
precompress(ROOT_DIR . '/public/scripts.js');
file_put_contents(ROOT_DIR . '/public/theme.js', minifyJs(file_get_contents(ROOT_DIR . '/resources/theme.js')));
precompress(ROOT_DIR . '/public/theme.js');

// Add cache-breaker query parameters to CSS/JS references in templates
$cssHash = md5_file(ROOT_DIR . '/public/styles.css');
$scriptsHash = md5_file(ROOT_DIR . '/public/scripts.js');
$themeHash = md5_file(ROOT_DIR . '/public/theme.js');
foreach ($languages as $lang) {
    $mainTemplates[$lang] = str_replace('/styles.css', '/styles.css?' . $cssHash, $mainTemplates[$lang]);
    $mainTemplates[$lang] = str_replace('/scripts.js', '/scripts.js?' . $scriptsHash, $mainTemplates[$lang]);
    $mainTemplates[$lang] = str_replace('/theme.js', '/theme.js?' . $themeHash, $mainTemplates[$lang]);
}

// Detect template changes
$templateHashFile = ROOT_DIR . '/output/.template-hash';
$allTemplateContents = implode('', $mainTemplates) . implode('', $entryTemplates) . implode('', $listingTemplates)
    . implode('', $notFoundTemplates) . implode('', $imprintTemplates) . implode('', $canceledTemplates) . implode('', $thankYouTemplates) . implode('', $postDateTemplates)
    . implode('', $relatedPostsTemplates) . $relatedPostEntryTemplate . $tagLinkTemplate
    . $statisticsTemplate . $statisticsPostRowTemplate . $statisticsCategoryRowTemplate
    . $sitemapTemplate . $sitemapEntryTemplate
    . $rssTemplate . $rssEntryTemplate . $atomTemplate . $atomEntryTemplate
    . file_get_contents(ROOT_DIR . '/config/languages.json')
    . file_get_contents(ROOT_DIR . '/config/categories.json');
$currentHash = md5($allTemplateContents);
$templatesChanged = true;
if (is_file($templateHashFile)) {
    $templatesChanged = file_get_contents($templateHashFile) !== $currentHash;
}

// Collect all posts with metadata
$posts = [];
foreach (scandir(ROOT_DIR . '/posts') as $folder) {
    if ($folder === '.' || $folder === '..') {
        continue;
    }
    $metaFile = ROOT_DIR . '/posts/' . $folder . '/meta.json';
    if (!is_file($metaFile)) {
        continue;
    }
    $meta = json_decode(file_get_contents($metaFile), true);
    $meta['slug'] = $folder;
    $titleMdFile = ROOT_DIR . '/posts/' . $folder . '/en.md';
    if (!is_file($titleMdFile)) {
        foreach ($languages as $fallbackLang) {
            $titleMdFile = ROOT_DIR . '/posts/' . $folder . '/' . $fallbackLang . '.md';
            if (is_file($titleMdFile)) {
                break;
            }
        }
    }
    $titleWords = [];
    if (is_file($titleMdFile)) {
        $title = extractTitle(file_get_contents($titleMdFile), '');
        if ($title !== '') {
            $titleWords = array_unique(array_filter(
                preg_split('/\s+/', strtolower($title)),
                fn($w) => strlen($w) > 2
            ));
        }
    }
    $meta['title_words'] = array_values($titleWords);
    if ($meta['date'] > date('Y-m-d')) {
        continue;
    }
    $posts[] = $meta;
}

// Sort by date descending
usort($posts, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

// Generate posts.json for random redirect and other consumers
$postUrls = array_map(function ($post) {
    return '/' . $post['category'] . '/' . $post['slug'];
}, $posts);
file_put_contents(ROOT_DIR . '/output/posts.json', json_encode($postUrls));
precompress(ROOT_DIR . '/output/posts.json');

// Generate individual post pages per available language
foreach ($posts as $post) {
    $slug = $post['slug'];
    $category = $post['category'];
    $outputDir = ROOT_DIR . '/output/' . $category . '/' . $slug;
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    foreach ($languages as $lang) {
        $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
        if (!is_file($mdFile)) {
            continue;
        }
        $outputFile = $outputDir . '/' . $lang . '.html';
        $metaFile = ROOT_DIR . '/posts/' . $slug . '/meta.json';
        if (!$templatesChanged && is_file($outputFile)
            && filemtime($outputFile) >= filemtime($mdFile)
            && filemtime($outputFile) >= filemtime($metaFile)
        ) {
            continue;
        }
        $markdown = file_get_contents($mdFile);
        $content = markdownToHtml($markdown);
        $title = extractTitle($markdown, $slug);
        $viewFile = $outputDir . '/viewcount.txt';
        $viewCount = is_file($viewFile) ? (int)file_get_contents($viewFile) : 0;

        $dateHtml = $postDateTemplates[$lang];
        $dateHtml = str_replace('###POST_DATE###', htmlspecialchars($post['date']), $dateHtml);
        $dateHtml = str_replace('###POST_PATH###', htmlspecialchars($category . '/' . $slug), $dateHtml);
        $dateHtml = str_replace('###POST_VIEWS###', (string)$viewCount, $dateHtml);
        $content = preg_replace('/<\/h1>\n/', "</h1>\n" . $dateHtml, $content, 1);

        $content .= '<div id="voting-target"></div>';

        if (!empty($post['links'])) {
            $linkEntries = '';
            foreach ($post['links'] as $link) {
                $entry = $linkListEntryTemplate;
                $entry = str_replace('###LINK_URL###', htmlspecialchars($link['url'] ?? ''), $entry);
                $entry = str_replace('###LINK_NAME###', htmlspecialchars($link['name'][$lang] ?? $link['name']['en'] ?? ''), $entry);
                $entry = str_replace('###LINK_DESCRIPTION###', htmlspecialchars($link['description'][$lang] ?? $link['description']['en'] ?? ''), $entry);
                $linkEntries .= $entry;
            }
            $content .= str_replace('###LINK_ENTRIES###', $linkEntries, $linkListTemplates[$lang]);
        }

        $relatedPosts = getRelatedPosts($post, $posts);
        if ($relatedPosts !== []) {
            $relatedEntries = '';
            foreach ($relatedPosts as $related) {
                $relMdFile = ROOT_DIR . '/posts/' . $related['slug'] . '/' . $lang . '.md';
                if (!is_file($relMdFile)) {
                    $relMdFile = ROOT_DIR . '/posts/' . $related['slug'] . '/en.md';
                }
                if (!is_file($relMdFile)) {
                    continue;
                }
                $relTitle = extractTitle(file_get_contents($relMdFile), $related['slug']);
                $relCategory = $translations[$lang]['categories'][$related['category']] ?? $related['category'];

                $entry = $relatedPostEntryTemplate;
                $entry = str_replace('###LANG###', $lang, $entry);
                $entry = str_replace('###POST_PATH###', htmlspecialchars($related['category'] . '/' . $related['slug']), $entry);
                $entry = str_replace('###POST_TITLE###', htmlspecialchars($relTitle), $entry);
                $entry = str_replace('###POST_CATEGORY###', htmlspecialchars($relCategory), $entry);
                $entry = str_replace('###POST_DATE###', htmlspecialchars($related['date']), $entry);
                $relatedEntries .= $entry;
            }
            $relatedHtml = str_replace('###RELATED_ENTRIES###', $relatedEntries, $relatedPostsTemplates[$lang]);
            $content .= $relatedHtml;
        }

        $description = extractDescription($markdown);
        if ($description === '') {
            $description = $translations[$lang]['default_description'];
        }

        $page = $mainTemplates[$lang];
        $page = str_replace('###PAGE_TITLE###', htmlspecialchars($title), $page);
        $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($description), $page);
        $page = str_replace('###HREFLANG###', buildHreflangHtml($category . '/' . $slug, 'https://idrinth.de', $languages), $page);
        $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/' . $category . '/' . $slug, $page);
        $page = str_replace('###META_KEYWORDS###', '<meta name="keywords" content="' . htmlspecialchars(implode(', ', $post['tags'] ?? [])) . '">', $page);
        $page = str_replace('###ASIDE###', $asideTemplates[$lang], $page);
        $page = str_replace('###CONTENT###', $content, $page);

        file_put_contents($outputFile, minifyHtml($page));
        precompress($outputFile);
    }
}

// Determine if any post source files changed (for listing pages)
$postsChanged = $templatesChanged;
if (!$postsChanged) {
    foreach ($posts as $post) {
        $slug = $post['slug'];
        $metaFile = ROOT_DIR . '/posts/' . $slug . '/meta.json';
        foreach ($languages as $lang) {
            $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
            $outputFile = ROOT_DIR . '/output/' . $post['category'] . '/' . $slug . '/' . $lang . '.html';
            if (!is_file($outputFile)
                || (is_file($mdFile) && filemtime($mdFile) > filemtime($outputFile))
                || filemtime($metaFile) > filemtime($outputFile)
            ) {
                $postsChanged = true;
                break 2;
            }
        }
    }
}

// Generate home page listing (last 9 posts) per language
$homePosts = array_slice($posts, 0, 9);
if ($postsChanged) {
    foreach ($languages as $lang) {
        $listing = buildListingEntries($homePosts, $entryTemplates[$lang], $tagLinkTemplate, $lang);

        $listingContent = str_replace('###POST_LISTING_ENTRY###', $listing, $listingTemplates[$lang]);

        $homeIntro = $translations[$lang]['home_intro'] ?? '';
        if ($homeIntro !== '') {
            $listingContent = preg_replace('/<\/h1>/', '</h1><p class="listing-intro">' . htmlspecialchars($homeIntro) . '</p>', $listingContent, 1);
        }

        $page = $mainTemplates[$lang];
        $page = str_replace('###PAGE_TITLE###', $translations[$lang]['latest_posts'], $page);
        $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations[$lang]['default_description']), $page);
        $page = str_replace('###HREFLANG###', buildHreflangHtml('', 'https://idrinth.de', $languages), $page);
        $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang, $page);
        $page = str_replace('###META_KEYWORDS###', '', $page);
        $page = str_replace('###ASIDE###', '', $page);
        $page = str_replace('###CONTENT###', $listingContent, $page);

        file_put_contents(ROOT_DIR . '/output/' . $lang . '.html', minifyHtml($page));
        precompress(ROOT_DIR . '/output/' . $lang . '.html');

        generateFeeds(
            $homePosts, ROOT_DIR . '/output', $translations[$lang]['latest_posts'],
            $translations[$lang]['default_description'], '', $lang, 'https://idrinth.de',
            $rssTemplate, $rssEntryTemplate, $atomTemplate, $atomEntryTemplate
        );
    }
}

// Generate category listing pages (last 9 per category) per language
$categories = array_fill_keys(array_keys($categoryConfig), []);
foreach ($posts as $post) {
    $cat = $post['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $post;
}

if ($postsChanged) {
    foreach ($categories as $category => $catPosts) {
        $catPosts = array_slice($catPosts, 0, 9);
        $outputDir = ROOT_DIR . '/output/' . $category;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach ($languages as $lang) {
            $categoryTitle = $translations[$lang]['categories'][$category] ?? ucwords(str_replace('-', ' ', $category));
            $listing = buildListingEntries($catPosts, $entryTemplates[$lang], $tagLinkTemplate, $lang);

            $headingText = $translations[$lang]['latest_posts_in'] . ' ' . htmlspecialchars($categoryTitle);
            $listingContent = str_replace('###POST_LISTING_ENTRY###', $listing, $listingTemplates[$lang]);
            $listingContent = preg_replace('/<h1>[^<]+<\/h1>/', '<h1>' . $headingText . '</h1>', $listingContent, 1);

            $categoryIntro = $categoryConfig[$category][$lang]['description'] ?? '';
            if ($categoryIntro !== '') {
                $listingContent = preg_replace('/<\/h1>/', '</h1><p class="listing-intro">' . htmlspecialchars($categoryIntro) . '</p>', $listingContent, 1);
            }

            $categoryDescription = sprintf($translations[$lang]['category_description'], $categoryTitle);

            $page = $mainTemplates[$lang];
            $page = str_replace('###PAGE_TITLE###', htmlspecialchars($categoryTitle), $page);
            $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($categoryDescription), $page);
            $page = str_replace('###HREFLANG###', buildHreflangHtml($category, 'https://idrinth.de', $languages), $page);
            $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/' . $category, $page);
            $page = str_replace('###META_KEYWORDS###', '', $page);
            $page = str_replace('###ASIDE###', '', $page);
            $page = str_replace('###CONTENT###', $listingContent, $page);

            file_put_contents($outputDir . '/' . $lang . '.html', minifyHtml($page));
            precompress($outputDir . '/' . $lang . '.html');

            generateFeeds(
                $catPosts, $outputDir, $translations[$lang]['latest_posts_in'] . ' ' . $categoryTitle,
                $categoryDescription, '/' . $category, $lang, 'https://idrinth.de',
                $rssTemplate, $rssEntryTemplate, $atomTemplate, $atomEntryTemplate
            );
        }
    }
}

// Generate tag listing pages (last 9 per tag) per language
$tags = [];
foreach ($posts as $post) {
    foreach ($post['tags'] ?? [] as $tag) {
        if (!isset($tags[$tag])) {
            $tags[$tag] = [];
        }
        $tags[$tag][] = $post;
    }
}

if ($postsChanged) {
    foreach ($tags as $tag => $tagPosts) {
        $tagPosts = array_slice($tagPosts, 0, 9);
        $tagDirName = str_replace(' ', '-', $tag);
        $outputDir = ROOT_DIR . '/output/tag/' . $tagDirName;
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach ($languages as $lang) {
            $listing = buildListingEntries($tagPosts, $entryTemplates[$lang], $tagLinkTemplate, $lang);

            $headingText = $translations[$lang]['latest_posts_with_tag'] . ' ' . htmlspecialchars($tag);
            $listingContent = str_replace('###POST_LISTING_ENTRY###', $listing, $listingTemplates[$lang]);
            $listingContent = preg_replace('/<h1>[^<]+<\/h1>/', '<h1>' . $headingText . '</h1>', $listingContent, 1);

            $tagIntroTemplate = $translations[$lang]['tag_intro'] ?? '';
            if ($tagIntroTemplate !== '') {
                $tagIntroText = htmlspecialchars(sprintf($tagIntroTemplate, $tag));
                $listingContent = preg_replace('/<\/h1>/', '</h1><p class="listing-intro">' . $tagIntroText . '</p>', $listingContent, 1);
            }

            $tagDescription = sprintf($translations[$lang]['tag_description'], $tag);

            $page = $mainTemplates[$lang];
            $page = str_replace('###PAGE_TITLE###', htmlspecialchars($tag), $page);
            $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($tagDescription), $page);
            $page = str_replace('###HREFLANG###', buildHreflangHtml('tag/' . $tagDirName, 'https://idrinth.de', $languages), $page);
            $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/tag/' . $tagDirName, $page);
            $page = str_replace('###META_KEYWORDS###', '', $page);
            $page = str_replace('###ASIDE###', '', $page);
            $page = str_replace('###CONTENT###', $listingContent, $page);

            file_put_contents($outputDir . '/' . $lang . '.html', minifyHtml($page));
            precompress($outputDir . '/' . $lang . '.html');

            generateFeeds(
                $tagPosts, $outputDir, $translations[$lang]['latest_posts_with_tag'] . ' ' . $tag,
                $tagDescription, '/tag/' . $tagDirName, $lang, 'https://idrinth.de',
                $rssTemplate, $rssEntryTemplate, $atomTemplate, $atomEntryTemplate
            );
        }
    }
}

// Generate 404 page per language
$notFoundDir = ROOT_DIR . '/output/404';
if (!is_dir($notFoundDir)) {
    mkdir($notFoundDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['page_not_found'], $page);
    $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations[$lang]['default_description']), $page);
    $page = str_replace('###HREFLANG###', '', $page);
    $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/404', $page);
    $page = str_replace('###META_KEYWORDS###', '', $page);
    $page = str_replace('###ASIDE###', $asideTemplates[$lang], $page);
    $page = str_replace('###CONTENT###', $notFoundTemplates[$lang], $page);
    file_put_contents($notFoundDir . '/' . $lang . '.html', minifyHtml($page));
    precompress($notFoundDir . '/' . $lang . '.html');
}

// Generate imprint page per language
$imprintDir = ROOT_DIR . '/output/imprint';
if (!is_dir($imprintDir)) {
    mkdir($imprintDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['imprint_title'], $page);
    $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations[$lang]['default_description']), $page);
    $page = str_replace('###HREFLANG###', buildHreflangHtml('imprint', 'https://idrinth.de', $languages), $page);
    $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/imprint', $page);
    $page = str_replace('###META_KEYWORDS###', '', $page);
    $page = str_replace('###ASIDE###', $asideTemplates[$lang], $page);
    $page = str_replace('###CONTENT###', $imprintTemplates[$lang], $page);
    file_put_contents($imprintDir . '/' . $lang . '.html', minifyHtml($page));
    precompress($imprintDir . '/' . $lang . '.html');
}

// Generate canceled donation page per language (not in sitemap)
$canceledDir = ROOT_DIR . '/output/canceled';
if (!is_dir($canceledDir)) {
    mkdir($canceledDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['canceled_title'], $page);
    $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations[$lang]['default_description']), $page);
    $page = str_replace('###HREFLANG###', '', $page);
    $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/canceled', $page);
    $page = str_replace('###META_KEYWORDS###', '', $page);
    $page = str_replace('###ASIDE###', $asideTemplates[$lang], $page);
    $page = str_replace('###CONTENT###', $canceledTemplates[$lang], $page);
    $page = str_replace('<meta name="description"', '<meta name="robots" content="noindex, nofollow">' . "\n" . '    <meta name="description"', $page);
    file_put_contents($canceledDir . '/' . $lang . '.html', minifyHtml($page));
    precompress($canceledDir . '/' . $lang . '.html');
}

// Generate thank-you page per language (not in sitemap)
$thankYouDir = ROOT_DIR . '/output/thank-you';
if (!is_dir($thankYouDir)) {
    mkdir($thankYouDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['thank_you_title'], $page);
    $page = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations[$lang]['default_description']), $page);
    $page = str_replace('###HREFLANG###', '', $page);
    $page = str_replace('###CANONICAL_URL###', 'https://idrinth.de/' . $lang . '/thank-you', $page);
    $page = str_replace('###META_KEYWORDS###', '', $page);
    $page = str_replace('###ASIDE###', $asideTemplates[$lang], $page);
    $page = str_replace('###CONTENT###', $thankYouTemplates[$lang], $page);
    $page = str_replace('<meta name="description"', '<meta name="robots" content="noindex, nofollow">' . "\n" . '    <meta name="description"', $page);
    file_put_contents($thankYouDir . '/' . $lang . '.html', minifyHtml($page));
    precompress($thankYouDir . '/' . $lang . '.html');
}

// Generate statistics page (English only, not linked or in sitemap)
$statisticsDir = ROOT_DIR . '/output/statistics';
if (!is_dir($statisticsDir)) {
    mkdir($statisticsDir, 0755, true);
}

$postRows = '';
foreach ($posts as $post) {
    $mdFile = ROOT_DIR . '/posts/' . $post['slug'] . '/en.md';
    $title = is_file($mdFile) ? extractTitle(file_get_contents($mdFile), $post['slug']) : $post['slug'];
    $categoryTitle = $translations['en']['categories'][$post['category']] ?? $post['category'];
    $dataPath = htmlspecialchars($post['category'] . '/' . $post['slug']);

    $row = $statisticsPostRowTemplate;
    $row = str_replace('###POST_TITLE###', htmlspecialchars($title), $row);
    $row = str_replace('###POST_CATEGORY###', htmlspecialchars($categoryTitle), $row);
    $row = str_replace('###POST_PATH###', $dataPath, $row);
    $row = str_replace('###POST_CATEGORY_SLUG###', htmlspecialchars($post['category']), $row);
    $postRows .= $row;
}

$categoryRows = '';
$categoryCounts = [];
foreach ($posts as $post) {
    $categoryCounts[$post['category']] = ($categoryCounts[$post['category']] ?? 0) + 1;
}
arsort($categoryCounts);
foreach ($categoryCounts as $cat => $count) {
    $categoryTitle = $translations['en']['categories'][$cat] ?? $cat;
    $row = $statisticsCategoryRowTemplate;
    $row = str_replace('###CATEGORY_TITLE###', htmlspecialchars($categoryTitle), $row);
    $row = str_replace('###CATEGORY_SLUG###', htmlspecialchars($cat), $row);
    $row = str_replace('###CATEGORY_COUNT###', (string)$count, $row);
    $categoryRows .= $row;
}

$statsContent = $statisticsTemplate;
$statsContent = str_replace('###STATS_POST_ROWS###', $postRows, $statsContent);
$statsContent = str_replace('###STATS_CATEGORY_ROWS###', $categoryRows, $statsContent);

$statsPage = $mainTemplates['en'];
$statsPage = str_replace('###PAGE_TITLE###', 'Statistics', $statsPage);
$statsPage = str_replace('###PAGE_DESCRIPTION###', htmlspecialchars($translations['en']['default_description']), $statsPage);
$statsPage = str_replace('###HREFLANG###', '', $statsPage);
$statsPage = str_replace('###CANONICAL_URL###', 'https://idrinth.de/en/statistics', $statsPage);
$statsPage = str_replace('###META_KEYWORDS###', '', $statsPage);
$statsPage = str_replace('###ASIDE###', $asideTemplates['en'], $statsPage);
$statsPage = str_replace('###CONTENT###', $statsContent, $statsPage);
$statsPage = str_replace('<meta name="description"', '<meta name="robots" content="noindex, nofollow">' . "\n" . '    <meta name="description"', $statsPage);
file_put_contents($statisticsDir . '/en.html', minifyHtml($statsPage));
precompress($statisticsDir . '/en.html');

// Generate weighted word score index per language
foreach ($languages as $lang) {
    $paths = [];
    $pathIndex = [];
    $wordScores = [];

    foreach ($posts as $post) {
        $slug = $post['slug'];
        $category = $post['category'];
        $articlePath = $category . '/' . $slug;

        $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
        if (!is_file($mdFile)) {
            continue;
        }

        if (!isset($pathIndex[$articlePath])) {
            $pathIndex[$articlePath] = count($paths);
            $paths[] = $articlePath;
        }
        $pathKey = $pathIndex[$articlePath];

        $markdown = file_get_contents($mdFile);
        $articleWords = [];

        // H1 words: 10 points each
        if (preg_match_all('/^# (?!#)(.+)$/m', $markdown, $matches)) {
            foreach ($matches[1] as $heading) {
                foreach (preg_split('/\s+/', mb_strtolower($heading)) as $word) {
                    $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
                    if (mb_strlen($word) >= 3) {
                        $articleWords[$word] = ($articleWords[$word] ?? 0) + 10;
                    }
                }
            }
        }

        // H2 words: 4 points each
        if (preg_match_all('/^## (?!#)(.+)$/m', $markdown, $matches)) {
            foreach ($matches[1] as $heading) {
                foreach (preg_split('/\s+/', mb_strtolower($heading)) as $word) {
                    $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
                    if (mb_strlen($word) >= 3) {
                        $articleWords[$word] = ($articleWords[$word] ?? 0) + 4;
                    }
                }
            }
        }

        // Tag words: 6 points each
        foreach ($post['tags'] ?? [] as $tag) {
            foreach (preg_split('/[\s-]+/', mb_strtolower($tag)) as $word) {
                $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
                if (mb_strlen($word) >= 3) {
                    $articleWords[$word] = ($articleWords[$word] ?? 0) + 6;
                }
            }
        }

        // Category words: 8 points each
        $categoryName = $translations[$lang]['categories'][$category] ?? $category;
        foreach (preg_split('/[\s-]+/', mb_strtolower($categoryName)) as $word) {
            $word = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
            if (mb_strlen($word) >= 3) {
                $articleWords[$word] = ($articleWords[$word] ?? 0) + 8;
            }
        }

        foreach ($articleWords as $word => $score) {
            if (!isset($wordScores[$word])) {
                $wordScores[$word] = [];
            }
            $wordScores[$word][$pathKey] = $score;
        }
    }

    ksort($wordScores);
    $terms = new stdClass();
    foreach ($wordScores as $word => $articles) {
        $obj = new stdClass();
        foreach ($articles as $idx => $score) {
            $obj->{(string)$idx} = $score;
        }
        $terms->{$word} = $obj;
    }
    $output = ['paths' => $paths, 'terms' => $terms];
    file_put_contents(ROOT_DIR . '/output/words-' . $lang . '.json', json_encode($output, JSON_UNESCAPED_UNICODE));
    precompress(ROOT_DIR . '/output/words-' . $lang . '.json');
}

// Generate sitemap.xml
$sitemapPaths = [];
$baseUrl = 'https://idrinth.de';

// Home page
$sitemapPaths[] = '';

// Category pages
foreach (array_keys($categories) as $category) {
    $sitemapPaths[] = $category;
}

// Individual post pages
foreach ($posts as $post) {
    $sitemapPaths[] = $post['category'] . '/' . $post['slug'];
}

// Tag pages
foreach (array_keys($tags) as $tag) {
    $sitemapPaths[] = 'tag/' . str_replace(' ', '-', $tag);
}

// Imprint
$sitemapPaths[] = 'imprint';

$sitemapEntries = '';
foreach ($sitemapPaths as $path) {
    $hreflangLinks = buildHreflangSitemap($path, $baseUrl, $languages);
    foreach ($languages as $lang) {
        $url = $baseUrl . '/' . $lang . ($path !== '' ? '/' . $path : '');
        $entry = str_replace('###URL###', htmlspecialchars($url), $sitemapEntryTemplate);
        $entry = str_replace('###HREFLANG_LINKS###', $hreflangLinks, $entry);
        $sitemapEntries .= $entry;
    }
}
$sitemap = str_replace('###SITEMAP_URLS###', $sitemapEntries, $sitemapTemplate);
file_put_contents(ROOT_DIR . '/public/sitemap.xml', $sitemap);
precompress(ROOT_DIR . '/public/sitemap.xml');

// Save template hash after successful generation
file_put_contents($templateHashFile, $currentHash);
