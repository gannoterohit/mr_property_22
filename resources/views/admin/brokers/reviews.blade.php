@extends('layouts.admin')

@section('title', 'Broker Reviews Moderation')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-theme-text text-[10px] font-extrabold uppercase tracking-[.2em]">People management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Broker Reviews</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor, moderate, hide or remove client feedback and ratings.</p>
        </div>
    </header>

    @include('admin.members.nav')

    {{-- KPI Cards --}}
    <section class="people-kpis admin-kpis grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([
            ['Total Reviews', $stats['total'], 'fa-comments', 'admin-theme-text', 'admin-theme-soft'],
            ['Approved', $stats['approved'], 'fa-circle-check', 'text-emerald-600', 'bg-emerald-50'],
            ['Pending / Hidden', $stats['pending'], 'fa-clock', 'text-amber-600', 'bg-amber-50'],
            ['5-Star Ratings', $stats['five_star'], 'fa-star', 'text-amber-500', 'bg-amber-50'],
            ['1-Star Ratings', $stats['one_star'], 'fa-triangle-exclamation', 'text-rose-600', 'bg-rose-50'],
        ] as [$label, $value, $icon, $tone, $bg])
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $bg }} {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                </div>
                <p class="mt-3 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    {{-- Filter Bar --}}
    <form class="owner-filter rounded-2xl border bg-white p-4 shadow-sm flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search client, broker, or comment..." class="h-11 w-full rounded-xl border-slate-200 pl-10 text-sm">
        </div>
        <select name="rating" class="h-11 rounded-xl border-slate-200 text-sm">
            <option value="">All Star Ratings</option>
            @for($s = 5; $s >= 1; $s--)
                <option value="{{ $s }}" {{ request('rating') == $s ? 'selected' : '' }}>{{ $s }} Stars (★)</option>
            @endfor
        </select>
        <select name="status" class="h-11 rounded-xl border-slate-200 text-sm">
            <option value="">All Statuses</option>
            <option value="approved" @selected(request('status')==='approved')>Approved</option>
            <option value="pending" @selected(request('status')==='pending')>Pending / Hidden</option>
        </select>
        <div class="flex gap-2">
            <button class="h-11 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white">Apply</button>
            <a href="{{ route('admin.broker-reviews.index') }}" class="inline-flex h-11 items-center rounded-xl border px-4 text-xs font-extrabold text-slate-600">Reset</a>
        </div>
    </form>

    {{-- Reviews Table --}}
    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">All Client Reviews</h2>
                <p class="text-xs text-slate-500">{{ $reviews->total() }} reviews match current filters</p>
            </div>
            <span class="admin-theme-soft rounded-full px-3 py-1.5 text-[10px] font-extrabold">Page {{ $reviews->currentPage() }} / {{ max(1, $reviews->lastPage()) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="owners-table admin-table-base w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left">Agency / Broker</th>
                        <th class="px-4 py-3 text-left">Tenant / Client</th>
                        <th class="px-4 py-3 text-left">Rating</th>
                        <th class="px-4 py-3 text-left">Comment</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-slate-50/70">
                            {{-- Broker --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($review->broker->name ?? 'B', 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="font-extrabold text-sm text-slate-900">
                                            @if($review->broker)
                                                <a href="{{ route('admin.brokers.show', $review->broker) }}" class="hover:text-indigo-600 transition">
                                                    {{ $review->broker->agency_name ?: $review->broker->name }}
                                                </a>
                                            @else
                                                <span class="text-slate-400">Deleted Broker</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            Agent: {{ $review->broker->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Tenant --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-sm">{{ $review->user->name ?? 'Anonymous' }}</div>
                                <div class="text-xs text-slate-400">{{ $review->user->email ?? 'No email' }}</div>
                            </td>

                            {{-- Rating --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1 text-amber-500 font-black text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $review->rating ? 'fas' : 'far text-slate-300' }} fa-star text-xs"></i>
                                    @endfor
                                    <span class="ml-1 text-xs text-slate-700">{{ $review->rating }}.0</span>
                                </div>
                            </td>

                            {{-- Feedback --}}
                            <td class="px-4 py-4 max-w-sm">
                                <p class="text-xs text-slate-700 leading-relaxed line-clamp-2" title="{{ $review->comment }}">
                                    {{ $review->comment ?: '(No text feedback provided)' }}
                                </p>
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ $review->created_at ? $review->created_at->format('M d, Y') : '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ $review->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <form action="{{ route('admin.broker-reviews.toggle-status', $review) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition" title="{{ $review->status === 'approved' ? 'Hide Review' : 'Approve Review' }}">
                                            <i class="fas {{ $review->status === 'approved' ? 'fa-eye-slash text-amber-600' : 'fa-eye text-emerald-600' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.broker-reviews.destroy', $review) }}" method="POST" class="admin-confirm inline" data-confirm-title="Delete this review?" data-confirm-text="This will permanently delete this client review." data-confirm-button="Yes, delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition" title="Delete Review">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @if($review->broker)
                                        <a href="{{ route('agency.show', $review->broker) }}" target="_blank" title="View Public Agency Profile" class="p-1.5 rounded-lg text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 text-sm">
                                <i class="fas fa-comment-slash text-3xl text-slate-300 mb-2 block"></i>
                                No broker reviews found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
            <div class="border-t p-4">{{ $reviews->links() }}</div>
        @endif
    </section>
</div>
@endsection
