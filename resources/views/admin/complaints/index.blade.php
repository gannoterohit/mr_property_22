@extends('layouts.admin')
@section('title','Complaints Management')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">
@endpush
@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Unified support</p>
            <h1 class="mt-1 text-2xl font-extrabold">Complaints Management</h1>
            <p class="text-sm text-slate-500">Track SLA, escalation, ownership and staff assignment.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="complaints" />
            <span class="rounded-xl border bg-white px-4 py-2.5 text-xs font-bold">{{ $complaints->total() }} matching tickets</span>
        </div>
    </header>

    {{-- Active Quick Filter Indicator --}}
    @if(request('assigned') === 'me' || request('assigned_to') === 'me')
        <div class="flex items-center justify-between rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-3 text-xs text-indigo-900">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-indigo-600"></i>
                <span>Showing tickets <strong>Assigned to You</strong>.</span>
            </div>
            <a href="{{ route('admin.complaints.index') }}" class="font-bold underline hover:text-indigo-700">Clear filter</a>
        </div>
    @elseif(request('assigned') === 'unassigned' || request('assigned_to') === 'unassigned')
        <div class="flex items-center justify-between rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-900">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-amber-600"></i>
                <span>Showing <strong>Unassigned</strong> tickets awaiting staff pickup.</span>
            </div>
            <a href="{{ route('admin.complaints.index') }}" class="font-bold underline hover:text-amber-700">Clear filter</a>
        </div>
    @endif

    <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach([
            ['Open', $complaintStats['open'], 'fa-inbox', 'admin-theme-text', 'admin-theme-soft', route('admin.complaints.index', ['status' => 'open'])],
            ['My Assigned', $complaintStats['my_assigned'], 'fa-user-check', 'text-indigo-600', 'bg-indigo-50', route('admin.complaints.index', ['assigned' => 'me'])],
            ['Unassigned', $complaintStats['unassigned'], 'fa-user-clock', 'text-amber-600', 'bg-amber-50', route('admin.complaints.index', ['assigned' => 'unassigned'])],
            ['Overdue', $complaintStats['overdue'], 'fa-clock', 'text-red-600', 'bg-red-50', route('admin.complaints.index', ['sla' => 'overdue'])],
            ['Escalated', $complaintStats['escalated'], 'fa-arrow-up-right-dots', 'text-purple-600', 'bg-purple-50', route('admin.complaints.index', ['sla' => 'escalated'])],
            ['Resolved', $complaintStats['resolved'], 'fa-circle-check', 'text-emerald-600', 'bg-emerald-50', route('admin.complaints.index', ['status' => 'resolved'])],
        ] as [$label, $value, $icon, $tone, $bg, $url])
            <a href="{{ $url }}" class="rounded-2xl border bg-white p-4 h-full hover:shadow-sm transition block group">
                <div class="flex justify-between items-center gap-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 group-hover:text-slate-600 transition">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $bg }} {{ $tone }} shrink-0">
                        <i class="fas {{ $icon }}"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </section>

    <form method="GET" class="rounded-2xl border bg-white p-4">
        <div class="complaint-filter admin-filter-bar items-end">
            <input name="search" value="{{ request('search') }}" placeholder="Ticket number or subject" class="h-10 rounded-xl text-xs">
            
            {{-- Staff Assignment Filter --}}
            <select name="assigned_to" class="h-10 rounded-xl text-xs">
                <option value="">All Assignees</option>
                <option value="me" @selected(request('assigned_to') === 'me' || request('assigned') === 'me')>Assigned to Me (You)</option>
                <option value="unassigned" @selected(request('assigned_to') === 'unassigned' || request('assigned') === 'unassigned')>Unassigned</option>
                <optgroup label="Staff Members">
                    @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}" @selected((string)request('assigned_to') === (string)$staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </optgroup>
            </select>

            <select name="status" class="h-10 rounded-xl text-xs">
                <option value="">All statuses</option>
                <option value="open" @selected(request('status')==='open')>All open</option>
                @foreach(\App\Models\Complaint::STATUSES as $k=>$v)
                    <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="priority" class="h-10 rounded-xl text-xs">
                <option value="">All priorities</option>
                @foreach(['low','medium','high','urgent'] as $v)
                    <option value="{{ $v }}" @selected(request('priority')===$v)>{{ ucfirst($v) }}</option>
                @endforeach
            </select>
            <select name="category" class="h-10 rounded-xl text-xs">
                <option value="">All categories</option>
                @foreach(\App\Models\Complaint::CATEGORIES as $k=>$v)
                    <option value="{{ $k }}" @selected(request('category')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="sla" class="h-10 rounded-xl text-xs">
                <option value="">Any SLA state</option>
                <option value="overdue" @selected(request('sla')==='overdue')>Overdue</option>
                <option value="due_today" @selected(request('sla')==='due_today')>Due today</option>
                <option value="escalated" @selected(request('sla')==='escalated')>Escalated</option>
            </select>
            <select name="type" class="h-10 rounded-xl text-xs">
                <option value="">All types</option>
                <option value="complaint" @selected(request('type')==='complaint')>Complaint</option>
                <option value="inquiry" @selected(request('type')==='inquiry')>Inquiry</option>
                <option value="request" @selected(request('type')==='request')>Request</option>
            </select>
            <div class="flex gap-2 justify-end">
                <a href="{{ route('admin.complaints.index') }}" class="flex h-10 items-center rounded-xl border px-4 text-xs font-bold hover:bg-slate-50 transition">Reset</a>
                <button class="h-10 rounded-xl bg-slate-900 px-5 text-xs font-bold text-white hover:bg-slate-800 transition">Apply filters</button>
            </div>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border bg-white">
        <div class="flex justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">Support queue</h2>
                <p class="text-xs text-slate-500">Open a ticket to assign, reply or resolve it</p>
            </div>
            <span class="text-xs font-bold text-slate-500">Page {{ $complaints->currentPage() }} of {{ $complaints->lastPage() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="complaint-table admin-table-base">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Reporter</th>
                        <th>Complaint</th>
                        <th>Priority</th>
                        <th>SLA</th>
                        <th>Status</th>
                        <th class="text-right">Assigned Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($complaints as $complaint)
                        @php 
                            $closed = in_array($complaint->status, ['resolved', 'rejected', 'closed']);
                            $overdue = !$closed && $complaint->due_at?->isPast();
                            $priorityTone = match($complaint->priority) {
                                'urgent' => 'bg-red-100 text-red-700',
                                'high' => 'bg-orange-50 text-orange-700',
                                'medium' => 'bg-amber-50 text-amber-700',
                                default => 'bg-slate-100 text-slate-600'
                            };
                            $isMyTicket = $complaint->assigned_to === auth()->id();
                        @endphp
                        <tr class="hover:bg-slate-50 {{ $isMyTicket ? 'bg-indigo-50/20' : '' }}">
                            <td class="px-5">
                                <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-xs font-extrabold admin-theme-text">{{ $complaint->ticket_number }}</a>
                                <p class="text-[10px] text-slate-400">{{ $complaint->created_at->format('d M Y') }}</p>
                            </td>
                            <td class="px-5">
                                <strong class="block truncate text-xs">{{ $complaint->user?->name ?? 'Deleted user' }}</strong>
                                <p class="text-[10px] text-slate-400">{{ ucfirst($complaint->user?->role ?? 'unknown') }}</p>
                            </td>
                            <td class="px-5">
                                <a href="{{ route('admin.complaints.show', $complaint) }}" class="block truncate text-xs font-bold hover:underline">{{ $complaint->subject }}</a>
                                <p class="truncate text-[10px] text-slate-400">{{ \App\Models\Complaint::CATEGORIES[$complaint->category] ?? ucfirst($complaint->category) }}</p>
                            </td>
                            <td class="px-5">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold capitalize {{ $priorityTone }}">{{ $complaint->priority }}</span>
                            </td>
                            <td class="px-5">
                                <p class="text-xs font-bold {{ $overdue ? 'text-red-600' : 'text-slate-600' }}">{{ $complaint->due_at?->format('d M, h:i A') ?? 'Not assigned' }}</p>
                                @if($overdue)
                                    <span class="text-[10px] font-bold text-red-600">Overdue</span>
                                @elseif($complaint->escalated_at)
                                    <span class="text-[10px] font-bold text-amber-600">Escalated</span>
                                @elseif(!$closed && $complaint->due_at)
                                    <span class="text-[10px] text-slate-400">{{ $complaint->due_at->diffForHumans() }}</span>
                                @endif
                            </td>
                            <td class="px-5">
                                <span class="rounded-full admin-theme-soft px-2.5 py-1 text-[10px] font-bold admin-theme-text">{{ \App\Models\Complaint::STATUSES[$complaint->status] ?? ucfirst($complaint->status) }}</span>
                            </td>
                            <td class="px-5 text-right">
                                @if($isMyTicket)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                                        <i class="fas fa-user-check text-[10px]"></i>
                                        <span>{{ $complaint->assignee->name }}</span>
                                        <span class="text-[9px] uppercase tracking-wider bg-indigo-600 text-white px-1.5 py-0.2 rounded-sm font-black">You</span>
                                    </span>
                                @elseif($complaint->assignee)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 bg-slate-100">
                                        <i class="fas fa-user text-[10px] text-slate-400"></i>
                                        <span>{{ $complaint->assignee->name }}</span>
                                    </span>
                                @else
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200">
                                            Unassigned
                                        </span>
                                        @if(!$closed)
                                            <form action="{{ route('admin.complaints.assign', $complaint) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="assigned_to" value="me">
                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition cursor-pointer" title="Assign this ticket to me">
                                                    <i class="fas fa-hand text-[9px]"></i> Claim
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-14 text-center">
                                <i class="fas fa-shield-halved text-3xl text-slate-300"></i>
                                <p class="mt-2 text-sm font-bold">No complaints found</p>
                                <p class="text-xs text-slate-400">Try adjusting the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
            <div class="border-t p-4">{{ $complaints->links() }}</div>
        @endif
    </section>
</div>
@endsection
