<?php

define("ROOT_DIR", dirname(__DIR__));

$languages = ['en', 'de', 'fr'];

$translations = [
    'en' => [
        'latest_posts' => 'Latest Posts',
        'latest_posts_in' => 'Latest Posts in',
        'page_not_found' => 'Page Not Found',
        'page_not_found_text' => 'The page you are looking for does not exist.',
        'views' => 'views',
        'imprint_title' => 'Imprint',
        'imprint_content' => '<h1>Legal Notice</h1>' . "\n"
            . '<h2>Information according to § 5 TMG</h2>' . "\n"
            . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
            . '<h2>Contact</h2>' . "\n"
            . '<p>Phone: +4917647945826<br>Email: webmaster@bjoern-buettner.me</p>' . "\n"
            . '<h2>Responsible for content according to § 55 Abs. 2 RStV</h2>' . "\n"
            . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
            . '<h2>Disclaimer</h2>' . "\n"
            . '<h3>Liability for Links</h3>' . "\n"
            . '<p>Our website contains links to external third-party websites, over whose content we have no influence. Therefore, we cannot accept any liability for this external content. The respective provider or operator of the linked pages is always responsible for the content of the linked pages. The linked pages were checked for possible legal violations at the time of linking. Illegal content was not recognisable at the time of linking. However, permanent monitoring of the content of the linked pages is not reasonable without concrete evidence of a violation of the law. If we become aware of any infringements, we will remove such links immediately.</p>' . "\n"
            . '<h3>Data Protection</h3>' . "\n"
            . '<p>The use of our website is generally possible without providing personal data. As far as personal data (e.g. name, address or email addresses) is collected on our pages, this is always done on a voluntary basis as far as possible. This data will not be passed on to third parties without your express consent.</p>' . "\n"
            . '<p>We point out that data transmission over the Internet (e.g. communication by email) can have security gaps. Complete protection of data against access by third parties is not possible.</p>' . "\n"
            . '<p>The use of contact data published within the framework of the imprint obligation by third parties for sending unsolicited advertising and information materials is hereby expressly prohibited. The operators of the pages expressly reserve the right to take legal action in the event of unsolicited sending of advertising information, such as spam emails.</p>' . "\n",
        'related_posts' => 'Related Posts',
        'categories' => [
            'software-engineering' => 'Software Engineering',
            'open-source' => 'Open-Source',
            'games' => 'Games',
            'streaming' => 'Streaming',
            'modding' => 'Modding',
            'stories' => 'Written Stories',
            'pen-and-paper' => 'Pen & Paper',
            'world-building' => 'World Building Results',
        ],
    ],
    'de' => [
        'latest_posts' => 'Neueste Beiträge',
        'latest_posts_in' => 'Neueste Beiträge in',
        'page_not_found' => 'Seite nicht gefunden',
        'page_not_found_text' => 'Die Seite, die Sie suchen, existiert nicht.',
        'views' => 'Aufrufe',
        'imprint_title' => 'Impressum',
        'imprint_content' => '<h1>Impressum</h1>' . "\n"
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
            . '<p><small>Quelle: Disclaimer von eRecht24, dem Portal zum Internetrecht von Rechtsanwalt Sören Siebert.</small></p>' . "\n",
        'related_posts' => 'Ähnliche Beiträge',
        'categories' => [
            'software-engineering' => 'Softwareentwicklung',
            'open-source' => 'Open-Source',
            'games' => 'Spiele',
            'streaming' => 'Streaming',
            'modding' => 'Modding',
            'stories' => 'Geschichten',
            'pen-and-paper' => 'Pen & Paper',
            'world-building' => 'Weltenbau',
        ],
    ],
    'fr' => [
        'latest_posts' => 'Derniers articles',
        'latest_posts_in' => 'Derniers articles dans',
        'page_not_found' => 'Page non trouvée',
        'page_not_found_text' => 'La page que vous recherchez n\'existe pas.',
        'views' => 'vues',
        'imprint_title' => 'Mentions légales',
        'imprint_content' => '<h1>Mentions légales</h1>' . "\n"
            . '<h2>Informations conformément au § 5 TMG</h2>' . "\n"
            . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
            . '<h2>Contact</h2>' . "\n"
            . '<p>Téléphone : +4917647945826<br>Email : webmaster@bjoern-buettner.me</p>' . "\n"
            . '<h2>Responsable du contenu selon § 55 Abs. 2 RStV</h2>' . "\n"
            . '<p>Björn Büttner<br>Böllerts Höfe 4<br>45479 Mülheim an der Ruhr</p>' . "\n"
            . '<h2>Avertissement</h2>' . "\n"
            . '<h3>Responsabilité pour les liens</h3>' . "\n"
            . '<p>Notre offre contient des liens vers des sites web externes de tiers, sur le contenu desquels nous n\'avons aucune influence. Par conséquent, nous ne pouvons assumer aucune responsabilité pour ces contenus externes. Le fournisseur ou exploitant respectif des pages liées est toujours responsable du contenu des pages liées. Les pages liées ont été vérifiées pour d\'éventuelles violations de la loi au moment de la liaison. Aucun contenu illégal n\'était identifiable au moment de la liaison. Cependant, un contrôle permanent du contenu des pages liées n\'est pas raisonnable sans preuve concrète d\'une violation de la loi. Dès que nous aurons connaissance de violations de la loi, nous supprimerons immédiatement ces liens.</p>' . "\n"
            . '<h3>Protection des données</h3>' . "\n"
            . '<p>L\'utilisation de notre site web est généralement possible sans fournir de données personnelles. Dans la mesure où des données personnelles (par exemple, nom, adresse ou adresses e-mail) sont collectées sur nos pages, cela se fait toujours sur une base volontaire dans la mesure du possible. Ces données ne seront pas transmises à des tiers sans votre consentement explicite.</p>' . "\n"
            . '<p>Nous soulignons que la transmission de données sur Internet (par exemple, la communication par e-mail) peut présenter des failles de sécurité. Une protection complète des données contre l\'accès par des tiers n\'est pas possible.</p>' . "\n"
            . '<p>L\'utilisation par des tiers des coordonnées publiées dans le cadre de l\'obligation de mentions légales pour l\'envoi de publicité et de matériel d\'information non expressément demandés est expressément interdite. Les exploitants des pages se réservent expressément le droit d\'engager des poursuites judiciaires en cas d\'envoi non sollicité d\'informations publicitaires, telles que des courriers indésirables.</p>' . "\n",
        'related_posts' => 'Articles similaires',
        'categories' => [
            'software-engineering' => 'Génie logiciel',
            'open-source' => 'Open-Source',
            'games' => 'Jeux',
            'streaming' => 'Streaming',
            'modding' => 'Modding',
            'stories' => 'Histoires écrites',
            'pen-and-paper' => 'Jeu de rôle',
            'world-building' => 'Création de mondes',
        ],
    ],
];

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
            $points += 10;
        }
        $sharedTags = array_intersect($currentTags, $post['tags'] ?? []);
        $points += count($sharedTags);
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

        $viewFile = ROOT_DIR . '/output/' . $post['category'] . '/' . $slug . '/viewcount.txt';
        $viewCount = is_file($viewFile) ? (int)file_get_contents($viewFile) : 0;
        $entry = str_replace('###POST_VIEWS###', (string)$viewCount, $entry);

        $entries .= $entry . "\n";
    }
    return $entries;
}

