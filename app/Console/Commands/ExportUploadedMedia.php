<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ExportUploadedMedia extends Command
{
    protected $signature = 'media:export {--path= : Destination zip file path}';

    protected $description = 'Export storage/app/public uploaded media for moving the site with its database.';

    public function handle(): int
    {
        $source = storage_path('app/public');
        if (! is_dir($source)) {
            $this->error('Source folder not found: ' . $source);
            return self::FAILURE;
        }

        $destination = $this->option('path') ?: storage_path('app/media-backups/public-media-' . now()->format('Ymd-His') . '.zip');
        $destinationDir = dirname($destination);
        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Could not create zip: ' . $destination);
            return self::FAILURE;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $count = 0;
        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
                continue;
            }

            $zip->addFile($file->getPathname(), $relativePath);
            $count++;
        }

        $zip->close();

        $this->info('Exported ' . $count . ' media files.');
        $this->info('Backup file: ' . $destination);

        return self::SUCCESS;
    }
}
