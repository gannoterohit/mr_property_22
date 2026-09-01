@extends('layouts.admin')
@section('title', 'Coupons & Promotions')

@section('admin-content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Marketing</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Coupons & Promotions</h1>
            <p class="mt-1 text-sm text-slate-500">Create and manage promo codes. Applied at checkout for real discounts.</p>
        </div>
        <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center gap-2 rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90 transition">
            <i class="fas fa-plus text-xs"></i> New Coupon
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Coupons</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ $coupons->total() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Uses</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ number_format($totalUsages) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Savings Given</p>
            <p class="mt-1 text-3xl font-black text-emerald-800">₹{{ number_format($totalSavings, 0) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.offers.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search coupon code or title..."
                class="h-10 w-full rounded-xl border border-slate-200 pl-9 pr-3 text-xs font-semibold focus:ring-0 focus:border-slate-400">
        </div>
        <select name="status" class="h-10 rounded-xl border border-slate-200 pl-3 pr-8 text-xs font-semibold">
            <option value="">All Status</option>
            <option value="active" @selected(request('status')=='active')>Active</option>
            <option value="inactive" @selected(request('status')=='inactive')>Inactive</option>
        </select>
        <button type="submit" class="h-10 px-5 rounded-xl admin-theme-bg text-xs font-bold text-white">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.offers.index') }}" class="h-10 px-4 rounded-xl border border-slate-200 text-slate-500 text-xs font-bold flex items-center hover:bg-slate-50">
                <i class="fas fa-rotate-left mr-1"></i> Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Coupon</th>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Discount</th>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Applicable</th>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Uses / Limit</th>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Validity</th>
                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($coupons as $coupon)
                    @php
                        $status = $coupon->status;
                        $statusColor = match($status) {
                            'Live'      => 'bg-emerald-50 text-emerald-700',
                            'Scheduled' => 'bg-blue-50 text-blue-700',
                            'Expired'   => 'bg-red-50 text-red-600',
                            'Exhausted' => 'bg-orange-50 text-orange-700',
                            default     => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl admin-theme-soft admin-theme-text">
                                    <i class="fas fa-tag text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $coupon->title }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <code class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-black text-slate-700 tracking-wider">{{ $coupon->code }}</code>
                                        @if($coupon->show_as_banner)
                                            <span class="rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">📢 Banner</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm font-black text-slate-900">{{ $coupon->discount_label }}</span>
                            @if($coupon->min_order_value > 0)
                                <p class="text-[10px] text-slate-400 mt-0.5">Min ₹{{ number_format($coupon->min_order_value, 0) }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full admin-theme-soft admin-theme-text px-2.5 py-1 text-[11px] font-bold">{{ $coupon->applicable_for_label }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-bold text-slate-900">{{ number_format($coupon->uses_count) }} @if($coupon->max_uses)/ {{ number_format($coupon->max_uses) }}@else/ ∞ @endif</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $coupon->per_user_limit }}/user</p>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $coupon->start_date?->format('d M Y') ?? 'Now' }}
                            <span class="mx-1">–</span>
                            {{ $coupon->end_date?->format('d M Y') ?? 'No expiry' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusColor }}">{{ $status }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <x-admin.status-toggle :active="$coupon->is_active" :action="route('admin.offers.toggleActive', $coupon)" method="POST" />
                                <x-admin.action-icon variant="edit" :href="route('admin.offers.edit', $coupon)" title="Edit coupon" />
                                <form action="{{ route('admin.offers.destroy', $coupon) }}" method="POST"
                                    class="admin-confirm"
                                    data-confirm-title="Delete {{ $coupon->code }}?"
                                    data-confirm-text="This coupon will be permanently removed. Usage history is kept."
                                    data-confirm-button="Yes, delete coupon">
                                    @csrf @method('DELETE')
                                    <x-admin.action-icon variant="delete" type="submit" title="Delete" />
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fas fa-ticket text-lg"></i>
                            </span>
                            <p class="mt-3 font-bold text-slate-800">No coupons yet</p>
                            <p class="mt-1 text-sm text-slate-500">Create your first promo code to start offering discounts.</p>
                            <a href="{{ route('admin.offers.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl admin-theme-bg px-5 py-2.5 text-sm font-bold text-white">
                                <i class="fas fa-plus text-xs"></i> Create Coupon
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($coupons->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection
