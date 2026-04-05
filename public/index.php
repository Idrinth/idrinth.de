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
function findAndExit(string $uri, string $language, bool $countView = true): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    if (is_file($path . $language . '.html')) {
        if ($countView) {
            incrementViewCount($path);
        }
        header('Content-type: text/html; charset=utf-8');
        header('Link: </styles.css>; rel=preload; as=style, </ad.jpg>; rel=preload; as=image');
        exit(file_get_contents($path . $language . '.html'));
    }
    if (is_file($path . 'en.html')) {
        if ($countView) {
            incrementViewCount($path);
        }
        header('Content-type: text/html; charset=utf-8');
        header('Link: </styles.css>; rel=preload; as=style, </ad.jpg>; rel=preload; as=image');
        exit(file_get_contents($path . '/en.html'));
    }
}
function findAdAndExit(string $file, string $mime): void
{
    $path = ROOT_DIR . '/ads/' . date('Y-m');
    if (is_file($path . '/' . $file)) {
        header('Content-type: ' . $mime);
        exit(file_get_contents($path . '/' . $file));
    }
    if (is_file(ROOT_DIR . '/ads/0000-00/' . $file)) {
        header('Content-type: ' . $mime);
        exit(file_get_contents(ROOT_DIR . '/ads/0000-00/' . $file));
    }
}
$uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
$language = 'en';
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
    exit(is_file($viewFile) ? file_get_contents($viewFile) : '0');
}
if ($uri === 'random') {
    $posts = glob(ROOT_DIR . '/posts/*/meta.json');
    if ($posts) {
        $chosen = $posts[array_rand($posts)];
        $meta = json_decode(file_get_contents($chosen), true);
        $slug = basename(dirname($chosen));
        header('Location: /' . $meta['category'] . '/' . $slug, true, 302);
        exit;
    }
}
if ($uri === '') {
    findAndExit($uri, $language);
}
if (!str_contains($uri, '.')) {
    if ($uri === 'imprint') {
        findAndExit($uri, $language, false);
    }
    findAndExit($uri, $language);
}
header('Content-type: text/html; charset=utf-8', true, 404);
findAndExit('404', $language, false);
