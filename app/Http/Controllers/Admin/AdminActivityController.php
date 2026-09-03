<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);
        $filteredLogs = (clone $query)->count();
        $logs = $query->with('actor')->latest()->paginate(30)->withQueryString();

        return view('admin.activity.index',[
            'logs'=>$logs,
            'staff'=>User::where('role','admin')->orderBy('name')->get(),
            'totalLogs'=>AdminActivityLog::count(),
            'todayLogs'=>AdminActivityLog::whereDate('created_at',today())->count(),
            'activeActors'=>AdminActivityLog::where('created_at','>=',now()->subDays(30))->distinct('actor_id')->count('actor_id'),
            'filteredLogs'=>$filteredLogs,
            'methods'=>AdminActivityLog::query()->whereNotNull('method')->distinct()->orderBy('method')->pluck('method'),
        ]);
    }

    public function destroy(AdminActivityLog $activityLog)
    {
        $activityLog->delete();

        return back()->with('success', 'Activity log deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'log_ids' => ['required', 'array', 'min:1'],
            'log_ids.*' => ['integer', 'exists:admin_activity_logs,id'],
        ]);

        $deleted = AdminActivityLog::whereIn('id', $data['log_ids'])->delete();

        return back()->with('success', "{$deleted} selected activity log(s) deleted.");
    }

    public function destroyFiltered(Request $request)
    {
        $data = $request->validate([
            'delete_scope' => ['required', 'in:filtered,all'],
            'search' => ['nullable', 'string', 'max:255'],
            'actor' => ['nullable', 'integer', 'exists:users,id'],
            'method' => ['nullable', 'string', 'max:10'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = $data['delete_scope'] === 'all'
            ? AdminActivityLog::query()
            : $this->filteredQuery($request);

        if ($data['delete_scope'] === 'filtered' && ! $this->hasActiveFilters($request)) {
            return back()->with('error', 'Please apply at least one filter, or use Delete all logs.');
        }

        $deleted = $query->delete();
        $label = $data['delete_scope'] === 'all' ? 'all' : 'filtered';

        return redirect()->route('admin.activity.index')->with('success', "{$deleted} {$label} activity log(s) deleted.");
    }

    private function filteredQuery(Request $request)
    {
        return AdminActivityLog::query()
            ->when($request->filled('actor'), fn ($query) => $query->where('actor_id', $request->integer('actor')))
            ->when($request->filled('method'), fn ($query) => $query->where('method', strtoupper((string) $request->input('method'))))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($inner) => $inner
                ->where('description', 'like', '%'.$request->search.'%')
                ->orWhere('route_name', 'like', '%'.$request->search.'%')
                ->orWhere('action', 'like', '%'.$request->search.'%')
                ->orWhere('ip_address', 'like', '%'.$request->search.'%')));
    }

    private function hasActiveFilters(Request $request): bool
    {
        return collect(['search', 'actor', 'method', 'from', 'to'])->contains(fn ($key) => $request->filled($key));
    }
}
