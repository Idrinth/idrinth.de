<?php

define("ROOT_DIR", dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'De\\Idrinth\\Blog\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = ROOT_DIR . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

use De\Idrinth\Blog\Tracking\TrackerInterface;
use De\Idrinth\Blog\Tracking\FileTracker;

function preferredEncoding(): string
{
    $accept = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    if (str_contains($accept, 'br')) {
        return 'br';
    }
    if (str_contains($accept, 'gzip')) {
        return 'gzip';
    }
    return '';
}
function sendCompressed(string $path, string $contentType): void
{
    $encoding = preferredEncoding();
    if ($encoding === 'br' && is_file($path . '.br')) {
        header('Content-Encoding: br');
        header('Content-Type: ' . $contentType);
        readfile($path . '.br');
        return;
    }
    if ($encoding === 'gzip' && is_file($path . '.gz')) {
        header('Content-Encoding: gzip');
        header('Content-Type: ' . $contentType);
        readfile($path . '.gz');
        return;
    }
    header('Content-Type: ' . $contentType);
    readfile($path);
}

$tracker = new FileTracker(ROOT_DIR . '/output', ROOT_DIR . '/ads');
$visitorIp = $_SERVER['REMOTE_ADDR'] ?? '';
$visitorAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$visitorHash = md5($visitorIp . $visitorAgent . date('Y-m-d'));

function displayHTMLAndExit(string $path, bool $countView, string $language, string $contentPath, TrackerInterface $tracker, string $visitorHash): void
{
    if (is_file($path)) {
        header('Vary: Accept-Encoding');
        $cssHash = md5_file(ROOT_DIR . '/public/styles.css');
        $themeHash = md5_file(ROOT_DIR . '/public/theme.js');
        header("Link: </styles.css?$cssHash>; rel=preload; as=style, </theme.js?$themeHash>; rel=preload; as=script");
        header('Permissions-Policy: all=()');
        if ($countView) {
            register_shutdown_function([$tracker, 'incrementPageView'], $contentPath, $visitorHash);
            register_shutdown_function([$tracker, 'trackLanguageVisitor'], $language, date('Y-m'), $visitorHash);
        }
        sendCompressed($path, 'text/html; charset=utf-8');
        exit;
    }
}
function findAndExit(string $uri, string $language, bool $countView, TrackerInterface $tracker, string $visitorHash): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    displayHTMLAndExit($path . $language . '.html', $countView, $language, $uri, $tracker, $visitorHash);
    displayHTMLAndExit($path . 'en.html', $countView, 'en', $uri, $tracker, $visitorHash);
}
function findAdAndExit(string $file, string $mime, TrackerInterface $tracker, string $visitorHash): void
{
    $size = pathinfo($file, PATHINFO_FILENAME);
    $month = date('Y-m');
    $path = ROOT_DIR . '/ads/' . $month;
    if (is_file($path . '/' . $file)) {
        register_shutdown_function([$tracker, 'incrementAdView'], $month, $size, $visitorHash);
        header('Content-type: ' . $mime);
        readfile($path . '/' . $file);
        exit;
    }
    $fallback = ROOT_DIR . '/ads/0000-00';
    if (is_file($fallback . '/' . $file)) {
        register_shutdown_function([$tracker, 'incrementAdView'], '0000-00', $size, $visitorHash);
        header('Content-type: ' . $mime);
        readfile($fallback . '/' . $file);
        exit;
    }
}
$uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
$language = 'en';
$supportedLanguages = array_keys(json_decode(file_get_contents(ROOT_DIR . '/config/languages.json'), true));
$languageFromUrl = false;
foreach ($supportedLanguages as $lang) {
    if ($uri === $lang || str_starts_with($uri, $lang . '/')) {
        $language = $lang;
        $uri = trim(substr($uri, strlen($lang)), '/');
        $languageFromUrl = true;
        break;
    }
}
if ($uri === 'ad/leaderboard.avif') {
    findAdAndExit('leaderboard.avif', 'image/avif', $tracker, $visitorHash);
}
if ($uri === 'ad/leaderboard.webp') {
    findAdAndExit('leaderboard.webp', 'image/webp', $tracker, $visitorHash);
}
if ($uri === 'ad/leaderboard.jpg') {
    findAdAndExit('leaderboard.jpg', 'image/jpeg', $tracker, $visitorHash);
}
if ($uri === 'ad/banner.avif') {
    findAdAndExit('banner.avif', 'image/avif', $tracker, $visitorHash);
}
if ($uri === 'ad/banner.webp') {
    findAdAndExit('banner.webp', 'image/webp', $tracker, $visitorHash);
}
if ($uri === 'ad/banner.jpg') {
    findAdAndExit('banner.jpg', 'image/jpeg', $tracker, $visitorHash);
}
if ($uri === 'ad/mobile.avif') {
    findAdAndExit('mobile.avif', 'image/avif', $tracker, $visitorHash);
}
if ($uri === 'ad/mobile.webp') {
    findAdAndExit('mobile.webp', 'image/webp', $tracker, $visitorHash);
}
if ($uri === 'ad/mobile.jpg') {
    findAdAndExit('mobile.jpg', 'image/jpeg', $tracker, $visitorHash);
}
if ($uri === 'ad.lnk') {
    findAdAndExit('link.txt', 'txt/plain', $tracker, $visitorHash);
}
$langPattern = implode('|', array_map('preg_quote', $supportedLanguages));
if (preg_match('/^words-(' . $langPattern . ')\.json$/', $uri, $wm)) {
    $wordsFile = ROOT_DIR . '/output/words-' . $wm[1] . '.json';
    if (is_file($wordsFile)) {
        header('Vary: Accept-Encoding');
        header('Cache-Control: max-age=3600');
        sendCompressed($wordsFile, 'application/json; charset=utf-8');
        exit;
    }
}
if ($uri === 'views' || str_starts_with($uri, 'views/')) {
    $viewPath = trim(substr($uri, 5), '/');
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($tracker->getPageViews($viewPath));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^vote/(.+)$#', $uri, $vm)) {
    $votePath = $vm[1];
    $body = trim(file_get_contents('php://input'));
    if ($body !== 'up' && $body !== 'down') {
        header('Content-type: application/json', true, 400);
        echo json_encode(['error' => 'body must be up or down']);
        exit;
    }
    $direction = $body === 'up' ? 1 : -1;
    $basePath = ROOT_DIR . '/output/' . $votePath . '/';
    if (!is_dir($basePath)) {
        header('Content-type: application/json', true, 404);
        echo json_encode(['error' => 'not found']);
        exit;
    }
    $identity = md5($visitorIp . $visitorAgent) . date('Y-m-d');
    try {
        $result = $tracker->vote($votePath, $identity, $direction);
        header('Content-type: application/json');
        echo json_encode($result);
    } catch (\RuntimeException $e) {
        header('Content-type: application/json', true, 500);
        echo json_encode(['error' => 'failed']);
    }
    exit;
}
if ($uri === 'votes' || str_starts_with($uri, 'votes/')) {
    $votePath = trim(substr($uri, 5), '/');
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($tracker->getVotes($votePath));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^readtime/(.+)$#', $uri, $rtm)) {
    $rtPath = $rtm[1];
    if (str_contains($rtPath, '..') || str_contains($rtPath, "\0") || str_starts_with($rtPath, '/')) {
        header('Content-type: application/json', true, 400);
        echo json_encode(['error' => 'invalid path']);
        exit;
    }
    $body = trim(file_get_contents('php://input'));
    $parts = explode(':', $body, 2);
    $seconds = (int)$parts[0];
    $sessionId = isset($parts[1]) ? preg_replace('/[^a-z0-9]/', '', $parts[1]) : '';
    if ($seconds < 5 || $seconds > 3600) {
        header('Content-type: application/json', true, 400);
        echo json_encode(['error' => 'seconds must be between 5 and 3600']);
        exit;
    }
    $outputRoot = realpath(ROOT_DIR . '/output');
    $basePath = realpath(ROOT_DIR . '/output/' . $rtPath);
    if ($basePath === false || !is_dir($basePath) || !str_starts_with($basePath, $outputRoot)) {
        header('Content-type: application/json', true, 404);
        echo json_encode(['error' => 'not found']);
        exit;
    }
    $key = $sessionId !== '' ? $visitorHash . '-' . $sessionId : $visitorHash;
    if ($tracker->trackReadTime($rtPath, $key, $seconds)) {
        header('Content-type: application/json');
        echo json_encode(['ok' => true]);
    } else {
        header('Content-type: application/json', true, 500);
        echo json_encode(['error' => 'failed']);
    }
    exit;
}
if ($uri === 'readtime' || str_starts_with($uri, 'readtime/')) {
    $rtPath = trim(substr($uri, 8), '/');
    if (str_contains($rtPath, '..') || str_contains($rtPath, "\0") || str_starts_with($rtPath, '/')) {
        header('Content-type: application/json', true, 400);
        echo json_encode(['error' => 'invalid path']);
        exit;
    }
    $outputRoot = realpath(ROOT_DIR . '/output');
    $candidate = $rtPath !== '' ? ROOT_DIR . '/output/' . $rtPath : ROOT_DIR . '/output';
    $basePath = realpath($candidate);
    if ($basePath === false || !str_starts_with($basePath, $outputRoot)) {
        header('Content-type: application/json', true, 404);
        echo json_encode(['error' => 'not found']);
        exit;
    }
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($tracker->getReadTime($rtPath));
    exit;
}
if ($uri === 'lang-stats') {
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($tracker->getLanguageStats($supportedLanguages));
    exit;
}
if ($uri === 'ad-stats') {
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($tracker->getAdStats());
    exit;
}
if ($uri === 'random') {
    $postsFile = ROOT_DIR . '/output/posts.json';
    if (is_file($postsFile)) {
        $posts = json_decode(file_get_contents($postsFile), true);
        if ($posts) {
            header('Location: ' . $posts[array_rand($posts)], true, 302);
            exit;
        }
    }
}
if (!$languageFromUrl && isset($_COOKIE['language']) && in_array($_COOKIE['language'], $supportedLanguages, true)) {
    $language = $_COOKIE['language'];
    $redirect = '/' . $language . '/' . $uri;
    $redirect = rtrim($redirect, '/');
    header('Location: ' . $redirect, true, 302);
    exit;
}
if (!$languageFromUrl) {
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (preg_match_all('/([a-z]{2})(?:-[a-zA-Z]+)?(?:;q=([0-9.]+))?/', $accept, $matches, PREG_SET_ORDER)) {
        usort($matches, fn($a, $b) => ($b[2] ?? '1') <=> ($a[2] ?? '1'));
        foreach ($matches as $match) {
            if (in_array($match[1], $supportedLanguages, true)) {
                $language = $match[1];
                break;
            }
        }
    }
    $redirect = '/' . $language . '/' . $uri;
    $redirect = rtrim($redirect, '/');
    header('Location: ' . $redirect, true, 302);
    exit;
}
setcookie('language', $language, [
    'expires' => time() + 30 * 24 * 60 * 60,
    'path' => '/',
]);
$feedFormats = ['feed.rss' => 'application/rss+xml', 'feed.atom' => 'application/atom+xml'];
foreach ($feedFormats as $feedFile => $contentType) {
    if ($uri === $feedFile || str_ends_with($uri, '/' . $feedFile)) {
        $feedPath = $uri === $feedFile ? '' : substr($uri, 0, -(strlen($feedFile) + 1));
        $ext = str_ends_with($feedFile, '.rss') ? 'rss' : 'atom';
        $file = ROOT_DIR . '/output/' . ($feedPath !== '' ? $feedPath . '/' : '') . $language . '.' . $ext;
        if (is_file($file)) {
            header('Vary: Accept-Encoding');
            header('Cache-Control: max-age=3600');
            sendCompressed($file, $contentType . '; charset=utf-8');
            exit;
        }
        break;
    }
}
if (!str_contains($uri, '.')) {
    findAndExit($uri, $language, !in_array($uri, ['imprint', 'thank-you', 'statistics', 'canceled'], true), $tracker, $visitorHash);
}
header('Content-type: text/html; charset=utf-8', true, 404);
findAndExit('404', $language, false, $tracker, $visitorHash);
