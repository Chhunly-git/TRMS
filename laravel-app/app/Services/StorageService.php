<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StorageService
{
    /**
     * Get the configured primary storage disk name (s3 / minio).
     */
    public static function diskName(): string
    {
        return config('filesystems.default', 's3');
    }

    /**
     * Get the primary storage disk instance.
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    /**
     * Resolve the disk where a file actually exists.
     * Checks S3/MinIO first, then falls back to local 'public' storage for backward compatibility.
     */
    public static function diskForPath(?string $path): Filesystem
    {
        $primary = self::diskName();

        if (!empty($path)) {
            try {
                if (Storage::disk($primary)->exists($path)) {
                    return Storage::disk($primary);
                }
            } catch (\Throwable $e) {
                // Ignore and check fallback
            }

            try {
                if (Storage::disk('public')->exists($path)) {
                    return Storage::disk('public');
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return Storage::disk($primary);
    }

    /**
     * Check if a file exists on either MinIO/S3 or local public storage.
     */
    public static function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $primary = self::diskName();

        try {
            if (Storage::disk($primary)->exists($path)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return false;
    }

    /**
     * Store an uploaded file onto MinIO/S3.
     */
    public static function store(UploadedFile $file, string $directory): string|false
    {
        return $file->store($directory, self::diskName());
    }

    /**
     * Store an uploaded file with a given filename onto MinIO/S3.
     */
    public static function storeAs(UploadedFile $file, string $directory, string $filename): string|false
    {
        return $file->storeAs($directory, $filename, self::diskName());
    }

    /**
     * Put raw content or binary into MinIO/S3.
     */
    public static function put(string $path, mixed $contents, mixed $options = []): bool
    {
        return Storage::disk(self::diskName())->put($path, $contents, $options);
    }

    /**
     * Delete a file from MinIO/S3 and local public storage.
     */
    public static function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $deleted = false;
        $primary = self::diskName();

        try {
            if (Storage::disk($primary)->exists($path)) {
                $deleted = Storage::disk($primary)->delete($path) || $deleted;
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                $deleted = Storage::disk('public')->delete($path) || $deleted;
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return $deleted;
    }

    /**
     * Download a file from MinIO/S3 or local storage.
     */
    public static function download(string $path, ?string $name = null, array $headers = []): Response
    {
        $disk = self::diskForPath($path);
        return $disk->download($path, $name, $headers);
    }

    /**
     * Stream a file inline in the browser (e.g. PDF view in iframe/modal).
     */
    public static function response(string $path, ?string $name = null, array $headers = []): Response
    {
        $disk = self::diskForPath($path);
        $mime = $disk->mimeType($path) ?: 'application/pdf';
        $filename = $name ?? basename($path);

        $mergedHeaders = array_merge([
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'X-Frame-Options' => 'ALLOWALL',
        ], $headers);

        return $disk->response($path, $filename, $mergedHeaders, 'inline');
    }

    /**
     * Get the full URL to access the file.
     */
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = self::diskForPath($path);
        return $disk->url($path);
    }
}
