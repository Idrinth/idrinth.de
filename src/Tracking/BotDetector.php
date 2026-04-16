<?php

declare(strict_types=1);

namespace De\Idrinth\Blog\Tracking;

class BotDetector
{
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
        'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck',
        'sogou', 'exabot', 'facebot', 'ia_archiver',
        'semrush', 'ahrefs', 'moz.com', 'majestic',
        'facebookexternalhit', 'twitterbot', 'linkedinbot', 'whatsapp',
        'telegrambot', 'discordbot', 'slackbot',
        'gptbot', 'chatgpt', 'claudebot', 'anthropic', 'ccbot',
        'applebot', 'amazonbot', 'bytespider', 'petalbot',
        'dataforseo', 'dotbot', 'rogerbot', 'screaming frog',
        'uptimerobot', 'pingdom', 'site24x7', 'statuscake',
        'headlesschrome', 'phantomjs', 'wget', 'curl',
        'python-requests', 'python-urllib', 'go-http-client',
        'java/', 'libwww-perl', 'nutch', 'scrapy',
        'httpclient', 'okhttp', 'axios/',
    ];

    public static function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }
        $lower = strtolower($userAgent);
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }
}
