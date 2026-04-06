<?php

define("ROOT_DIR", dirname(__DIR__));

$outputDir = ROOT_DIR . '/output';

foreach (glob($outputDir . '/*viewcount.txt') as $file) {
    $basename = basename($file, 'viewcount.txt');
    if ($basename === '') {
        // This is the root viewcount.txt, skip it
        continue;
    }
    $folderViewcount = $outputDir . '/' . $basename . '/viewcount.txt';
    if (!is_file($folderViewcount)) {
        echo "Skipping $file: target $folderViewcount does not exist\n";
        continue;
    }

    $oldCount = (int) file_get_contents($file);

    $fp = fopen($folderViewcount, 'c+');
    if (!$fp) {
        echo "Failed to open $folderViewcount\n";
        continue;
    }
    if (!flock($fp, LOCK_EX)) {
        echo "Failed to lock $folderViewcount\n";
        fclose($fp);
        continue;
    }

    $currentCount = (int) stream_get_contents($fp);
    $newCount = $currentCount + $oldCount;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) $newCount);
    fflush($fp);

    flock($fp, LOCK_UN);
    fclose($fp);

    unlink($file);

    echo "Merged $basename: $currentCount + $oldCount = $newCount\n";
}
