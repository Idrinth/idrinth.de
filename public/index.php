<?php

define("ROOT_DIR", dirname(__DIR__));
function findAndExit(string $uri, string $language): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    if (is_file($path . $language . '.html')) {
        exit(file_get_contents($path . $language . '.html'));
    }
    if (is_file($path . 'en.html')) {
        exit(file_get_contents($path . '/en.html'));
    }
}
function findAdAndExit(string $file): void
{
    $path = ROOT_DIR . '/ads/' . date('Y-m');
    if (is_file($path . '/' . $file)) {
        exit(file_get_contents($path . '/' . $file));
    }
    if (is_file(ROOT_DIR . '/ads/0000-00/' . $file)) {
        exit(file_get_contents(ROOT_DIR . '/ads/0000-00/' . $file));
    }
}
$uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
$language = 'en';
if ($uri === 'ad.jpg') {
    findAdAndExit('ad.jpg');
}
if ($uri === 'ad.lnk') {
    findAdAndExit('link.txt');
}
if ($uri === '') {
    findAndExit($uri, $language);
}
if (!str_contains($uri, '.')) {
    findAndExit($uri, $language);
}
findAndExit('404', $language);