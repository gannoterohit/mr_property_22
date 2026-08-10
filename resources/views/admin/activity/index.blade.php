@extends('layouts.admin')
@section('title', 'Activity Logs')

@section('admin-content')
@php
    $hasFilters = request()->hasAny(['search', 'actor', 'method', 'from', 'to']);
@endphp

<div class="space-y-4 p-5 lg:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.18em] admin-theme-text">Security audit</p>
            <h1 class="mt-1 text-xl font-extrabold text-slate-950">Admin Activity Logs</h1>
            <p class="mt-1 text-xs text-slate-500">Track successful changes made by every administrator and staff member.</p>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <div class="audit-summary">
                <span><strong>{{ number_format($totalLogs) }}</strong>Total</span>
                <span><strong>{{ number_format($todayLogs) }}</strong>Today</span>
                <span><strong>{{ number_format($filteredLogs) }}</strong>Filtered</span>
                <span><strong>{{ number_format($activeActors) }}</strong>Active staff</span>
                <em><i class="fas fa-circle-check"></i>Logging active</em>
            </div>
            <x-admin.data-actions dataset="activity-logs" />
        </div>
    </div>

    <form id="bulkDeleteLogsForm" method="POST" action="{{ route('admin.activity.bulk-destroy') }}" class="hidden admin-confirm" data-confirm-title="Delete selected logs?" data-confirm-text="Selected activity logs will be permanently removed." data-confirm-button="Yes, delete selected">
        @csrf
        @method('DELETE')
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <form method="GET" class="audit-filters">
                <div class="relative">
                    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                    <input name="search" value="{{ request('search') }}" placeholder="Search action, route or IP..." class="h-10 w-full rounded-xl pl-9 text-xs">
                </div>
                <select name="actor" class="h-10 rounded-xl text-xs">
                    <option value="">All staff members</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" @selected(request('actor')==$member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                <select name="method" class="h-10 rounded-xl text-xs">
                    <option value="">All methods</option>
                    @foreach($methods as $method)
                        <option value="{{ $method }}" @selected(request('method')===$method)>{{ $method }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="h-10 rounded-xl text-xs">
                <input type="date" name="to" value="{{ request('to') }}" class="h-10 rounded-xl text-xs">
                <x-admin.button type="submit" variant="primary">Apply</x-admin.button>
                @if($hasFilters)
                    <x-admin.button :href="route('admin.activity.index')">Clear</x-admin.button>
                @endif
            </form>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-xs font-bold text-slate-600">
                Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.button type="submit" form="bulkDeleteLogsForm" variant="danger">Delete selected</x-admin.button>

                <form method="POST" action="{{ route('admin.activity.destroy-filtered') }}" class="admin-confirm" data-confirm-title="Delete filtered activity logs?" data-confirm-text="All logs matching the current filters will be permanently removed." data-confirm-button="Yes, delete filtered">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete_scope" value="filtered">
                    @foreach(['search', 'actor', 'method', 'from', 'to'] as $filter)
                        <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                    @endforeach
                    <x-admin.button type="submit" variant="danger">Delete filtered</x-admin.button>
                </form>

                <form method="POST" action="{{ route('admin.activity.destroy-filtered') }}" class="admin-confirm" data-confirm-title="Delete all activity logs?" data-confirm-text="This will permanently remove every admin activity log. A new log for this delete action may be recorded." data-confirm-button="Yes, delete all">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete_scope" value="all">
                    <x-admin.button type="submit" variant="danger">Delete all</x-admin.button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px]">
                <thead>
                    <tr>
                        <th class="w-10 px-5"><input id="selectAllLogs" type="checkbox" class="rounded border-slate-300"></th>
                        <th>Date & time</th>
                        <th>Staff member</th>
                        <th>Administrative action</th>
                        <th>Module / route</th>
                        <th>IP address</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        @php
                            $methodClass = match($log->method) {
                                'POST' => 'bg-emerald-50 text-emerald-700',
                                'PUT', 'PATCH' => 'bg-sky-50 text-sky-700',
                                'DELETE' => 'bg-red-50 text-red-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                            $routeParts = explode('.', str_replace('admin.', '', $log->route_name ?? 'admin'));
                            $module = ucfirst(str_replace('-', ' ', $routeParts[0] ?? 'Admin'));
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5"><input type="checkbox" form="bulkDeleteLogsForm" name="log_ids[]" value="{{ $log->id }}" class="log-checkbox rounded border-slate-300"></td>
                            <td class="whitespace-nowrap px-5"><p class="text-xs font-bold text-slate-700">{{ $log->created_at->format('d M Y') }}</p><p class="mt-0.5 text-[10px] text-slate-400">{{ $log->created_at->format('h:i:s A') }}</p></td>
                            <td class="px-5"><div class="flex items-center gap-2.5"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full admin-theme-soft text-[10px] font-extrabold">{{ strtoupper(substr($log->actor?->name ?? 'D',0,1)) }}</span><div><p class="text-xs font-bold text-slate-800">{{ $log->actor?->name ?? 'Deleted admin' }}</p><p class="max-w-[170px] truncate text-[10px] text-slate-400">{{ $log->actor?->email }}</p></div></div></td>
                            <td class="px-5"><div class="flex items-center gap-2"><span class="rounded-md px-2 py-1 text-[9px] font-extrabold {{ $methodClass }}">{{ $log->method }}</span><p class="text-xs text-slate-700">{{ $log->description }}</p></div></td>
                            <td class="px-5"><p class="text-xs font-bold text-slate-700">{{ $module }}</p><code class="text-[9px] admin-theme-text">{{ $log->route_name }}</code></td>
                            <td class="whitespace-nowrap px-5 font-mono text-[10px] text-slate-500">{{ $log->ip_address }}</td>
                            <td class="px-5">
                                <div class="flex justify-end">
                                    <form method="POST" action="{{ route('admin.activity.destroy', $log) }}" class="admin-confirm" data-confirm-title="Delete this activity log?" data-confirm-text="This log entry will be permanently removed." data-confirm-button="Yes, delete">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.action-icon variant="delete" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-14 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-clock-rotate-left"></i></span><p class="mt-3 text-sm font-bold text-slate-800">No activity found</p><p class="mt-1 text-xs text-slate-500">Successful admin changes will automatically appear here.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $logs->links() }}</div>@endif
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-activity-index.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAllLogs');
    const checkboxes = [...document.querySelectorAll('.log-checkbox')];
    if (!selectAll) return;

    selectAll.addEventListener('change', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>
@endpush
