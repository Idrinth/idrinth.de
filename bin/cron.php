<?php

define("ROOT_DIR", dirname(__DIR__));

$languages = ['en', 'de', 'fr'];

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
        if (strlen($trimmed) > 120) {
            return substr($trimmed, 0, 117) . '...';
        }
        return $trimmed;
    }
    return '';
}

function buildListingEntries(array $posts, string $entryTemplate, string $lang): string
{
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
            $tagsHtml .= '<li>' . htmlspecialchars($tag) . "</li>\n";
        }

        $entry = $entryTemplate;
        $entry = str_replace('###POST_CATEGORY###', htmlspecialchars($slug), $entry);
        $entry = str_replace('###POST_TITLE###', htmlspecialchars($title), $entry);
        $entry = str_replace('###POST_DESCRIPTION###', htmlspecialchars($description), $entry);
        $entry = str_replace('###POST_TAGS###', $tagsHtml, $entry);

        $entries .= $entry . "\n";
    }
    return $entries;
}

// Load templates
$mainTemplate = file_get_contents(ROOT_DIR . '/resources/main.html');
$entryTemplate = file_get_contents(ROOT_DIR . '/resources/post-listing-entry.html');

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
    $posts[] = $meta;
}

// Sort by date descending
usort($posts, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

// Generate individual post pages per available language
foreach ($posts as $post) {
    $slug = $post['slug'];
    $outputDir = ROOT_DIR . '/output/' . $slug;
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    foreach ($languages as $lang) {
        $mdFile = ROOT_DIR . '/posts/' . $slug . '/' . $lang . '.md';
        if (!is_file($mdFile)) {
            continue;
        }
        $markdown = file_get_contents($mdFile);
        $content = markdownToHtml($markdown);
        $title = extractTitle($markdown, $slug);

        $page = $mainTemplate;
        $page = str_replace('###PAGE_TITLE###', htmlspecialchars($title), $page);
        $page = str_replace("<h1>Latest Posts</h1>\n    <ol>\n        ###POST_LISTING###\n    </ol>", $content, $page);
        $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);

        file_put_contents($outputDir . '/' . $lang . '.html', $page);
    }
}

// Generate home page listing (last 9 posts) per language
$homePosts = array_slice($posts, 0, 9);
foreach ($languages as $lang) {
    $listing = buildListingEntries($homePosts, $entryTemplate, $lang);

    $page = $mainTemplate;
    $page = str_replace('###PAGE_TITLE###', 'Latest Posts', $page);
    $page = str_replace('###POST_LISTING###', $listing, $page);
    $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);

    file_put_contents(ROOT_DIR . '/output/' . $lang . '.html', $page);
}

// Generate category listing pages (last 9 per category) per language
$categories = [
    'software-engineering' => [],
    'open-source' => [],
    'games' => [],
    'streaming' => [],
    'modding' => [],
    'stories' => [],
    'pen-and-paper' => [],
    'world-building' => [],
];
foreach ($posts as $post) {
    $cat = $post['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $post;
}

foreach ($categories as $category => $catPosts) {
    $catPosts = array_slice($catPosts, 0, 9);
    $outputDir = ROOT_DIR . '/output/' . $category;
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $categoryTitle = ucwords(str_replace('-', ' ', $category));

    foreach ($languages as $lang) {
        $listing = buildListingEntries($catPosts, $entryTemplate, $lang);

        $page = $mainTemplate;
        $page = str_replace('###PAGE_TITLE###', htmlspecialchars($categoryTitle), $page);
        $page = str_replace('Latest Posts', 'Latest Posts in ' . htmlspecialchars($categoryTitle), $page);
        $page = str_replace('###POST_LISTING###', $listing, $page);
        $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);

        file_put_contents($outputDir . '/' . $lang . '.html', $page);
    }
}
