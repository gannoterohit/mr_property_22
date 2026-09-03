<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataMaintenanceService;
use Illuminate\Http\Request;

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
}
