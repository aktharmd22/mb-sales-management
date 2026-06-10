<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Best-effort fetch of a page's preview (thumbnail) image from its
 * Open Graph / Twitter card meta tags.
 */
class LinkPreview
{
    public static function image(?string $url): ?string
    {
        if (! $url || ! str_starts_with($url, 'http')) {
            return null;
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; MalayznbeatBot/1.0; +https://sales.malayznbeat.com)'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            $patterns = [
                '/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image(?::secure_url)?["\']/i',
                '/<meta[^>]+name=["\']twitter:image(?::src)?["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']twitter:image(?::src)?["\']/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $m)) {
                    return self::absolutize(html_entity_decode(trim($m[1])), $url);
                }
            }
        } catch (\Throwable) {
            // Network/parse failure — no preview, that's fine.
        }

        return null;
    }

    /** Turn a possibly-relative image URL into an absolute one. */
    private static function absolutize(string $image, string $base): ?string
    {
        if ($image === '') {
            return null;
        }
        if (str_starts_with($image, 'http')) {
            return $image;
        }

        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        if ($host === '') {
            return null;
        }
        if (str_starts_with($image, '//')) {
            return $scheme . ':' . $image;
        }
        if (str_starts_with($image, '/')) {
            return $scheme . '://' . $host . $image;
        }

        return $scheme . '://' . $host . '/' . ltrim($image, '/');
    }
}
