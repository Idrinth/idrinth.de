<?php

define("ROOT_DIR", dirname(__DIR__));
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
function incrementFile(string $filePath): void
{
    $fp = fopen($filePath, 'c+');
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
function trackUniqueVisitor(string $visitorsFile, string $uniqueCounterFile): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Y-m-d');
    $hash = md5($ip . $userAgent . $date);
    $fp = fopen($visitorsFile, 'c+');
    if (!$fp || !flock($fp, LOCK_EX)) {
        if ($fp) {
            fclose($fp);
        }
        return;
    }
    $contents = stream_get_contents($fp);
    $visitors = $contents !== '' ? explode("\n", trim($contents)) : [];
    if (in_array($hash, $visitors, true)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return;
    }
    fseek($fp, 0, SEEK_END);
    fwrite($fp, $hash . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
    incrementFile($uniqueCounterFile);
}
function trackLanguageVisitor(string $language): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Y-m-d');
    $hash = md5($ip . $userAgent . $date);
    $month = date('Y-m');
    $dir = ROOT_DIR . '/output/lang-stats';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $visitorsFile = $dir . '/visitors-' . $month . '-' . $language . '.txt';
    $counterFile = $dir . '/count-' . $month . '-' . $language . '.txt';
    $fp = fopen($visitorsFile, 'c+');
    if (!$fp || !flock($fp, LOCK_EX)) {
        if ($fp) {
            fclose($fp);
        }
        return;
    }
    $contents = stream_get_contents($fp);
    $visitors = $contents !== '' ? explode("\n", trim($contents)) : [];
    if (in_array($hash, $visitors, true)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return;
    }
    fseek($fp, 0, SEEK_END);
    fwrite($fp, $hash . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
    incrementFile($counterFile);
}
function incrementViewCount(string $path): void
{
    incrementFile($path . '/viewcount.txt');
    trackUniqueVisitor($path . '/visitors-' . date('Y-m-d') . '.txt', $path . '/unique-viewcount.txt');
}
function displayHTMLAndExit(string $path, bool $countView = true, string $language = 'en'): void
{
    if (is_file($path)) {
        header('Vary: Accept-Encoding');
        $cssHash = md5_file(ROOT_DIR . '/public/styles.css');
        $themeHash = md5_file(ROOT_DIR . '/public/theme.js');
        header("Link: </styles.css?$cssHash>; rel=preload; as=style, </theme.js?$themeHash>; rel=preload; as=script");
        header('Permissions-Policy: all=()');
        if ($countView) {
            register_shutdown_function('incrementViewCount', dirname($path));
            register_shutdown_function('trackLanguageVisitor', $language);
        }
        sendCompressed($path, 'text/html; charset=utf-8');
        exit;
    }
}
function findAndExit(string $uri, string $language, bool $countView = true): void
{
    $path = ROOT_DIR . str_replace('//', '/', '/output/' . $uri . '/');
    displayHTMLAndExit($path . $language . '.html', $countView, $language);
    displayHTMLAndExit($path . 'en.html', $countView, 'en');
}
function incrementAdViewCount(string $adDir, string $size): void
{
    incrementFile($adDir . '/viewed-' . $size . '.txt');
    trackUniqueVisitor($adDir . '/ad-visitors-' . date('Y-m-d') . '.txt', $adDir . '/unique-viewed.txt');
}
function findAdAndExit(string $file, string $mime): void
{
    $size = pathinfo($file, PATHINFO_FILENAME);
    $path = ROOT_DIR . '/ads/' . date('Y-m');
    if (is_file($path . '/' . $file)) {
        register_shutdown_function('incrementAdViewCount', $path, $size);
        header('Content-type: ' . $mime);
        readfile($path . '/' . $file);
        exit;
    }
    $fallback = ROOT_DIR . '/ads/0000-00';
    if (is_file($fallback . '/' . $file)) {
        register_shutdown_function('incrementAdViewCount', $fallback, $size);
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
    findAdAndExit('leaderboard.avif', 'image/avif');
}
if ($uri === 'ad/leaderboard.webp') {
    findAdAndExit('leaderboard.webp', 'image/webp');
}
if ($uri === 'ad/leaderboard.jpg') {
    findAdAndExit('leaderboard.jpg', 'image/jpeg');
}
if ($uri === 'ad/banner.avif') {
    findAdAndExit('banner.avif', 'image/avif');
}
if ($uri === 'ad/banner.webp') {
    findAdAndExit('banner.webp', 'image/webp');
}
if ($uri === 'ad/banner.jpg') {
    findAdAndExit('banner.jpg', 'image/jpeg');
}
if ($uri === 'ad/mobile.avif') {
    findAdAndExit('mobile.avif', 'image/avif');
}
if ($uri === 'ad/mobile.webp') {
    findAdAndExit('mobile.webp', 'image/webp');
}
if ($uri === 'ad/mobile.jpg') {
    findAdAndExit('mobile.jpg', 'image/jpeg');
}
if ($uri === 'ad.lnk') {
    findAdAndExit('link.txt', 'txt/plain');
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
    $basePath = ROOT_DIR . '/output/' . ($viewPath !== '' ? $viewPath . '/' : '');
    $viewFile = $basePath . 'viewcount.txt';
    $uniqueFile = $basePath . 'unique-viewcount.txt';
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode([
        'views' => is_file($viewFile) ? (int)file_get_contents($viewFile) : 0,
        'unique' => is_file($uniqueFile) ? (int)file_get_contents($uniqueFile) : 0,
    ]);
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
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Y-m-d');
    $identity = md5($ip . $userAgent) . $date;
    $votesFile = $basePath . 'votes.json';
    $fp = fopen($votesFile, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        $contents = stream_get_contents($fp);
        $votes = $contents !== '' ? json_decode($contents, true) : [];
        if (!is_array($votes)) {
            $votes = [];
        }
        $votes[$identity] = $direction;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($votes));
        flock($fp, LOCK_UN);
        fclose($fp);
        $up = 0;
        $down = 0;
        foreach ($votes as $v) {
            if ($v === 1) {
                $up++;
            } else {
                $down++;
            }
        }
        $ratingFile = $basePath . 'rating.txt';
        $rfp = fopen($ratingFile, 'c+');
        if ($rfp && flock($rfp, LOCK_EX)) {
            ftruncate($rfp, 0);
            rewind($rfp);
            fwrite($rfp, (string)($up - $down));
            flock($rfp, LOCK_UN);
        }
        if ($rfp) {
            fclose($rfp);
        }
        header('Content-type: application/json');
        echo json_encode(['up' => $up, 'down' => $down, 'rating' => $up - $down]);
        exit;
    }
    if ($fp) {
        fclose($fp);
    }
    header('Content-type: application/json', true, 500);
    echo json_encode(['error' => 'failed']);
    exit;
}
if ($uri === 'votes' || str_starts_with($uri, 'votes/')) {
    $votePath = trim(substr($uri, 5), '/');
    $basePath = ROOT_DIR . '/output/' . ($votePath !== '' ? $votePath . '/' : '');
    $votesFile = $basePath . 'votes.json';
    $up = 0;
    $down = 0;
    if (is_file($votesFile)) {
        $votes = json_decode(file_get_contents($votesFile), true);
        if (is_array($votes)) {
            foreach ($votes as $v) {
                if ($v === 1) {
                    $up++;
                } else {
                    $down++;
                }
            }
        }
    }
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode(['up' => $up, 'down' => $down, 'rating' => $up - $down]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^readtime/(.+)$#', $uri, $rtm)) {
    $rtPath = $rtm[1];
    $body = trim(file_get_contents('php://input'));
    $seconds = (int)$body;
    if ($seconds < 5 || $seconds > 3600) {
        header('Content-type: application/json', true, 400);
        echo json_encode(['error' => 'seconds must be between 5 and 3600']);
        exit;
    }
    $basePath = ROOT_DIR . '/output/' . $rtPath . '/';
    if (!is_dir($basePath)) {
        header('Content-type: application/json', true, 404);
        echo json_encode(['error' => 'not found']);
        exit;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Y-m-d');
    $hash = md5($ip . $userAgent . $date);
    $rtFile = $basePath . 'readtime.json';
    $fp = fopen($rtFile, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        $contents = stream_get_contents($fp);
        $data = $contents !== '' ? json_decode($contents, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $prev = $data[$hash] ?? 0;
        $data[$hash] = max($prev, $seconds);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));
        flock($fp, LOCK_UN);
        fclose($fp);
        header('Content-type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($fp) {
        fclose($fp);
    }
    header('Content-type: application/json', true, 500);
    echo json_encode(['error' => 'failed']);
    exit;
}
if ($uri === 'readtime' || str_starts_with($uri, 'readtime/')) {
    $rtPath = trim(substr($uri, 8), '/');
    $basePath = ROOT_DIR . '/output/' . ($rtPath !== '' ? $rtPath . '/' : '');
    $rtFile = $basePath . 'readtime.json';
    $sessions = 0;
    $average = 0;
    if (is_file($rtFile)) {
        $data = json_decode(file_get_contents($rtFile), true);
        if (is_array($data) && count($data) > 0) {
            $sessions = count($data);
            $average = round(array_sum($data) / $sessions);
        }
    }
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode(['sessions' => $sessions, 'average' => $average]);
    exit;
}
if ($uri === 'lang-stats') {
    $dir = ROOT_DIR . '/output/lang-stats';
    $result = [];
    if (is_dir($dir)) {
        foreach (glob($dir . '/count-*.txt') as $file) {
            $basename = basename($file, '.txt');
            if (preg_match('/^count-(\d{4}-\d{2})-(\w{2})$/', $basename, $m)) {
                $month = $m[1];
                $lang = $m[2];
                if (!isset($result[$month])) {
                    $result[$month] = array_fill_keys($supportedLanguages, 0);
                }
                $result[$month][$lang] = (int)file_get_contents($file);
            }
        }
        krsort($result);
    }
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($result);
    exit;
}
if ($uri === 'ad-stats') {
    $result = [];
    foreach (glob(ROOT_DIR . '/ads/*', GLOB_ONLYDIR) as $adDir) {
        $month = basename($adDir);
        $entry = ['month' => $month];
        foreach (['leaderboard', 'banner', 'mobile'] as $size) {
            $file = $adDir . '/viewed-' . $size . '.txt';
            $entry[$size] = is_file($file) ? (int)file_get_contents($file) : 0;
        }
        $uniqueFile = $adDir . '/unique-viewed.txt';
        $entry['unique'] = is_file($uniqueFile) ? (int)file_get_contents($uniqueFile) : 0;
        $result[] = $entry;
    }
    usort($result, function ($a, $b) { return strcmp($b['month'], $a['month']); });
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    echo json_encode($result);
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
    findAndExit($uri, $language, !in_array($uri, ['imprint', 'thank-you', 'statistics', 'canceled'], true));
}
header('Content-type: text/html; charset=utf-8', true, 404);
findAndExit('404', $language, false);
