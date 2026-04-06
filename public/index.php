<?php

define("ROOT_DIR", dirname(__DIR__));
function incrementViewCount(string $path): void
{
    $viewFile = $path . 'viewcount.txt';
    $fp = fopen($viewFile, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        $count = (int)stream_get_contents($fp);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string)($count + 1));
        flock($fp, LOCK_UN);
    }
    if ($fp) {
        fclose($fp);
    }
}
function displayHTMLAndExit(string $path, bool $countView = true): void
{
    if (is_file($path)) {
        if ($countView) {
            incrementViewCount(dirname($path));
        }
        header('Content-type: text/html; charset=utf-8');
        header('Link: </styles.css>; rel=preload; as=style, </theme.js>; rel=preload; as=script, </ad.jpg>; rel=preload; as=image');
        header('Permissions-Policy: all=()');
        readfile($path);
        exit;
    }
}
function findAndExit(string $uri, string $language, bool $countView = true): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    displayHTMLAndExit($path . $language . '.html', $countView);
    displayHTMLAndExit($path . 'en.html', $countView);
}
function findAdAndExit(string $file, string $mime): void
{
    $path = ROOT_DIR . '/ads/' . date('Y-m');
    if (is_file($path . '/' . $file)) {
        header('Content-type: ' . $mime);
        readfile($path . '/' . $file);
        exit;
    }
    if (is_file(ROOT_DIR . '/ads/0000-00/' . $file)) {
        header('Content-type: ' . $mime);
        readfile(ROOT_DIR . '/ads/0000-00/' . $file);
        exit;
    }
}
$uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
$language = 'en';
$supportedLanguages = ['en', 'fr', 'de'];
$languageFromUrl = false;
foreach ($supportedLanguages as $lang) {
    if ($uri === $lang || str_starts_with($uri, $lang . '/')) {
        $language = $lang;
        $uri = trim(substr($uri, strlen($lang)), '/');
        $languageFromUrl = true;
        break;
    }
}
if ($uri === 'ad.jpg') {
    findAdAndExit('ad.jpg', 'image/jpeg');
}
if ($uri === 'ad.lnk') {
    findAdAndExit('link.txt', 'txt/plain');
}
if ($uri === 'views' || str_starts_with($uri, 'views/')) {
    $viewPath = trim(substr($uri, 5), '/');
    $viewFile = ROOT_DIR . '/output/' . ($viewPath !== '' ? $viewPath . '/' : '') . 'viewcount.txt';
    header('Content-type: text/plain');
    header('Cache-Control: no-cache');
    if (is_file($viewFile)) {
        readfile($viewFile);
    } else {
        echo '0';
    }
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
            header('Content-Type: ' . $contentType . '; charset=utf-8');
            header('Cache-Control: max-age=3600');
            readfile($file);
            exit;
        }
        break;
    }
}
if ($uri === '') {
    findAndExit($uri, $language);
}
if (!str_contains($uri, '.')) {
    if ($uri === 'imprint') {
        findAndExit($uri, $language, false);
    }
    if ($uri === 'thank-you') {
        findAndExit($uri, $language, false);
    }
    if ($uri === 'statistics') {
        findAndExit($uri, 'en', false);
    }
    if ($uri === 'canceled') {
        findAndExit($uri, $language, false);
    }
    findAndExit($uri, $language);
}
header('Content-type: text/html; charset=utf-8', true, 404);
findAndExit('404', $language, false);
