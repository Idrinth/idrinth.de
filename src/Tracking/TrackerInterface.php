<?php

declare(strict_types=1);

namespace De\Idrinth\Blog\Tracking;

interface TrackerInterface
{
    public function incrementPageView(string $path, string $visitorHash, bool $isBot = false): void;

    /**
     * @return array{views: int, unique: int, bot_views: int, bot_unique: int}
     */
    public function getPageViews(string $path): array;

    public function trackLanguageVisitor(string $language, string $month, string $visitorHash, bool $isBot = false): void;

    /**
     * @param string[] $supportedLanguages
     * @return array<string, array<string, int>>
     */
    public function getLanguageStats(array $supportedLanguages): array;

    public function incrementAdView(string $month, string $size, string $visitorHash): void;

    /**
     * @return list<array{month: string, leaderboard: int, banner: int, mobile: int, unique: int}>
     */
    public function getAdStats(): array;

    /**
     * @return array{up: int, down: int, rating: int}
     * @throws \RuntimeException
     */
    public function vote(string $path, string $identity, int $direction): array;

    /**
     * @return array{up: int, down: int, rating: int}
     */
    public function getVotes(string $path): array;

    public function trackReadTime(string $path, string $sessionKey, int $seconds): bool;

    /**
     * @return array{sessions: int, average: int}
     */
    public function getReadTime(string $path): array;
}