// Load per-language templates
$mainTemplates = [];
$entryTemplates = [];
$listingTemplates = [];
foreach ($languages as $lang) {
    $mainTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/main.html');
    $entryTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/post-listing-entry.html');
    $listingTemplates[$lang] = file_get_contents(ROOT_DIR . '/resources/' . $lang . '/post-listing.html');
}

// Detect template changes
$templateHashFile = ROOT_DIR . '/output/.template-hash';
$currentHash = md5(implode('', $mainTemplates) . implode('', $entryTemplates) . implode('', $listingTemplates));
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

// Generate posts.json for random redirect and other consumers
$postUrls = array_map(function ($post) {
    return '/' . $post['category'] . '/' . $post['slug'];
}, $posts);
file_put_contents(ROOT_DIR . '/output/posts.json', json_encode($postUrls));

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
        $viewsLabel = $translations[$lang]['views'];
        $dateHtml = '<time datetime="' . htmlspecialchars($post['date']) . '">' . htmlspecialchars($post['date']) . '</time>'
            . ' <span class="views" data-path="' . htmlspecialchars($category . '/' . $slug) . '">' . $viewCount . ' ' . $viewsLabel . '</span>' . "\n";
        $content = preg_replace('/<\/h1>\n/', "</h1>\n" . $dateHtml, $content, 1);

        $relatedPosts = getRelatedPosts($post, $posts);
        if ($relatedPosts !== []) {
            $categoryTitle = $translations[$lang]['categories'][$category] ?? $category;
            $relatedHtml = '<h2>' . $translations[$lang]['related_posts'] . "</h2>\n<ol>\n";
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
                $relatedHtml .= '<li><a href="/' . $lang . '/' . htmlspecialchars($related['category'] . '/' . $related['slug']) . '/">'
                    . htmlspecialchars($relTitle) . '</a> — '
                    . htmlspecialchars($relCategory) . ', '
                    . htmlspecialchars($related['date']) . "</li>\n";
            }
            $relatedHtml .= "</ol>\n";
            $content .= $relatedHtml;
        }

        $page = $mainTemplates[$lang];
        $page = str_replace('###PAGE_TITLE###', htmlspecialchars($title), $page);
        $page = str_replace('###CONTENT###', $content, $page);

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
        $listing = buildListingEntries($homePosts, $entryTemplates[$lang], $lang);

        $listingContent = str_replace('###POST_LISTING_ENTRY###', $listing, $listingTemplates[$lang]);

        $page = $mainTemplates[$lang];
        $page = str_replace('###PAGE_TITLE###', $translations[$lang]['latest_posts'], $page);
        $page = str_replace('###CONTENT###', $listingContent, $page);

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

        foreach ($languages as $lang) {
            $categoryTitle = $translations[$lang]['categories'][$category] ?? ucwords(str_replace('-', ' ', $category));
            $listing = buildListingEntries($catPosts, $entryTemplates[$lang], $lang);

            $headingText = $translations[$lang]['latest_posts_in'] . ' ' . htmlspecialchars($categoryTitle);
            $listingContent = str_replace('###POST_LISTING_ENTRY###', $listing, $listingTemplates[$lang]);
            $listingContent = preg_replace('/<h1>[^<]+<\/h1>/', '<h1>' . $headingText . '</h1>', $listingContent, 1);

            $page = $mainTemplates[$lang];
            $page = str_replace('###PAGE_TITLE###', htmlspecialchars($categoryTitle), $page);
            $page = str_replace('###CONTENT###', $listingContent, $page);

            file_put_contents($outputDir . '/' . $lang . '.html', $page);
        }
    }
}

