<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataMaintenanceService;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DataMaintenanceController extends Controller
{
    public function index(DataMaintenanceService $service)
    {
        $summary = $service->summary();
        $retention = $service->retention();
        $eligible = $service->eligibleCounts();
        $orphanMedia = $service->orphanMedia();

        return view('admin.data-maintenance.index', compact('summary', 'retention', 'eligible', 'orphanMedia'));
    }

    public function update(Request $request, DataMaintenanceService $service)
    {
        $rules = collect(DataMaintenanceService::RETENTION_DEFAULTS)
            ->mapWithKeys(fn ($value, $key) => [$key => ['required', 'integer', 'min:1', 'max:3650']])
            ->all();
        $service->updateRetention($request->validate($rules));

        return back()->with('success', 'Data retention settings updated successfully.');
    }

    public function cleanup(Request $request, DataMaintenanceService $service)
    {
        $data = $request->validate([
            'confirm_cleanup' => ['accepted'],
            'delete_orphan_media' => ['nullable', 'boolean'],
        ]);
        $deleted = $service->run((bool) ($data['delete_orphan_media'] ?? false));

        return back()->with('success', 'Cleanup completed. '.number_format(collect($deleted)->except('orphan_media_bytes')->sum()).' records/files removed.');
    }

    public function backup(Request $request)
    {
        abort_unless(config('database.default') === 'mysql', 400, 'Database backups currently support MySQL only.');

        $connection = config('database.connections.mysql');
        $dumpBinary = env('MYSQLDUMP_PATH', 'C:\\xampp\\mysql\\bin\\mysqldump.exe');
        if (! is_file($dumpBinary)) {
            $dumpBinary = 'mysqldump';
        }

        $process = new Process([
            $dumpBinary,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ], base_path(), ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            report(new \RuntimeException('Database backup failed: '.$process->getErrorOutput()));
            return back()->with('error', 'Database backup failed. Check the mysqldump installation and database credentials.');
        }

        return response()->streamDownload(
            static function () use ($process): void {
                echo $process->getOutput();
            },
            'apnanest-backup-'.now()->format('Y-m-d-His').'.sql',
            ['Content-Type' => 'application/sql']
        );
    }
}
