<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DataMaintenanceService
{
    public const RETENTION_DEFAULTS = [
        'otp_days' => 1,
        'session_days' => 7,
        'payment_days' => 30,
        'analytics_days' => 90,
        'search_log_days' => 90,
        'activity_log_days' => 180,
        'contact_message_days' => 90,
    ];

    public function retention(): array
    {
        return collect(self::RETENTION_DEFAULTS)->mapWithKeys(function (int $default, string $key) {
            $value = (int) Setting::get('retention_'.$key, $default);

            return [$key => max(1, min($value, 3650))];
        })->all();
    }

    public function updateRetention(array $values): void
    {
        foreach (array_keys(self::RETENTION_DEFAULTS) as $key) {
            Setting::set('retention_'.$key, (string) $values[$key]);
        }
    }

    public function summary(): array
    {
        $tables = [
            'users' => 'Users',
            'rooms' => 'Rooms',
            'payments' => 'Payments',
            'enquiries' => 'Unlock enquiries',
            'analytics_events' => 'Analytics events',
            'search_logs' => 'Search logs',
            'admin_activity_logs' => 'Admin activity logs',
        ];

        $counts = [];
        foreach ($tables as $table => $label) {
            $counts[$table] = [
                'label' => $label,
                'count' => Schema::hasTable($table) ? DB::table($table)->count() : 0,
            ];
        }

        return [
            'counts' => $counts,
            'storage_bytes' => collect(Storage::disk('public')->allFiles())
                ->sum(fn (string $file) => Storage::disk('public')->size($file)),
            'database_bytes' => $this->databaseSize(),
            'last_run_at' => Setting::get('data_cleanup_last_run_at'),
            'last_result' => json_decode((string) Setting::get('data_cleanup_last_result', '{}'), true) ?: [],
        ];
    }

    public function eligibleCounts(): array
    {
        $days = $this->retention();

        return [
            'otps' => $this->countBefore('otps', 'expires_at', now()->subDays($days['otp_days'])),
            'sessions' => Schema::hasTable('sessions')
                ? DB::table('sessions')->where('last_activity', '<', now()->subDays($days['session_days'])->timestamp)->count() : 0,
            'locked_enquiries' => Schema::hasTable('enquiries')
                ? DB::table('enquiries')->where('unlocked', false)->where('created_at', '<', now()->subDays($days['payment_days']))->count() : 0,
            'stale_payments' => Schema::hasTable('payments')
                ? DB::table('payments')->whereIn('status', ['pending', 'failed'])->where('created_at', '<', now()->subDays($days['payment_days']))->count() : 0,
            'analytics_events' => $this->countBefore('analytics_events', 'created_at', now()->subDays($days['analytics_days'])),
            'search_logs' => $this->countBefore('search_logs', 'created_at', now()->subDays($days['search_log_days'])),
            'activity_logs' => $this->countBefore('admin_activity_logs', 'created_at', now()->subDays($days['activity_log_days'])),
            'read_contact_messages' => Schema::hasTable('contact_messages')
                ? DB::table('contact_messages')->where('is_read', true)->where('created_at', '<', now()->subDays($days['contact_message_days']))->count() : 0,
        ];
    }

    public function orphanMedia(): array
    {
        $referenced = [];
        if (Schema::hasTable('rooms')) {
            Room::query()->select(['id', 'photo', 'photos', 'video'])->chunkById(250, function ($rooms) use (&$referenced) {
                foreach ($rooms as $room) {
                    foreach (array_merge([$room->photo, $room->video], $room->photos ?: []) as $path) {
                        if (is_string($path) && $path !== '' && !str_starts_with($path, 'http')) {
                            $referenced[ltrim(str_replace('\\', '/', $path), '/')] = true;
                        }
                    }
                }
            });
        }

        return collect(Storage::disk('public')->allFiles('rooms'))
            ->reject(fn (string $file) => isset($referenced[ltrim(str_replace('\\', '/', $file), '/')]))
            ->map(fn (string $file) => [
                'path' => $file,
                'bytes' => Storage::disk('public')->size($file),
                'modified_at' => Storage::disk('public')->lastModified($file),
            ])->values()->all();
    }

    public function run(bool $deleteOrphanMedia = false): array
    {
        $days = $this->retention();
        $deleted = DB::transaction(function () use ($days) {
            return [
                'otps' => $this->deleteBefore('otps', 'expires_at', now()->subDays($days['otp_days'])),
                'sessions' => Schema::hasTable('sessions')
                    ? DB::table('sessions')->where('last_activity', '<', now()->subDays($days['session_days'])->timestamp)->delete() : 0,
                'locked_enquiries' => Schema::hasTable('enquiries')
                    ? DB::table('enquiries')->where('unlocked', false)->where('created_at', '<', now()->subDays($days['payment_days']))->delete() : 0,
                'stale_payments' => Schema::hasTable('payments')
                    ? DB::table('payments')->whereIn('status', ['pending', 'failed'])->where('created_at', '<', now()->subDays($days['payment_days']))->delete() : 0,
                'analytics_events' => $this->deleteBefore('analytics_events', 'created_at', now()->subDays($days['analytics_days'])),
                'search_logs' => $this->deleteBefore('search_logs', 'created_at', now()->subDays($days['search_log_days'])),
                'activity_logs' => $this->deleteBefore('admin_activity_logs', 'created_at', now()->subDays($days['activity_log_days'])),
                'read_contact_messages' => Schema::hasTable('contact_messages')
                    ? DB::table('contact_messages')->where('is_read', true)->where('created_at', '<', now()->subDays($days['contact_message_days']))->delete() : 0,
            ];
        });

        $deleted['orphan_media'] = 0;
        $deleted['orphan_media_bytes'] = 0;
        if ($deleteOrphanMedia) {
            $quarantineRoot = 'maintenance-quarantine/'.now()->format('Ymd-His');
            foreach ($this->orphanMedia() as $file) {
                $quarantinePath = $quarantineRoot.'/'.$file['path'];
                if (Storage::disk('public')->move($file['path'], $quarantinePath)) {
                    $deleted['orphan_media']++;
                    $deleted['orphan_media_bytes'] += $file['bytes'];
                }
            }
        }

        Setting::set('data_cleanup_last_run_at', now()->toDateTimeString());
        Setting::set('data_cleanup_last_result', json_encode($deleted));

        return $deleted;
    }

    private function countBefore(string $table, string $column, $cutoff): int
    {
        return Schema::hasTable($table) ? DB::table($table)->where($column, '<', $cutoff)->count() : 0;
    }

    private function deleteBefore(string $table, string $column, $cutoff): int
    {
        return Schema::hasTable($table) ? DB::table($table)->where($column, '<', $cutoff)->delete() : 0;
    }

    private function databaseSize(): ?int
    {
        if (DB::getDriverName() !== 'mysql') {
            return null;
        }

        try {
            return (int) (DB::table('information_schema.tables')
                ->where('table_schema', DB::getDatabaseName())
                ->selectRaw('SUM(data_length + index_length) AS bytes')
                ->value('bytes') ?? 0);
        } catch (\Throwable) {
            // Some shared hosts do not grant information_schema access.
            return null;
        }
    }
}
