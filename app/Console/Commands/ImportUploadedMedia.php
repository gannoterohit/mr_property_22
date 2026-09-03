<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;

class ImportUploadedMedia extends Command
{
    protected $signature = 'media:import {source : Zip file created by media:export}';

    protected $description = 'Import uploaded media into storage/app/public.';

    public function handle(): int
    {
        $source = $this->argument('source');
        if (! is_file($source)) {
            $this->error('Backup zip not found: ' . $source);
            return self::FAILURE;
        }

        $destination = storage_path('app/public');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($source) !== true) {
            $this->error('Could not open zip: ' . $source);
            return self::FAILURE;
        }

        $zip->extractTo($destination);
        $count = $zip->numFiles;
        $zip->close();

        $this->info('Imported media entries: ' . $count);
        $this->info('Imported into: ' . $destination);
        $this->line('Run php artisan storage:link if public/storage is not linked.');

        return self::SUCCESS;
    }
}
