<?php

define("ROOT_DIR", dirname(__DIR__));
function findAndExit(string $uri, string $language): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    if (is_file($path . $language . '.html')) {
        header('Content-type: text/html; charset=utf-8');
        header('Link: </styles.css>; rel=preload; as=style');
        header('Link: </ad.jpg>; rel=preload; as=image');
        exit(file_get_contents($path . $language . '.html'));
    }
    if (is_file($path . 'en.html')) {
        header('Content-type: text/html; charset=utf-8');
        header('Link: </styles.css>; rel=preload; as=style');
        header('Link: </ad.jpg>; rel=preload; as=image');
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
if ($uri === '') {
    findAndExit($uri, $language);
}
if (!str_contains($uri, '.')) {
    findAndExit($uri, $language);
}
header('Content-type: text/html; charset=utf-8', true, 404);
findAndExit('404', $language);