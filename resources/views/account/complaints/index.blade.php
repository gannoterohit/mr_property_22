@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'My Complaints')
@php
    $role = Auth::user()->role;
    $isBroker = $role === 'broker';
    $contentSection = $role === 'owner' ? 'owner-content' : ($isBroker ? 'broker-content' : ($role === 'user' ? 'customer-content' : 'content'));
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
<style>
.complaints-wrap { padding: 2rem 0 3.5rem; }
.status-open      { background: #eff6ff; color: #3b82f6; }
.status-pending   { background: #fffbeb; color: #d97706; }
.status-resolved  { background: #ecfdf5; color: #059669; }
.status-closed    { background: #f1f5f9; color: #64748b; }
.status-rejected  { background: #fef2f2; color: #dc2626; }
@media (max-width: 640px) { .complaints-wrap { padding: 1.5rem 0 2.5rem; } }
</style>
@endpush

@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 complaints-wrap">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
        <div>
            <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-1">Support</p>
            <h1 class="text-xl font-black text-slate-900 leading-tight">My Complaints</h1>
            <p class="text-sm text-slate-500 mt-1">Track reports and support team responses.</p>
        </div>
        <a href="{{ route('complaints.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-indigo-100 hover:bg-indigo-700 transition flex-shrink-0">
            <i class="fas fa-plus text-xs"></i> New Complaint
        </a>
    </div>

    {{-- Table Panel --}}
    <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Desktop --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Complaint</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                        @php
                            $statusClass = match($complaint->status) {
                                'open'     => 'status-open',
                                'pending'  => 'status-pending',
                                'resolved' => 'status-resolved',
                                'closed'   => 'status-closed',
                                default    => 'status-rejected',
                            };
                        @endphp
                        <tr onclick="location.href='{{ route('complaints.show', $complaint) }}'" style="cursor:pointer">
                            <td>
                                <a class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline"
                                   href="{{ route('complaints.show', $complaint) }}">
                                    {{ $complaint->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <p class="font-semibold text-slate-900">{{ $complaint->subject }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ \App\Models\Complaint::CATEGORIES[$complaint->category] ?? $complaint->category }}</p>
                            </td>
                            <td>
                                <span class="text-slate-600">{{ $complaint->room?->title ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full opacity-80 bg-current"></span>
                                    {{ \App\Models\Complaint::STATUSES[$complaint->status] ?? ucfirst($complaint->status) }}
                                </span>
                            </td>
                            <td class="text-slate-400 whitespace-nowrap">{{ $complaint->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="py-16 text-center">
                                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 text-2xl mb-4">
                                        <i class="fas fa-shield-halved"></i>
                                    </span>
                                    <p class="font-semibold text-slate-700">No complaints submitted</p>
                                    <p class="text-xs text-slate-400 mt-1">Issues with a property or service? Submit a complaint.</p>
                                    <a href="{{ route('complaints.create') }}"
                                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                                        <i class="fas fa-plus text-xs"></i> Submit Complaint
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse($complaints as $complaint)
                @php
                    $statusClass = match($complaint->status) {
                        'open'     => 'status-open',
                        'pending'  => 'status-pending',
                        'resolved' => 'status-resolved',
                        'closed'   => 'status-closed',
                        default    => 'status-rejected',
                    };
                @endphp
                <a href="{{ route('complaints.show', $complaint) }}" class="block p-4 hover:bg-slate-50/70 transition">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-indigo-600">{{ $complaint->ticket_number }}</p>
                            <p class="font-semibold text-slate-900 text-sm truncate mt-0.5">{{ $complaint->subject }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold flex-shrink-0 {{ $statusClass }}">
                            {{ \App\Models\Complaint::STATUSES[$complaint->status] ?? ucfirst($complaint->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $complaint->room?->title ?? 'General complaint' }}</span>
                        <span>{{ $complaint->updated_at->diffForHumans() }}</span>
                    </div>
                </a>
            @empty
                <div class="p-12 text-center">
                    <i class="fas fa-shield-halved text-3xl text-slate-200 block mb-3"></i>
                    <p class="font-semibold text-slate-500 text-sm">No complaints submitted.</p>
                </div>
            @endforelse
        </div>

        @if($complaints->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $complaints->links() }}</div>
        @endif
    </div>
</div>
@endsection
