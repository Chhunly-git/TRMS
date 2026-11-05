<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class FileUrlService
{
    /**
     * Whitelist prefixes for private files allowed to be served via signed route.
     */
    public const ALLOWED_PREFIXES = [
        'users/profile-images/',
        'users/thumbnails/',
        'users/signatures/',
        'users/national-ids/',
        'users/passports/',
        'users/education/',
        'documents/',
        'responses/',
        'templates/',
    ];

    /**
     * Generate a temporary signed route for a private file.
     * Expiry is rounded up to the next 30-minute boundary to keep URLs stable for browser caching.
     */
    public static function signed(string $path, int $minutes = 30): string
    {
        $cleanPath = trim($path, '/');

        // Round expiry up to the next 30-minute boundary (1800 seconds)
        $windowSeconds = 1800;
        $targetTimestamp = now()->timestamp + ($minutes * 60);
        $expiryTimestamp = (int) (ceil($targetTimestamp / $windowSeconds) * $windowSeconds);
        $expiry = Carbon::createFromTimestamp($expiryTimestamp);

        return URL::temporarySignedRoute(
            'files.show',
            $expiry,
            ['path' => $cleanPath]
        );
    }

    /**
     * Validate whether a file path is safe and matches whitelisted prefixes.
     */
    public static function isValidPath(string $path): bool
    {
        $cleanPath = trim($path, '/');

        // Reject directory traversal or path manipulation
        if (str_contains($cleanPath, '..') || str_contains($cleanPath, '\\')) {
            return false;
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($cleanPath, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rewrite legacy bucket URLs in HTML content to temporary signed routes at render time.
     */
    public static function rewriteLegacyContentUrls(?string $content): ?string
    {
        if (empty($content)) {
            return $content;
        }

        // Match URLs containing /trms-bucket/<path>
        // Examples:
        // http://localhost:9000/trms-bucket/users/signatures/sig_123.png
        // /trms-bucket/users/signatures/sig_123.png
        // http://minio-service:9000/trms-bucket/...
        $pattern = '/(?:https?:\/\/[^\/"\'>\s]+)?\/trms-bucket\/([a-zA-Z0-9_\-\.\/]+)/i';

        return preg_replace_callback($pattern, function ($matches) {
            $rawPath = $matches[1];
            if (self::isValidPath($rawPath)) {
                return self::signed($rawPath, 30);
            }
            return $matches[0];
        }, $content);
    }
}