// Generate 404 page per language
$notFoundDir = ROOT_DIR . '/output/404';
if (!is_dir($notFoundDir)) {
    mkdir($notFoundDir, 0755, true);
}
foreach ($languages as $lang) {
    $notFoundContent = '<h1>' . $translations[$lang]['page_not_found'] . '</h1>' . "\n"
        . '<p>' . $translations[$lang]['page_not_found_text'] . '</p>' . "\n";

    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['page_not_found'], $page);
    $page = str_replace('###CONTENT###', $notFoundContent, $page);
    file_put_contents($notFoundDir . '/' . $lang . '.html', $page);
}

// Generate imprint page per language
$imprintDir = ROOT_DIR . '/output/imprint';
if (!is_dir($imprintDir)) {
    mkdir($imprintDir, 0755, true);
}
foreach ($languages as $lang) {
    $page = $mainTemplates[$lang];
    $page = str_replace('###PAGE_TITLE###', $translations[$lang]['imprint_title'], $page);
    $page = str_replace('###CONTENT###', $translations[$lang]['imprint_content'], $page);
    file_put_contents($imprintDir . '/' . $lang . '.html', $page);
}

// Generate sitemap.xml
$sitemapUrls = [];
$baseUrl = 'https://idrinth.de';

// Home page
$sitemapUrls[] = $baseUrl . '/';

// Category pages
foreach (array_keys($categories) as $category) {
    $sitemapUrls[] = $baseUrl . '/' . $category;
}

// Individual post pages
foreach ($posts as $post) {
    $sitemapUrls[] = $baseUrl . '/' . $post['category'] . '/' . $post['slug'];
}

// Imprint
$sitemapUrls[] = $baseUrl . '/imprint';

$sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($sitemapUrls as $url) {
    $sitemap .= '  <url><loc>' . htmlspecialchars($url) . '</loc></url>' . "\n";
}
$sitemap .= '</urlset>' . "\n";
file_put_contents(ROOT_DIR . '/public/sitemap.xml', $sitemap);

// Save template hash after successful generation
file_put_contents($templateHashFile, $currentHash);
