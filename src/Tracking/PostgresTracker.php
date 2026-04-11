<?php

declare(strict_types=1);

namespace De\Idrinth\Blog\Tracking;

class PostgresTracker implements TrackerInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createSchema(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS page_views (
            path VARCHAR(500) NOT NULL,
            total_views INT NOT NULL DEFAULT 0,
            unique_views INT NOT NULL DEFAULT 0,
            PRIMARY KEY (path)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS page_visitors (
            path VARCHAR(500) NOT NULL,
            visitor_hash CHAR(32) NOT NULL,
            PRIMARY KEY (path, visitor_hash)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS language_stats (
            language VARCHAR(5) NOT NULL,
            month CHAR(7) NOT NULL,
            visit_count INT NOT NULL DEFAULT 0,
            PRIMARY KEY (language, month)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS language_visitors (
            language VARCHAR(5) NOT NULL,
            month CHAR(7) NOT NULL,
            visitor_hash CHAR(32) NOT NULL,
            PRIMARY KEY (language, month, visitor_hash)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS ad_views (
            month CHAR(7) NOT NULL,
            size VARCHAR(20) NOT NULL,
            view_count INT NOT NULL DEFAULT 0,
            PRIMARY KEY (month, size)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS ad_visitors (
            month CHAR(7) NOT NULL,
            visitor_hash CHAR(32) NOT NULL,
            PRIMARY KEY (month, visitor_hash)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS votes (
            path VARCHAR(500) NOT NULL,
            identity VARCHAR(100) NOT NULL,
            direction SMALLINT NOT NULL,
            PRIMARY KEY (path, identity)
        )');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS read_times (
            path VARCHAR(500) NOT NULL,
            session_key VARCHAR(100) NOT NULL,
            seconds INT NOT NULL DEFAULT 0,
            PRIMARY KEY (path, session_key)
        )');
    }

    public function incrementPageView(string $path, string $visitorHash): void
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                'INSERT INTO page_views (path, total_views, unique_views) VALUES (?, 1, 0)
                ON CONFLICT (path) DO UPDATE SET total_views = page_views.total_views + 1'
            );
            $stmt->execute([$path]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO page_visitors (path, visitor_hash) VALUES (?, ?)
                ON CONFLICT (path, visitor_hash) DO NOTHING'
            );
            $stmt->execute([$path, $visitorHash]);
            if ($stmt->rowCount() > 0) {
                $stmt = $this->pdo->prepare('UPDATE page_views SET unique_views = unique_views + 1 WHERE path = ?');
                $stmt->execute([$path]);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Tracking error: ' . $e->getMessage());
        }
    }

    public function getPageViews(string $path): array
    {
        $stmt = $this->pdo->prepare('SELECT total_views, unique_views FROM page_views WHERE path = ?');
        $stmt->execute([$path]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return ['views' => (int)$row['total_views'], 'unique' => (int)$row['unique_views']];
        }
        return ['views' => 0, 'unique' => 0];
    }

    public function trackLanguageVisitor(string $language, string $month, string $visitorHash): void
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                'INSERT INTO language_visitors (language, month, visitor_hash) VALUES (?, ?, ?)
                ON CONFLICT (language, month, visitor_hash) DO NOTHING'
            );
            $stmt->execute([$language, $month, $visitorHash]);
            if ($stmt->rowCount() > 0) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO language_stats (language, month, visit_count) VALUES (?, ?, 1)
                    ON CONFLICT (language, month) DO UPDATE SET visit_count = language_stats.visit_count + 1'
                );
                $stmt->execute([$language, $month]);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Tracking error: ' . $e->getMessage());
        }
    }

    public function getLanguageStats(array $supportedLanguages): array
    {
        $stmt = $this->pdo->query('SELECT language, month, visit_count FROM language_stats ORDER BY month DESC');
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (!isset($result[$row['month']])) {
                $result[$row['month']] = array_fill_keys($supportedLanguages, 0);
            }
            $result[$row['month']][$row['language']] = (int)$row['visit_count'];
        }
        krsort($result);
        return $result;
    }

    public function incrementAdView(string $month, string $size, string $visitorHash): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO ad_views (month, size, view_count) VALUES (?, ?, 1)
                ON CONFLICT (month, size) DO UPDATE SET view_count = ad_views.view_count + 1'
            );
            $stmt->execute([$month, $size]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO ad_visitors (month, visitor_hash) VALUES (?, ?)
                ON CONFLICT (month, visitor_hash) DO NOTHING'
            );
            $stmt->execute([$month, $visitorHash]);
        } catch (\PDOException $e) {
            error_log('Tracking error: ' . $e->getMessage());
        }
    }

    public function getAdStats(): array
    {
        $viewsByMonth = [];
        $stmt = $this->pdo->query('SELECT month, size, view_count FROM ad_views ORDER BY month DESC');
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $viewsByMonth[$row['month']][$row['size']] = (int)$row['view_count'];
        }
        $uniqueByMonth = [];
        $stmt = $this->pdo->query('SELECT month, COUNT(*) as cnt FROM ad_visitors GROUP BY month');
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $uniqueByMonth[$row['month']] = (int)$row['cnt'];
        }
        $allMonths = array_unique(array_merge(array_keys($viewsByMonth), array_keys($uniqueByMonth)));
        rsort($allMonths);
        $result = [];
        foreach ($allMonths as $month) {
            $result[] = [
                'month' => $month,
                'leaderboard' => $viewsByMonth[$month]['leaderboard'] ?? 0,
                'banner' => $viewsByMonth[$month]['banner'] ?? 0,
                'mobile' => $viewsByMonth[$month]['mobile'] ?? 0,
                'unique' => $uniqueByMonth[$month] ?? 0,
            ];
        }
        return $result;
    }

    public function vote(string $path, string $identity, int $direction): array
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                'INSERT INTO votes (path, identity, direction) VALUES (?, ?, ?)
                ON CONFLICT (path, identity) DO UPDATE SET direction = EXCLUDED.direction'
            );
            $stmt->execute([$path, $identity, $direction]);
            $stmt = $this->pdo->prepare(
                'SELECT
                    COALESCE(SUM(CASE WHEN direction = 1 THEN 1 ELSE 0 END), 0) as up_count,
                    COALESCE(SUM(CASE WHEN direction = -1 THEN 1 ELSE 0 END), 0) as down_count
                FROM votes WHERE path = ?'
            );
            $stmt->execute([$path]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $this->pdo->commit();
            $up = (int)$row['up_count'];
            $down = (int)$row['down_count'];
            return ['up' => $up, 'down' => $down, 'rating' => $up - $down];
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \RuntimeException('Vote failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getVotes(string $path): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN direction = 1 THEN 1 ELSE 0 END), 0) as up_count,
                COALESCE(SUM(CASE WHEN direction = -1 THEN 1 ELSE 0 END), 0) as down_count
            FROM votes WHERE path = ?'
        );
        $stmt->execute([$path]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $up = (int)($row['up_count'] ?? 0);
        $down = (int)($row['down_count'] ?? 0);
        return ['up' => $up, 'down' => $down, 'rating' => $up - $down];
    }

    public function trackReadTime(string $path, string $sessionKey, int $seconds): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO read_times (path, session_key, seconds) VALUES (?, ?, ?)
                ON CONFLICT (path, session_key) DO UPDATE SET seconds = GREATEST(read_times.seconds, EXCLUDED.seconds)'
            );
            $stmt->execute([$path, $sessionKey, $seconds]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getReadTime(string $path): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as sessions, COALESCE(ROUND(AVG(seconds)), 0) as average
            FROM read_times WHERE path = ?'
        );
        $stmt->execute([$path]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'sessions' => (int)($row['sessions'] ?? 0),
            'average' => (int)($row['average'] ?? 0),
        ];
    }
}
