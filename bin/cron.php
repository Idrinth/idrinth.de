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
        $entry = str_replace('###POST_CATEGORY###', htmlspecialchars($post['category'] . '/' . $slug), $entry);
        $entry = str_replace('###POST_TITLE###', htmlspecialchars($title), $entry);
        $entry = str_replace('###POST_DESCRIPTION###', htmlspecialchars($description), $entry);
        $entry = str_replace('###POST_TAGS###', $tagsHtml, $entry);
        $entry = str_replace('###POST_DATE###', htmlspecialchars($post['date']), $entry);

        $entries .= $entry . "\n";
    }
    return $entries;
}

// Load templates
$mainTemplate = file_get_contents(ROOT_DIR . '/resources/main.html');
$entryTemplate = file_get_contents(ROOT_DIR . '/resources/post-listing-entry.html');

// Detect template changes
$templateHashFile = ROOT_DIR . '/output/.template-hash';
$currentHash = md5($mainTemplate . $entryTemplate);
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
    $posts[] = $meta;
}

// Sort by date descending
usort($posts, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

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
        $dateHtml = '<time datetime="' . htmlspecialchars($post['date']) . '">' . htmlspecialchars($post['date']) . '</time>' . "\n";
        $content = preg_replace('/<\/h1>\n/', "</h1>\n" . $dateHtml, $content, 1);

        $page = $mainTemplate;
        $page = str_replace('###PAGE_TITLE###', htmlspecialchars($title), $page);
        $page = str_replace("<h1>Latest Posts</h1>\n    <ol>\n        ###POST_LISTING###\n    </ol>", $content, $page);
        $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);

        file_put_contents($outputFile, $page);
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
        $listing = buildListingEntries($homePosts, $entryTemplate, $lang);

        $page = $mainTemplate;
        $page = str_replace('###PAGE_TITLE###', 'Latest Posts', $page);
        $page = str_replace('###POST_LISTING###', $listing, $page);
        $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);

        file_put_contents(ROOT_DIR . '/output/' . $lang . '.html', $page);
    }
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

if ($postsChanged) {
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
}

// Generate 404 page per language
$notFoundContent = '<h1>Page Not Found</h1>' . "\n" . '<p>The page you are looking for does not exist.</p>' . "\n";
$notFoundDir = ROOT_DIR . '/output/404';
if (!is_dir($notFoundDir)) {
    mkdir($notFoundDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplate;
    $page = str_replace('###PAGE_TITLE###', 'Page Not Found', $page);
    $page = str_replace("<h1>Latest Posts</h1>\n    <ol>\n        ###POST_LISTING###\n    </ol>", $notFoundContent, $page);
    $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);
    file_put_contents($notFoundDir . '/' . $lang . '.html', $page);
}

// Generate imprint page per language
$imprintContent = '<h1>Impressum</h1>' . "\n"
    . '<h2>Angaben gemäß § 5 TMG</h2>' . "\n"
    . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
    . '<h2>Kontakt</h2>' . "\n"
    . '<p>Telefon: +4917647945826<br>eMail: webmaster@bjoern-buettner.me</p>' . "\n"
    . '<h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>' . "\n"
    . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
    . '<h2>Haftungsausschluss</h2>' . "\n"
    . '<h3>Haftung für Links</h3>' . "\n"
    . '<p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>' . "\n"
    . '<h3>Datenschutz</h3>' . "\n"
    . '<p>Die Nutzung unserer Webseite ist in der Regel ohne Angabe personenbezogener Daten möglich. Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, Anschrift oder eMail-Adressen) erhoben werden, erfolgt dies, soweit möglich, stets auf freiwilliger Basis. Diese Daten werden ohne Ihre ausdrückliche Zustimmung nicht an Dritte weitergegeben.</p>' . "\n"
    . '<p>Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht möglich.</p>' . "\n"
    . '<p>Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten durch Dritte zur Übersendung von nicht ausdrücklich angeforderter Werbung und Informationsmaterialien wird hiermit ausdrücklich widersprochen. Die Betreiber der Seiten behalten sich ausdrücklich rechtliche Schritte im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-Mails, vor.</p>' . "\n"
    . '<p><small>Quelle: Disclaimer von eRecht24, dem Portal zum Internetrecht von Rechtsanwalt Sören Siebert.</small></p>' . "\n";
$imprintDir = ROOT_DIR . '/output/imprint';
if (!is_dir($imprintDir)) {
    mkdir($imprintDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplate;
    $page = str_replace('###PAGE_TITLE###', 'Impressum', $page);
    $page = str_replace("<h1>Latest Posts</h1>\n    <ol>\n        ###POST_LISTING###\n    </ol>", $imprintContent, $page);
    $page = str_replace('lang="en"', 'lang="' . $lang . '"', $page);
    file_put_contents($imprintDir . '/' . $lang . '.html', $page);
}

// Save template hash after successful generation
file_put_contents($templateHashFile, $currentHash);
