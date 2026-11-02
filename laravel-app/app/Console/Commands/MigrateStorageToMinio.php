<?php

namespace App\Console\Commands;

use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToMinio extends Command
{
    protected $signature = 'storage:migrate-to-minio {--overwrite : Overwrite files that already exist on MinIO}';

    protected $description = 'Migrate all local storage files (storage/app/public) to MinIO S3 object storage';

    public function handle()
    {
        $this->info('Starting migration of local files to MinIO S3...');
        $diskName = StorageService::diskName();
        $this->info("Target Disk: {$diskName} (Bucket: " . config("filesystems.disks.{$diskName}.bucket") . ")");

        $localPublicPath = storage_path('app/public');
        if (!File::isDirectory($localPublicPath)) {
            $this->warn("Local storage directory {$localPublicPath} does not exist.");
            return 0;
        }

        $files = File::allFiles($localPublicPath);
        $total = count($files);
        $this->info("Found {$total} files in storage/app/public.");

        if ($total === 0) {
            $this->info('No files to migrate.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $skipped = 0;
        $failed = 0;
        $overwrite = $this->option('overwrite');

        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            try {
                if (!$overwrite && Storage::disk($diskName)->exists($relativePath)) {
                    $skipped++;
                } else {
                    $stream = fopen($file->getRealPath(), 'r+');
                    Storage::disk($diskName)->put($relativePath, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $migrated++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("\nFailed to upload {$relativePath}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Total Files', 'Migrated to MinIO', 'Already Existed (Skipped)', 'Failed'],
            [[$total, $migrated, $skipped, $failed]]
        );

        $this->info('Migration completed successfully!');
        return 0;
    }
}
