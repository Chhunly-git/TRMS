<?php

namespace App\Services;

use App\Models\InboundDocumentResponse;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class FileUrlService
{
    public const PLACEHOLDER_SRC = '/img/signature-placeholder.png';

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
     * Validate that a path is strictly an authorized signature path under users/signatures/.
     */
    public static function isValidSignaturePath(string $path): bool
    {
        $clean = trim($path, '/');

        // Check for URL encoding tricks (%2e = ., %2f = /, %5c = \)
        $lower = strtolower($clean);
        if (str_contains($lower, '%2e') || str_contains($lower, '%2f') || str_contains($lower, '%5c')) {
            return false;
        }

        if (str_contains($clean, '..') || str_contains($clean, '\\') || str_starts_with($clean, '/')) {
            return false;
        }

        if (!str_starts_with($clean, 'users/signatures/')) {
            return false;
        }

        // Only allow safe alphanumeric and standard file characters
        $subPath = substr($clean, strlen('users/signatures/'));
        return (bool) preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $subPath);
    }

    /**
     * Extract a relative file path from a signed URL, legacy bucket URL, or raw string.
     */
    public static function extractRelativePath(string $rawUrl): ?string
    {
        $trimmed = trim($rawUrl);
        if (empty($trimmed)) {
            return null;
        }

        // 1. Check for /api/files?path=...
        if (preg_match('/\/api\/files\?[^"\']*path=([^&"\'\s>]+)/i', $trimmed, $m)) {
            $extracted = urldecode($m[1]);
            return trim($extracted, '/');
        }

        // 2. Check for legacy bucket URL: .../trms-bucket/<path>
        if (preg_match('/(?:\/trms-bucket\/|https?:\/\/[^\/"\'>\s]+\/trms-bucket\/)([a-zA-Z0-9_\-\.\/]+)/i', $trimmed, $m)) {
            return trim($m[1], '/');
        }

        // 3. Raw relative path
        if (str_starts_with($trimmed, 'users/signatures/')) {
            return trim($trimmed, '/');
        }

        return null;
    }

    /**
     * Normalize content HTML before saving to storage:
     * - Rewrites signed URLs and legacy bucket URLs in <img> to stable `data-file-path` attributes.
     * - Replaces `src` with a static placeholder to prevent expiring URLs from being stored.
     * - Strictly strips any `data-file-path` not under `users/signatures/` (e.g. national IDs, passports).
     * - Uses DOMDocument with full Khmer UTF-8 preservation.
     */
    public static function normalizeContentForStorage(?string $content): ?string
    {
        if (empty($content) || !str_contains($content, '<img')) {
            return $content;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        if ($images->length === 0) {
            return $content;
        }

        foreach ($images as $img) {
            $existingFilePath = $img->getAttribute('data-file-path');
            $currentSrc = $img->getAttribute('src');

            $candidatePath = null;
            if (!empty($existingFilePath)) {
                $candidatePath = self::extractRelativePath($existingFilePath) ?: $existingFilePath;
            } elseif (!empty($currentSrc)) {
                $candidatePath = self::extractRelativePath($currentSrc);
            }

            if ($candidatePath !== null) {
                if (self::isValidSignaturePath($candidatePath)) {
                    $img->setAttribute('data-file-path', $candidatePath);
                    $img->setAttribute('src', self::PLACEHOLDER_SRC);
                } else {
                    // Attempt to store a non-signature path (e.g. ID card, passport) -> strip data-file-path
                    $img->removeAttribute('data-file-path');
                    $img->setAttribute('src', self::PLACEHOLDER_SRC);
                    Log::warning('Security: Stripped unauthorized data-file-path during document storage normalization', [
                        'candidate_path' => $candidatePath,
                    ]);
                }
            }
        }

        $saved = $dom->saveHTML();
        $clean = preg_replace('/^<\?xml[^>]+>/i', '', $saved);
        return html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Expand content HTML for render (show, revisions, print):
     * - Fills `src` with a fresh 30-minute signed URL for authorized signatures.
     * - Authorization guard: May sign a path ONLY if it is under `users/signatures/` AND
     *   belongs to the drafter or an approver on THIS specific response document.
     * - If unauthorized or missing, leaves `src` pointing to placeholder and logs it.
     */
    public static function expandContentForRender(?string $content, ?InboundDocumentResponse $response = null): ?string
    {
        if (empty($content) || !str_contains($content, '<img')) {
            return $content;
        }

        // Collect authorized signature paths for this document
        $allowedSignaturePaths = [];
        if ($response) {
            $userIds = array_filter(array_unique(array_merge(
                [$response->drafted_by, $response->current_approver_id, $response->approved_by],
                $response->approvals ? $response->approvals->pluck('user_id')->all() : []
            )));

            if (!empty($userIds)) {
                $rawPaths = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->whereNotNull('signature_image')
                    ->pluck('signature_image')
                    ->all();

                foreach ($rawPaths as $rp) {
                    $clean = trim(str_replace('\\', '/', $rp), '/');
                    if (!empty($clean)) {
                        $allowedSignaturePaths[] = $clean;
                    }
                }
            }
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        if ($images->length === 0) {
            return $content;
        }

        foreach ($images as $img) {
            $filePath = $img->getAttribute('data-file-path');
            $src = $img->getAttribute('src');

            $candidatePath = null;
            if (!empty($filePath)) {
                $candidatePath = $filePath;
            } elseif (!empty($src)) {
                $candidatePath = self::extractRelativePath($src);
            }

            if (!$candidatePath || !self::isValidSignaturePath($candidatePath)) {
                // If there's an illegal path in data-file-path, drop it
                if (!empty($filePath)) {
                    $img->removeAttribute('data-file-path');
                    $img->setAttribute('src', self::PLACEHOLDER_SRC);
                    Log::warning('Security: Blocked non-signature file path during render', [
                        'candidate_path' => $candidatePath,
                        'response_id' => $response?->id,
                    ]);
                }
                continue;
            }

            // Check participant authorization: signature must belong to drafter or approver on this doc
            $isAuthorized = false;
            if ($response) {
                $isAuthorized = in_array($candidatePath, $allowedSignaturePaths, true);
            } else {
                // If no response context provided, allow only valid signature paths
                $isAuthorized = true;
            }

            if ($isAuthorized) {
                $signedUrl = self::signed($candidatePath, 30);
                $img->setAttribute('src', $signedUrl);
                $img->setAttribute('data-file-path', $candidatePath);
            } else {
                $img->setAttribute('src', self::PLACEHOLDER_SRC);
                $img->removeAttribute('data-file-path');
                Log::warning('Security: Blocked signature file path not belonging to document participants', [
                    'candidate_path' => $candidatePath,
                    'response_id' => $response?->id,
                    'allowed_signatures' => $allowedSignaturePaths,
                ]);
            }
        }

        $saved = $dom->saveHTML();
        $clean = preg_replace('/^<\?xml[^>]+>/i', '', $saved);
        return html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Backward-compatible legacy content rewrite helper.
     */
    public static function rewriteLegacyContentUrls(?string $content): ?string
    {
        return self::expandContentForRender($content);
    }
}
