<?php

/**
 * Migrates file-system tracking data to the configured database.
 *
 * Reads all tracking files (view counts, visitor hashes, votes, read times,
 * language stats, ad stats), imports them into the database configured in
 * config/config.php, and removes the source files on success.
 *
 * Usage: php bin/file-to-database.php
 */

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

use De\Idrinth\Blog\Tracking\MariaDbTracker;
use De\Idrinth\Blog\Tracking\PostgresTracker;
use De\Idrinth\Blog\Tracking\SqliteTracker;

$outputDir = ROOT_DIR . '/output';
$adsDir = ROOT_DIR . '/ads';

// --- Load database configuration ---

$configFile = ROOT_DIR . '/config/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Error: config/config.php not found. Database configuration is required.\n");
    exit(1);
}
$config = require $configFile;
if (!is_array($config) || !isset($config['driver'])) {
    fwrite(STDERR, "Error: config/config.php must return an array with a 'driver' key.\n");
    exit(1);
}

// --- Connect to database and ensure schema ---

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
            $driver = 'mysql';
            (new MariaDbTracker($pdo))->createSchema();
            break;
        case 'postgres':
        case 'pgsql':
            $dsn = 'pgsql:host=' . ($config['host'] ?? '127.0.0.1')
                . ';port=' . ($config['port'] ?? 5432)
                . ';dbname=' . ($config['database'] ?? 'idrinth_blog');
            $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $driver = 'postgres';
            (new PostgresTracker($pdo))->createSchema();
            break;
        case 'sqlite':
            $path = $config['path'] ?? ROOT_DIR . '/config/tracking.sqlite';
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $driver = 'sqlite';
            (new SqliteTracker($pdo))->createSchema();
            break;
        default:
            fwrite(STDERR, "Error: Unsupported driver '{$config['driver']}'.\n");
            exit(1);
    }
} catch (PDOException $e) {
    fwrite(STDERR, "Error: Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Connected to {$config['driver']} database. Schema ensured.\n";

// --- Helper ---

function readFileLocked(string $path): string
{
    if (!is_file($path)) {
        return '';
    }
    $fp = fopen($path, 'r');
    if (!$fp || !flock($fp, LOCK_SH)) {
        if ($fp) {
            fclose($fp);
        }
        return '';
    }
    $contents = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $contents !== false ? $contents : '';
}

// --- Prepare SQL statements ---

$isMySQL = ($driver === 'mysql');

if ($isMySQL) {
    $stmtPageViews = $pdo->prepare(
        'INSERT INTO page_views (path, total_views, unique_views) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE total_views = VALUES(total_views), unique_views = VALUES(unique_views)'
    );
    $stmtPageVisitor = $pdo->prepare(
        'INSERT IGNORE INTO page_visitors (path, visitor_hash) VALUES (?, ?)'
    );
    $stmtVote = $pdo->prepare(
        'INSERT INTO votes (path, identity, direction) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE direction = VALUES(direction)'
    );
    $stmtReadTime = $pdo->prepare(
        'INSERT INTO read_times (path, session_key, seconds) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE seconds = GREATEST(seconds, VALUES(seconds))'
    );
    $stmtLangStats = $pdo->prepare(
        'INSERT INTO language_stats (language, month, visit_count) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE visit_count = VALUES(visit_count)'
    );
    $stmtLangVisitor = $pdo->prepare(
        'INSERT IGNORE INTO language_visitors (language, month, visitor_hash) VALUES (?, ?, ?)'
    );
    $stmtAdViews = $pdo->prepare(
        'INSERT INTO ad_views (month, size, view_count) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE view_count = VALUES(view_count)'
    );
    $stmtAdVisitor = $pdo->prepare(
        'INSERT IGNORE INTO ad_visitors (month, visitor_hash) VALUES (?, ?)'
    );
} elseif ($driver === 'postgres') {
    $stmtPageViews = $pdo->prepare(
        'INSERT INTO page_views (path, total_views, unique_views) VALUES (?, ?, ?)
         ON CONFLICT (path) DO UPDATE SET total_views = EXCLUDED.total_views, unique_views = EXCLUDED.unique_views'
    );
    $stmtPageVisitor = $pdo->prepare(
        'INSERT INTO page_visitors (path, visitor_hash) VALUES (?, ?)
         ON CONFLICT (path, visitor_hash) DO NOTHING'
    );
    $stmtVote = $pdo->prepare(
        'INSERT INTO votes (path, identity, direction) VALUES (?, ?, ?)
         ON CONFLICT (path, identity) DO UPDATE SET direction = EXCLUDED.direction'
    );
    $stmtReadTime = $pdo->prepare(
        'INSERT INTO read_times (path, session_key, seconds) VALUES (?, ?, ?)
         ON CONFLICT (path, session_key) DO UPDATE SET seconds = GREATEST(read_times.seconds, EXCLUDED.seconds)'
    );
    $stmtLangStats = $pdo->prepare(
        'INSERT INTO language_stats (language, month, visit_count) VALUES (?, ?, ?)
         ON CONFLICT (language, month) DO UPDATE SET visit_count = EXCLUDED.visit_count'
    );
    $stmtLangVisitor = $pdo->prepare(
        'INSERT INTO language_visitors (language, month, visitor_hash) VALUES (?, ?, ?)
         ON CONFLICT (language, month, visitor_hash) DO NOTHING'
    );
    $stmtAdViews = $pdo->prepare(
        'INSERT INTO ad_views (month, size, view_count) VALUES (?, ?, ?)
         ON CONFLICT (month, size) DO UPDATE SET view_count = EXCLUDED.view_count'
    );
    $stmtAdVisitor = $pdo->prepare(
        'INSERT INTO ad_visitors (month, visitor_hash) VALUES (?, ?)
         ON CONFLICT (month, visitor_hash) DO NOTHING'
    );
} else {
    $stmtPageViews = $pdo->prepare(
        'INSERT INTO page_views (path, total_views, unique_views) VALUES (?, ?, ?)
         ON CONFLICT(path) DO UPDATE SET total_views = excluded.total_views, unique_views = excluded.unique_views'
    );
    $stmtPageVisitor = $pdo->prepare(
        'INSERT INTO page_visitors (path, visitor_hash) VALUES (?, ?)
         ON CONFLICT(path, visitor_hash) DO NOTHING'
    );
    $stmtVote = $pdo->prepare(
        'INSERT INTO votes (path, identity, direction) VALUES (?, ?, ?)
         ON CONFLICT(path, identity) DO UPDATE SET direction = excluded.direction'
    );
    $stmtReadTime = $pdo->prepare(
        'INSERT INTO read_times (path, session_key, seconds) VALUES (?, ?, ?)
         ON CONFLICT(path, session_key) DO UPDATE SET seconds = MAX(read_times.seconds, excluded.seconds)'
    );
    $stmtLangStats = $pdo->prepare(
        'INSERT INTO language_stats (language, month, visit_count) VALUES (?, ?, ?)
         ON CONFLICT(language, month) DO UPDATE SET visit_count = excluded.visit_count'
    );
    $stmtLangVisitor = $pdo->prepare(
        'INSERT INTO language_visitors (language, month, visitor_hash) VALUES (?, ?, ?)
         ON CONFLICT(language, month, visitor_hash) DO NOTHING'
    );
    $stmtAdViews = $pdo->prepare(
        'INSERT INTO ad_views (month, size, view_count) VALUES (?, ?, ?)
         ON CONFLICT(month, size) DO UPDATE SET view_count = excluded.view_count'
    );
    $stmtAdVisitor = $pdo->prepare(
        'INSERT INTO ad_visitors (month, visitor_hash) VALUES (?, ?)
         ON CONFLICT(month, visitor_hash) DO NOTHING'
    );
}

$filesToDelete = [];
$stats = [
    'page_views' => 0,
    'page_visitors' => 0,
    'votes' => 0,
    'read_times' => 0,
    'language_stats' => 0,
    'language_visitors' => 0,
    'ad_views' => 0,
    'ad_visitors' => 0,
];

// --- Phase 1: Import page tracking data ---

echo "\nScanning for page tracking data...\n";

$trackingFileNames = ['viewcount.txt', 'unique-viewcount.txt', 'votes.json', 'readtime.json', 'rating.txt'];
$postDirs = [];

if (is_dir($outputDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $filename = $fileInfo->getFilename();
        $dir = dirname($fileInfo->getPathname());
        $relative = ltrim(substr($dir, strlen($outputDir)), '/');
        if (str_starts_with($relative, 'lang-stats')) {
            continue;
        }
        $isTrackingFile = in_array($filename, $trackingFileNames, true)
            || preg_match('/^visitors-\d{4}-\d{2}-\d{2}\.txt$/', $filename);
        if ($isTrackingFile) {
            $postDirs[$relative] = $dir;
        }
    }
}

echo "Found " . count($postDirs) . " directories with tracking data.\n";

$pdo->beginTransaction();
try {
    foreach ($postDirs as $path => $dir) {
        $viewFile = $dir . '/viewcount.txt';
        $uniqueFile = $dir . '/unique-viewcount.txt';
        if (is_file($viewFile) || is_file($uniqueFile)) {
            $totalViews = (int)readFileLocked($viewFile);
            $uniqueViews = (int)readFileLocked($uniqueFile);
            $stmtPageViews->execute([$path, $totalViews, $uniqueViews]);
            $stats['page_views']++;
            if (is_file($viewFile)) {
                $filesToDelete[] = $viewFile;
            }
            if (is_file($uniqueFile)) {
                $filesToDelete[] = $uniqueFile;
            }
        }

        foreach (glob($dir . '/visitors-*.txt') as $visitorFile) {
            $contents = readFileLocked($visitorFile);
            if ($contents !== '') {
                foreach (explode("\n", trim($contents)) as $hash) {
                    $hash = trim($hash);
                    if ($hash !== '') {
                        $stmtPageVisitor->execute([$path, $hash]);
                        $stats['page_visitors']++;
                    }
                }
            }
            $filesToDelete[] = $visitorFile;
        }

        $votesFile = $dir . '/votes.json';
        if (is_file($votesFile)) {
            $contents = readFileLocked($votesFile);
            if ($contents !== '') {
                $votes = json_decode($contents, true);
                if (is_array($votes)) {
                    foreach ($votes as $identity => $direction) {
                        $stmtVote->execute([$path, (string)$identity, (int)$direction]);
                        $stats['votes']++;
                    }
                }
            }
            $filesToDelete[] = $votesFile;
        }

        $ratingFile = $dir . '/rating.txt';
        if (is_file($ratingFile)) {
            $filesToDelete[] = $ratingFile;
        }

        $readTimeFile = $dir . '/readtime.json';
        if (is_file($readTimeFile)) {
            $contents = readFileLocked($readTimeFile);
            if ($contents !== '') {
                $data = json_decode($contents, true);
                if (is_array($data)) {
                    foreach ($data as $sessionKey => $seconds) {
                        $stmtReadTime->execute([$path, (string)$sessionKey, (int)$seconds]);
                        $stats['read_times']++;
                    }
                }
            }
            $filesToDelete[] = $readTimeFile;
        }
    }
    $pdo->commit();
    echo "Page tracking data imported.\n";
} catch (PDOException $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Error importing page tracking data: " . $e->getMessage() . "\n");
    exit(1);
}

// --- Phase 2: Import language statistics ---

echo "\nImporting language statistics...\n";

$langStatsDir = $outputDir . '/lang-stats';
if (is_dir($langStatsDir)) {
    $pdo->beginTransaction();
    try {
        foreach (glob($langStatsDir . '/count-*.txt') as $file) {
            $basename = basename($file, '.txt');
            if (preg_match('/^count-(\d{4}-\d{2})-(\w+)$/', $basename, $m)) {
                $count = (int)readFileLocked($file);
                $stmtLangStats->execute([$m[2], $m[1], $count]);
                $stats['language_stats']++;
                $filesToDelete[] = $file;
            }
        }
        foreach (glob($langStatsDir . '/visitors-*.txt') as $file) {
            $basename = basename($file, '.txt');
            if (preg_match('/^visitors-(\d{4}-\d{2})-(\w+)$/', $basename, $m)) {
                $contents = readFileLocked($file);
                if ($contents !== '') {
                    foreach (explode("\n", trim($contents)) as $hash) {
                        $hash = trim($hash);
                        if ($hash !== '') {
                            $stmtLangVisitor->execute([$m[2], $m[1], $hash]);
                            $stats['language_visitors']++;
                        }
                    }
                }
                $filesToDelete[] = $file;
            }
        }
        $pdo->commit();
        echo "Language statistics imported.\n";
    } catch (PDOException $e) {
        $pdo->rollBack();
        fwrite(STDERR, "Error importing language statistics: " . $e->getMessage() . "\n");
        exit(1);
    }
} else {
    echo "No language statistics found.\n";
}

// --- Phase 3: Import ad statistics ---

echo "\nImporting ad statistics...\n";

$adMonthDirs = glob($adsDir . '/*', GLOB_ONLYDIR) ?: [];
if (count($adMonthDirs) > 0) {
    $pdo->beginTransaction();
    try {
        foreach ($adMonthDirs as $adDir) {
            $month = basename($adDir);

            foreach (['leaderboard', 'banner', 'mobile'] as $size) {
                $file = $adDir . '/viewed-' . $size . '.txt';
                if (is_file($file)) {
                    $count = (int)readFileLocked($file);
                    $stmtAdViews->execute([$month, $size, $count]);
                    $stats['ad_views']++;
                    $filesToDelete[] = $file;
                }
            }

            $uniqueFile = $adDir . '/unique-viewed.txt';
            if (is_file($uniqueFile)) {
                $filesToDelete[] = $uniqueFile;
            }

            foreach (glob($adDir . '/ad-visitors-*.txt') as $file) {
                $contents = readFileLocked($file);
                if ($contents !== '') {
                    foreach (explode("\n", trim($contents)) as $hash) {
                        $hash = trim($hash);
                        if ($hash !== '') {
                            $stmtAdVisitor->execute([$month, $hash]);
                            $stats['ad_visitors']++;
                        }
                    }
                }
                $filesToDelete[] = $file;
            }
        }
        $pdo->commit();
        echo "Ad statistics imported.\n";
    } catch (PDOException $e) {
        $pdo->rollBack();
        fwrite(STDERR, "Error importing ad statistics: " . $e->getMessage() . "\n");
        exit(1);
    }
} else {
    echo "No ad directories found.\n";
}

// --- Phase 4: Delete source files ---

echo "\nDeleting " . count($filesToDelete) . " tracking files...\n";

$deleted = 0;
$failed = 0;
foreach ($filesToDelete as $file) {
    if (unlink($file)) {
        $deleted++;
    } else {
        fwrite(STDERR, "Warning: Failed to delete $file\n");
        $failed++;
    }
}

if (is_dir($langStatsDir) && count(glob($langStatsDir . '/*') ?: []) === 0) {
    rmdir($langStatsDir);
    echo "Removed empty lang-stats directory.\n";
}

// --- Summary ---

echo "\n--- Migration Summary ---\n";
echo "Page views imported:        {$stats['page_views']}\n";
echo "Page visitors imported:     {$stats['page_visitors']}\n";
echo "Votes imported:             {$stats['votes']}\n";
echo "Read time records imported: {$stats['read_times']}\n";
echo "Language stats imported:    {$stats['language_stats']}\n";
echo "Language visitors imported: {$stats['language_visitors']}\n";
echo "Ad view records imported:   {$stats['ad_views']}\n";
echo "Ad visitors imported:       {$stats['ad_visitors']}\n";
echo "Files deleted:              {$deleted}\n";
if ($failed > 0) {
    echo "Files failed to delete:     {$failed}\n";
}
echo "\nMigration complete.\n";
