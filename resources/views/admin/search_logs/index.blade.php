@extends('layouts.admin')

@section('title','Search Analytics')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-search_logs-index.css') }}">
@endpush

@section('admin-content')
@php
    $tab = request('tab','demand');
    $navItems = [
        ['demand','Search Demand','fa-chart-line'],
        ['supply','Listing Supply','fa-building'],
        ['events','Visitor Events','fa-chart-simple'],
        ['logs','Search Logs','fa-clock-rotate-left'],
    ];
@endphp

<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Demand intelligence</p>
            <h1 class="mt-1 text-2xl font-extrabold">Search Analytics</h1>
            <p class="text-sm text-slate-500">Demand, listing supply, visitor events and raw search activity.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.data-actions dataset="search-logs" />
            <div class="rounded-xl admin-theme-bg px-5 py-3 text-white">
                <p class="text-[9px] font-bold uppercase text-white/70">Tracked searches</p>
                <p class="text-xl font-extrabold">{{ \App\Models\SearchLog::count() }}</p>
            </div>
        </div>
    </header>

    <nav class="flex gap-2 overflow-x-auto rounded-2xl border bg-white p-2">
        @foreach($navItems as [$key,$label,$icon])
            <a href="{{ route('admin.analytics',['tab'=>$key]) }}" class="inline-flex min-w-max items-center gap-2 rounded-xl px-5 py-3 text-xs font-bold {{ $tab===$key?'admin-theme-bg':'text-slate-600 hover:bg-slate-50' }}">
                <i class="fas {{ $icon }}"></i>{{ $label }}
            </a>
        @endforeach
    </nav>

    @if($tab==='demand' || $tab==='supply')
        @php
            $records = $tab === 'demand' ? $topCities : $topListingCities;
            $maximum = max(1, (int) ($records->first()?->total ?? 1));
            $unit = $tab === 'demand' ? 'searches' : 'listings';
        @endphp
        <section class="rounded-2xl border bg-white p-5">
            <div>
                <h2 class="text-sm font-extrabold">{{ $tab==='demand' ? 'Highest search demand' : 'Largest listing supply' }}</h2>
                <p class="text-xs text-slate-500">Cities ranked by {{ $unit }}</p>
            </div>
            <div class="analytics-list mt-5">
                @forelse($records as $index=>$record)
                    <div class="rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400">RANK {{ $index+1 }}</span>
                                <p class="mt-1 text-sm font-extrabold">{{ $record->city }}</p>
                            </div>
                            <strong class="text-xl {{ $tab==='demand'?'admin-theme-text':'text-emerald-600' }}">{{ $record->total }}</strong>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full {{ $tab==='demand'?'admin-theme-bg':'bg-emerald-500' }}" style="width:{{ ($record->total/$maximum)*100 }}%"></div>
                        </div>
                        <p class="mt-2 text-[10px] text-slate-400">{{ $record->total }} {{ $unit }}</p>
                    </div>
                @empty
                    <p class="p-10 text-sm text-slate-500">No data available.</p>
                @endforelse
            </div>
        </section>
    @elseif($tab==='events')
        <section class="space-y-4">
            <div class="rounded-2xl border bg-white p-4">
                <form method="GET" class="flex flex-wrap items-end gap-2">
                    <input type="hidden" name="tab" value="events">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">From</label>
                        <input type="date" name="from" value="{{ request('from', $from->toDateString()) }}" class="h-10 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">To</label>
                        <input type="date" name="to" value="{{ request('to', $to->toDateString()) }}" class="h-10 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Event</label>
                        <select name="event" class="h-10 rounded-lg text-xs">
                            <option value="">All events</option>
                            @foreach(['PageView','Search','ViewContent','InitiateCheckout','Purchase'] as $eventName)
                                <option value="{{ $eventName }}" @selected(request('event')===$eventName)>{{ $eventName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">City</label>
                        <input name="city" value="{{ request('city') }}" placeholder="Filter city" class="h-10 rounded-lg text-xs">
                    </div>
                    <button class="h-10 rounded-lg bg-slate-900 px-4 text-xs font-bold text-white">Apply filters</button>
                    <a href="{{ route('admin.analytics',['tab'=>'events']) }}" class="flex h-10 items-center rounded-lg border px-3 text-xs font-bold">Reset</a>
                </form>
            </div>

            <div class="analytics-kpis admin-kpis">
                @foreach([
                    ['Page views',$visitorStats['page_views'],'fa-eye','admin-theme-text'],
                    ['Visitors',$visitorStats['unique_visitors'],'fa-users','text-sky-600'],
                    ['Property views',$visitorStats['room_views'],'fa-door-open','text-emerald-600'],
                    ['Searches',$visitorStats['searches'],'fa-magnifying-glass','text-amber-600'],
                    ['Checkout starts',$visitorStats['checkout_starts'],'fa-cart-shopping','admin-theme-text'],
                    ['Purchases',$visitorStats['purchases'],'fa-circle-check','text-green-600'],
                    ['Revenue','Rs '.number_format($visitorStats['revenue'],2),'fa-indian-rupee-sign','text-slate-900'],
                    ['Checkout conversion',$visitorStats['checkout_conversion'].'%','fa-percent','text-rose-600'],
                ] as [$label,$value,$icon,$tone])
                    <div class="rounded-2xl border bg-white p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                            <i class="fas {{ $icon }} {{ $tone }}"></i>
                        </div>
                        <p class="mt-2 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border bg-white p-5">
                    <h2 class="text-sm font-extrabold">Top Viewed Properties</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($topViewedRooms as $item)
                            <div class="flex items-center justify-between gap-3 rounded-xl border p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-extrabold">{{ $item->room?->title ?? 'Deleted property' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $item->room?->city ?? 'Unknown city' }}</p>
                                </div>
                                <strong class="text-sm admin-theme-text">{{ $item->total }}</strong>
                            </div>
                        @empty
                            <p class="py-8 text-sm text-slate-500">No property views tracked yet.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl border bg-white p-5">
                    <h2 class="text-sm font-extrabold">Event Cities</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($eventCities as $city)
                            <div class="flex items-center justify-between rounded-xl border p-3">
                                <p class="text-xs font-extrabold">{{ $city->city }}</p>
                                <strong class="text-sm text-emerald-600">{{ $city->total }}</strong>
                            </div>
                        @empty
                            <p class="py-8 text-sm text-slate-500">No city events tracked yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border bg-white">
                <div class="flex justify-between border-b px-5 py-4">
                    <div>
                        <h2 class="text-sm font-extrabold">Visitor event history</h2>
                        <p class="text-xs text-slate-500">{{ $recentEvents->total() }} matching records</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500">Page {{ $recentEvents->currentPage() }} of {{ $recentEvents->lastPage() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="analytics-table">
                        <thead>
                            <tr><th>Date & time</th><th>Event</th><th>Property / city</th><th>Visitor</th><th>Value</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentEvents as $event)
                                <tr>
                                    <td class="px-5"><p class="text-xs font-bold">{{ $event->created_at->format('d M Y') }}</p><p class="text-[10px] text-slate-400">{{ $event->created_at->format('h:i A') }}</p></td>
                                    <td class="px-5"><span class="rounded-lg admin-theme-soft px-2 py-1 text-[10px] font-extrabold admin-theme-text">{{ $event->event_name }}</span></td>
                                    <td class="px-5"><p class="max-w-xs truncate text-xs font-semibold">{{ $event->room?->title ?? 'No property linked' }}</p><p class="text-[10px] text-slate-400">{{ $event->city ?: $event->room?->city ?: 'Unknown city' }}</p></td>
                                    <td class="px-5"><p class="text-xs font-semibold">{{ $event->user?->name ?: 'Guest visitor' }}</p><p class="text-[10px] text-slate-400">{{ $event->ip_address }}</p></td>
                                    <td class="px-5"><p class="text-xs font-extrabold">{{ $event->amount ? 'Rs '.number_format((float)$event->amount,2) : '-' }}</p><p class="text-[10px] text-slate-400">{{ $event->currency }}</p></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-12 text-center text-sm text-slate-500">No visitor events found yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentEvents->hasPages())
                    <div class="border-t p-4">{{ $recentEvents->withQueryString()->links() }}</div>
                @endif
            </div>
        </section>
    @else
        <section class="space-y-4">
            <div class="rounded-2xl border bg-white p-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <form method="GET" class="flex flex-wrap items-end gap-2">
                        <input type="hidden" name="tab" value="logs">
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">From</label><input type="date" name="from" value="{{ request('from') }}" class="h-10 rounded-lg text-xs"></div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">To</label><input type="date" name="to" value="{{ request('to') }}" class="h-10 rounded-lg text-xs"></div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">City</label><input name="city" value="{{ request('city') }}" placeholder="Filter city" class="h-10 rounded-lg text-xs"></div>
                        <button class="h-10 rounded-lg bg-slate-900 px-4 text-xs font-bold text-white">Apply filters</button>
                        <a href="{{ route('admin.analytics',['tab'=>'logs']) }}" class="flex h-10 items-center rounded-lg border px-3 text-xs font-bold">Reset</a>
                    </form>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.analytics.logs.range') }}" class="confirm-range">@csrf @method('DELETE')<input type="hidden" name="from" value="{{ request('from') }}"><input type="hidden" name="to" value="{{ request('to') }}"><button @disabled(!request('from')||!request('to')) class="h-10 rounded-lg bg-amber-50 px-4 text-xs font-bold text-amber-700 disabled:opacity-40">Delete date range</button></form>
                        <form method="POST" action="{{ route('admin.analytics.logs.all') }}" class="confirm-all">@csrf @method('DELETE')<button class="h-10 rounded-lg bg-red-600 px-4 text-xs font-bold text-white">Delete all logs</button></form>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl border bg-white">
                <div class="flex justify-between border-b px-5 py-4"><div><h2 class="text-sm font-extrabold">Search log history</h2><p class="text-xs text-slate-500">{{ $recentLogs->total() }} matching records</p></div><span class="text-xs font-bold text-slate-500">Page {{ $recentLogs->currentPage() }} of {{ $recentLogs->lastPage() }}</span></div>
                <div class="overflow-x-auto">
                    <table class="analytics-table">
                        <thead><tr><th>Date & time</th><th>City / query</th><th>Visitor</th><th>Applied filters</th><th>Action</th></tr></thead>
                        <tbody class="divide-y">
                            @forelse($recentLogs as $log)
                                <tr>
                                    <td class="px-5"><p class="text-xs font-bold">{{ $log->created_at->format('d M Y') }}</p><p class="text-[10px] text-slate-400">{{ $log->created_at->format('h:i A') }}</p></td>
                                    <td class="px-5"><strong class="text-xs">{{ $log->city?:'All cities' }}</strong><p class="text-[10px] text-slate-400">{{ $log->search_term?:'Auto-detected' }}</p></td>
                                    <td class="px-5"><p class="text-xs font-semibold">{{ $log->user?->name?:'Guest visitor' }}</p><p class="text-[10px] text-slate-400">{{ $log->ip_address }}</p></td>
                                    <td class="px-5"><div class="flex max-w-lg flex-wrap gap-1">@forelse(collect($log->filters)->filter() as $key=>$value)<span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-semibold">{{ ucfirst(str_replace('_',' ',$key)) }}: {{ is_array($value)?implode(', ',$value):$value }}</span>@empty<span class="text-xs text-slate-400">No filters</span>@endforelse</div></td>
                                    <td class="px-5"><form method="POST" action="{{ route('admin.analytics.logs.destroy',$log) }}" class="confirm-one">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-12 text-center text-sm text-slate-500">No search logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentLogs->hasPages())
                    <div class="border-t p-4">{{ $recentLogs->withQueryString()->links() }}</div>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        const confirmDelete = (selector, title, text) => document.querySelectorAll(selector).forEach(form => form.addEventListener('submit', async e => {
            e.preventDefault();
            const result = await Swal.fire({title, text, icon:'warning', showCancelButton:true, confirmButtonText:'Yes, delete', confirmButtonColor:'#dc2626'});
            if (result.isConfirmed) form.submit();
        }));
        confirmDelete('.confirm-one','Delete this search log?','This record will be permanently removed.');
        confirmDelete('.confirm-range','Delete logs in selected date range?','Every matching search record will be permanently removed.');
        confirmDelete('.confirm-all','Delete ALL search logs?','This will permanently remove the complete search history and cannot be undone.');
    </script>
@endpush
