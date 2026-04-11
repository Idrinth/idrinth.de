<?php

declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));

$configFile = ROOT_DIR . '/config/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Error: config/config.php not found. Create it with your database configuration.\n");
    fwrite(STDERR, "Example:\n");
    fwrite(STDERR, "  <?php return ['driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'idrinth_blog', 'username' => 'root', 'password' => ''];\n");
    exit(1);
}

$config = require $configFile;
if (!is_array($config) || !isset($config['driver'])) {
    fwrite(STDERR, "Error: config/config.php must return an array with a 'driver' key.\n");
    fwrite(STDERR, "Supported drivers: mariadb, mysql, postgres, pgsql, sqlite\n");
    exit(1);
}

try {
    switch ($config['driver']) {
        case 'mariadb':
        case 'mysql':
            $dsn = 'mysql:host=' . ($config['host'] ?? '127.0.0.1')
                . ';port=' . ($config['port'] ?? 3306)
                . ';dbname=' . ($config['database'] ?? 'idrinth_blog')
                . ';charset=utf8mb4';
            $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            break;
        case 'postgres':
        case 'pgsql':
            $dsn = 'pgsql:host=' . ($config['host'] ?? '127.0.0.1')
                . ';port=' . ($config['port'] ?? 5432)
                . ';dbname=' . ($config['database'] ?? 'idrinth_blog');
            $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            break;
        case 'sqlite':
            $path = $config['path'] ?? ROOT_DIR . '/config/tracking.sqlite';
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            break;
        default:
            fwrite(STDERR, "Error: Unsupported driver '{$config['driver']}'.\n");
            fwrite(STDERR, "Supported drivers: mariadb, mysql, postgres, pgsql, sqlite\n");
            exit(1);
    }
} catch (PDOException $e) {
    fwrite(STDERR, "Error: Failed to connect to database: {$e->getMessage()}\n");
    exit(1);
}

echo "Connected to {$config['driver']} database.\n";

$outputDir = ROOT_DIR . '/output';
$adsDir = ROOT_DIR . '/ads';

$counters = ['pages' => 0, 'visitors' => 0, 'language' => 0, 'ads' => 0, 'votes' => 0, 'readtimes' => 0];

// --- Page views ---
echo "\nExporting page views...\n";
$stmt = $pdo->query('SELECT path, total_views, unique_views FROM page_views');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dir = $outputDir . '/' . $row['path'];
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/viewcount.txt', (string)(int)$row['total_views'], LOCK_EX);
    file_put_contents($dir . '/unique-viewcount.txt', (string)(int)$row['unique_views'], LOCK_EX);
    $counters['pages']++;
    echo "  {$row['path']}: {$row['total_views']} views, {$row['unique_views']} unique\n";
}
echo "Exported {$counters['pages']} page view records.\n";

// --- Page visitors ---
echo "\nExporting page visitors...\n";
$stmt = $pdo->query('SELECT path, visitor_hash FROM page_visitors ORDER BY path');
$visitorsByPath = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $visitorsByPath[$row['path']][] = $row['visitor_hash'];
}
foreach ($visitorsByPath as $path => $hashes) {
    $dir = $outputDir . '/' . $path;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $dir . '/visitors-migrated.txt',
        implode("\n", $hashes) . "\n",
        LOCK_EX
    );
    $counters['visitors'] += count($hashes);
    echo "  {$path}: " . count($hashes) . " visitor hashes\n";
}
echo "Exported {$counters['visitors']} visitor records.\n";

// --- Language stats ---
echo "\nExporting language stats...\n";
$langStatsDir = $outputDir . '/lang-stats';
if (!is_dir($langStatsDir)) {
    mkdir($langStatsDir, 0755, true);
}
$stmt = $pdo->query('SELECT language, month, visit_count FROM language_stats');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $file = $langStatsDir . '/count-' . $row['month'] . '-' . $row['language'] . '.txt';
    file_put_contents($file, (string)(int)$row['visit_count'], LOCK_EX);
    $counters['language']++;
    echo "  {$row['month']}/{$row['language']}: {$row['visit_count']} visits\n";
}

