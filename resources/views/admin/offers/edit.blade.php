@extends('layouts.admin')
@section('title', 'Edit Coupon — ' . $offer->code)

@section('admin-content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.offers.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Coupons</p>
            <h1 class="text-2xl font-extrabold text-slate-950">Edit: <code class="text-lg">{{ $offer->code }}</code></h1>
        </div>
    </div>

    {{-- Usage Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase">Total Uses</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($offer->uses_count) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center shadow-sm">
            <p class="text-xs font-bold text-emerald-600 uppercase">Total Savings Given</p>
            <p class="text-2xl font-black text-emerald-800 mt-1">₹{{ number_format($usageStats->total_savings ?? 0, 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase">Status</p>
            <p class="text-lg font-black mt-1 {{ $offer->status === 'Live' ? 'text-emerald-700' : 'text-slate-500' }}">{{ $offer->status }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <p><i class="fas fa-circle-exclamation mr-1"></i> {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.offers.update', $offer) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')

        {{-- Core Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Coupon Details</h2>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Coupon Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $offer->title) }}" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-offset-0">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Promo Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $offer->code) }}" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono font-bold uppercase tracking-widest focus:ring-2"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm resize-none">{{ old('description', $offer->description) }}</textarea>
            </div>
        </div>

        {{-- Discount Rules --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Discount Rules</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Discount Type <span class="text-red-500">*</span></label>
                    <select name="discount_type" id="discountType" onchange="toggleCapField()" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="percentage" @selected(old('discount_type',$offer->discount_type)=='percentage')>Percentage (%) Off</option>
                        <option value="flat" @selected(old('discount_type',$offer->discount_type)=='flat')>Flat Amount (₹) Off</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Discount Value <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span id="discountPrefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">{{ $offer->discount_type === 'flat' ? '₹' : '%' }}</span>
                        <input type="number" name="discount_value" value="{{ old('discount_value', $offer->discount_value) }}" required min="1" step="0.01"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm font-bold">
                    </div>
                </div>
            </div>
            <div id="capField" class="{{ old('discount_type',$offer->discount_type) === 'flat' ? 'hidden' : '' }} grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Discount Cap (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                        <input type="number" name="max_discount_cap" value="{{ old('max_discount_cap', $offer->max_discount_cap) }}" min="1"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Min Order Value (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                        <input type="number" name="min_order_value" value="{{ old('min_order_value', $offer->min_order_value) }}" min="0"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm">
                    </div>
                </div>
            </div>
        </div>

        {{-- Targeting & Limits --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Targeting & Limits</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Applicable For</label>
                    <select name="applicable_for" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="all" @selected(old('applicable_for',$offer->applicable_for)=='all')>All Services</option>
                        <option value="owner_plans" @selected(old('applicable_for',$offer->applicable_for)=='owner_plans')>Owner Plans Only</option>
                        <option value="user_plans" @selected(old('applicable_for',$offer->applicable_for)=='user_plans')>User Plans Only</option>
                        <option value="broker_plans" @selected(old('applicable_for',$offer->applicable_for)=='broker_plans')>Broker Plans Only</option>
                        <option value="unlocks" @selected(old('applicable_for',$offer->applicable_for)=='unlocks')>Contact Unlocks Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Target Audience</label>
                    <select name="target_audience" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="both" @selected(old('target_audience',$offer->target_audience)=='both')>All Users</option>
                        <option value="user" @selected(old('target_audience',$offer->target_audience)=='user')>Users Only</option>
                        <option value="owner" @selected(old('target_audience',$offer->target_audience)=='owner')>Owners Only</option>
                        <option value="broker" @selected(old('target_audience',$offer->target_audience)=='broker')>Brokers Only</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Total Uses</label>
                    <input type="number" name="max_uses" value="{{ old('max_uses', $offer->max_uses) }}" min="1" placeholder="Blank = unlimited"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Uses Per User</label>
                    <input type="number" name="per_user_limit" value="{{ old('per_user_limit', $offer->per_user_limit) }}" min="1" max="10" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid From</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $offer->start_date?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid Till</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $offer->end_date?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Options</h2>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer->is_active)) class="h-4 w-4 rounded">
                <div>
                    <p class="text-sm font-bold text-slate-900">Active</p>
                    <p class="text-xs text-slate-500">Users can apply this coupon at checkout.</p>
                </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="show_as_banner" value="1" @checked(old('show_as_banner', $offer->show_as_banner)) class="h-4 w-4 rounded">
                <div>
                    <p class="text-sm font-bold text-slate-900">Show as Website Announcement</p>
                    <p class="text-xs text-slate-500">Display this promo code in the top announcement bar on the website.</p>
                </div>
            </label>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('admin.offers.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="px-8 py-2.5 rounded-xl admin-theme-bg text-sm font-bold text-white hover:opacity-90 transition">
                <i class="fas fa-floppy-disk mr-2 text-xs"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<script>
function toggleCapField() {
    const type = document.getElementById('discountType').value;
    const capField = document.getElementById('capField');
    const prefix = document.getElementById('discountPrefix');
    if (type === 'flat') {
        capField.classList.add('hidden');
        prefix.textContent = '₹';
    } else {
        capField.classList.remove('hidden');
        prefix.textContent = '%';
    }
}
</script>
@endsection
