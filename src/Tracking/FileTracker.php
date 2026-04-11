<?php

declare(strict_types=1);

namespace De\Idrinth\Blog\Tracking;

class FileTracker implements TrackerInterface
{
    private string $outputDir;
    private string $adsDir;

    public function __construct(string $outputDir, string $adsDir)
    {
        $this->outputDir = $outputDir;
        $this->adsDir = $adsDir;
    }

    private function incrementFile(string $filePath): void
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

    private function trackUniqueVisitor(string $visitorsFile, string $uniqueCounterFile, string $visitorHash): void
    {
        $fp = fopen($visitorsFile, 'c+');
        if (!$fp || !flock($fp, LOCK_EX)) {
            if ($fp) {
                fclose($fp);
            }
            return;
        }
        $contents = stream_get_contents($fp);
        $visitors = $contents !== '' ? explode("\n", trim($contents)) : [];
        if (in_array($visitorHash, $visitors, true)) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return;
        }
        fseek($fp, 0, SEEK_END);
        fwrite($fp, $visitorHash . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
        $this->incrementFile($uniqueCounterFile);
    }

    public function incrementPageView(string $path, string $visitorHash): void
    {
        $basePath = $this->outputDir . '/' . $path . '/';
        $this->incrementFile($basePath . 'viewcount.txt');
        $this->trackUniqueVisitor(
            $basePath . 'visitors-' . date('Y-m-d') . '.txt',
            $basePath . 'unique-viewcount.txt',
            $visitorHash
        );
    }

    public function getPageViews(string $path): array
    {
        $basePath = $this->outputDir . '/' . ($path !== '' ? $path . '/' : '');
        $viewFile = $basePath . 'viewcount.txt';
        $uniqueFile = $basePath . 'unique-viewcount.txt';
        return [
            'views' => is_file($viewFile) ? (int)file_get_contents($viewFile) : 0,
            'unique' => is_file($uniqueFile) ? (int)file_get_contents($uniqueFile) : 0,
        ];
    }

    public function trackLanguageVisitor(string $language, string $month, string $visitorHash): void
    {
        $dir = $this->outputDir . '/lang-stats';
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
        if (in_array($visitorHash, $visitors, true)) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return;
        }
        fseek($fp, 0, SEEK_END);
        fwrite($fp, $visitorHash . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
        $this->incrementFile($counterFile);
    }

    public function getLanguageStats(array $supportedLanguages): array
    {
        $dir = $this->outputDir . '/lang-stats';
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
        return $result;
    }

    public function incrementAdView(string $month, string $size, string $visitorHash): void
    {
        $adDir = $this->adsDir . '/' . $month;
        $this->incrementFile($adDir . '/viewed-' . $size . '.txt');
        $this->trackUniqueVisitor(
            $adDir . '/ad-visitors-' . date('Y-m-d') . '.txt',
            $adDir . '/unique-viewed.txt',
            $visitorHash
        );
    }

    public function getAdStats(): array
    {
        $result = [];
        foreach (glob($this->adsDir . '/*', GLOB_ONLYDIR) as $adDir) {
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
        return $result;
    }

    public function vote(string $path, string $identity, int $direction): array
    {
        $basePath = $this->outputDir . '/' . $path . '/';
        $votesFile = $basePath . 'votes.json';
        $fp = fopen($votesFile, 'c+');
        if (!$fp || !flock($fp, LOCK_EX)) {
            if ($fp) {
                fclose($fp);
            }
            throw new \RuntimeException('Failed to acquire lock for voting');
        }
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
        return ['up' => $up, 'down' => $down, 'rating' => $up - $down];
    }

    public function getVotes(string $path): array
    {
        $basePath = $this->outputDir . '/' . ($path !== '' ? $path . '/' : '');
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
        return ['up' => $up, 'down' => $down, 'rating' => $up - $down];
    }

    public function trackReadTime(string $path, string $sessionKey, int $seconds): bool
    {
        $basePath = $this->outputDir . '/' . $path . '/';
        $rtFile = $basePath . 'readtime.json';
        $fp = fopen($rtFile, 'c+');
        if (!$fp || !flock($fp, LOCK_EX)) {
            if ($fp) {
                fclose($fp);
            }
            return false;
        }
        $contents = stream_get_contents($fp);
        $data = $contents !== '' ? json_decode($contents, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $prev = $data[$sessionKey] ?? 0;
        $data[$sessionKey] = max($prev, $seconds);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    public function getReadTime(string $path): array
    {
        $basePath = $this->outputDir . '/' . ($path !== '' ? $path . '/' : '');
        $rtFile = $basePath . 'readtime.json';
        $sessions = 0;
        $average = 0;
        if (is_file($rtFile)) {
            $fp = fopen($rtFile, 'r');
            if ($fp && flock($fp, LOCK_SH)) {
                $contents = stream_get_contents($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                $data = $contents !== '' ? json_decode($contents, true) : null;
                if (is_array($data) && count($data) > 0) {
                    $sessions = count($data);
                    $average = (int)round(array_sum($data) / $sessions);
                }
            } elseif ($fp) {
                fclose($fp);
            }
        }
        return ['sessions' => $sessions, 'average' => $average];
    }
}