// --- Language visitors ---
$stmt = $pdo->query('SELECT language, month, visitor_hash FROM language_visitors ORDER BY language, month');
$langVisitors = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = $row['month'] . '-' . $row['language'];
    $langVisitors[$key][] = $row['visitor_hash'];
}
foreach ($langVisitors as $key => $hashes) {
    $file = $langStatsDir . '/visitors-' . $key . '.txt';
    file_put_contents($file, implode("\n", $hashes) . "\n", LOCK_EX);
}
echo "Exported {$counters['language']} language stat records.\n";

// --- Ad views ---
echo "\nExporting ad views...\n";
$stmt = $pdo->query('SELECT month, size, view_count FROM ad_views');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dir = $adsDir . '/' . $row['month'];
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/viewed-' . $row['size'] . '.txt', (string)(int)$row['view_count'], LOCK_EX);
    $counters['ads']++;
    echo "  {$row['month']}/{$row['size']}: {$row['view_count']} views\n";
}

// --- Ad unique visitors ---
$stmt = $pdo->query('SELECT month, COUNT(*) as cnt FROM ad_visitors GROUP BY month');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dir = $adsDir . '/' . $row['month'];
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/unique-viewed.txt', (string)(int)$row['cnt'], LOCK_EX);
}

$stmt = $pdo->query('SELECT month, visitor_hash FROM ad_visitors ORDER BY month');
$adVisitors = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $adVisitors[$row['month']][] = $row['visitor_hash'];
}
foreach ($adVisitors as $month => $hashes) {
    $dir = $adsDir . '/' . $month;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $dir . '/ad-visitors-migrated.txt',
        implode("\n", $hashes) . "\n",
        LOCK_EX
    );
}
echo "Exported {$counters['ads']} ad view records.\n";

// --- Votes ---
echo "\nExporting votes...\n";
$stmt = $pdo->query('SELECT path, identity, direction FROM votes ORDER BY path');
$votesByPath = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $votesByPath[$row['path']][$row['identity']] = (int)$row['direction'];
}
foreach ($votesByPath as $path => $votes) {
    $dir = $outputDir . '/' . $path;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/votes.json', json_encode($votes), LOCK_EX);
    $up = 0;
    $down = 0;
    foreach ($votes as $direction) {
        if ($direction === 1) {
            $up++;
        } else {
            $down++;
        }
    }
    file_put_contents($dir . '/rating.txt', (string)($up - $down), LOCK_EX);
    $counters['votes'] += count($votes);
    echo "  {$path}: " . count($votes) . " votes (up: {$up}, down: {$down})\n";
}
echo "Exported {$counters['votes']} vote records.\n";

// --- Read times ---
echo "\nExporting read times...\n";
$stmt = $pdo->query('SELECT path, session_key, seconds FROM read_times ORDER BY path');
$readTimesByPath = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $readTimesByPath[$row['path']][$row['session_key']] = (int)$row['seconds'];
}
foreach ($readTimesByPath as $path => $sessions) {
    $dir = $outputDir . '/' . $path;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/readtime.json', json_encode($sessions), LOCK_EX);
    $counters['readtimes'] += count($sessions);
    echo "  {$path}: " . count($sessions) . " sessions\n";
}
echo "Exported {$counters['readtimes']} read time records.\n";

// --- Summary ---
echo "\n=== Migration complete ===\n";
echo "Page views:     {$counters['pages']}\n";
echo "Visitors:       {$counters['visitors']}\n";
echo "Language stats: {$counters['language']}\n";
echo "Ad views:       {$counters['ads']}\n";
echo "Votes:          {$counters['votes']}\n";
echo "Read times:     {$counters['readtimes']}\n";
echo "\nDatabase data has been written to the filesystem.\n";
echo "You can now remove the database configuration from config/config.php\n";
echo "to switch to file-based tracking.\n";
