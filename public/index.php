<?php

define("ROOT_DIR", dirname(__DIR__));
exit(file_get_contents(ROOT_DIR . '/templates/template.html'));
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
$uri = rtrim($_SERVER['REQUEST_URI'] ?? '', '/');
$language = 'en';
if ($uri === '') {
    findAndExit($uri, $language);
}
if (!str_contains($uri, '.')) {
    findAndExit($uri, $language);
}
findAndExit('404', $language);