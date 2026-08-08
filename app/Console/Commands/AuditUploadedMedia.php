<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditUploadedMedia extends Command
{
    protected $signature = 'media:audit';

    protected $description = 'Check whether uploaded media referenced by the database exists on the public disk.';

    public function handle(): int
    {
        $paths = [];

        Room::query()->select(['id', 'title', 'photo', 'photos', 'video'])->each(function (Room $room) use (&$paths) {
            $roomPaths = array_merge([$room->photo], $room->photos ?: [], [$room->video]);

            foreach (array_filter($roomPaths) as $path) {
                if (is_string($path) && ! preg_match('/^https?:\/\//', $path)) {
                    $paths[ltrim($path, '/')]["#{$room->id} {$room->title}"] = true;
                }
            }
        });

        $missing = [];
        foreach ($paths as $path => $rooms) {
            if (! Storage::disk('public')->exists($path)) {
                $missing[] = [$path, implode(', ', array_slice(array_keys($rooms), 0, 3))];
            }
        }

        $this->info('Database media paths: ' . count($paths));
        $this->info('Available files: ' . (count($paths) - count($missing)));
        $this->info('Missing files: ' . count($missing));

        if ($missing) {
            $this->newLine();
            $this->warn('Copy these files/folders from the old system into storage/app/public:');
            $this->table(['Missing path', 'Used by'], array_slice($missing, 0, 25));
        }

        return $missing ? self::FAILURE : self::SUCCESS;
    }
}
