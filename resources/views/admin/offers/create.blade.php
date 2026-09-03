@extends('layouts.admin')
@section('title', 'Create Coupon')

@section('admin-content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.offers.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Coupons</p>
            <h1 class="text-2xl font-extrabold text-slate-950">Create Promo Code</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <p><i class="fas fa-circle-exclamation mr-1"></i> {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.offers.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Core Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Coupon Details</h2>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Coupon Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Diwali Special Offer"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-offset-0 focus:border-transparent @error('title') border-red-400 @enderror">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Promo Code <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="code" id="couponCode" value="{{ old('code') }}" required placeholder="e.g. DIWALI30"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono font-bold uppercase tracking-widest focus:ring-2 focus:ring-offset-0 focus:border-transparent @error('code') border-red-400 @enderror"
                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')">
                    <button type="button" onclick="generateCode()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition whitespace-nowrap">
                        <i class="fas fa-wand-magic-sparkles mr-1"></i> Generate
                    </button>
                </div>
                <p class="mt-1.5 text-[11px] text-slate-400">Only letters, numbers, underscores. Will be stored uppercase.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description for internal reference..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-offset-0 focus:border-transparent resize-none">{{ old('description') }}</textarea>
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
                        <option value="percentage" @selected(old('discount_type','percentage')=='percentage')>Percentage (%) Off</option>
                        <option value="flat" @selected(old('discount_type')=='flat')>Flat Amount (₹) Off</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Discount Value <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span id="discountPrefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">%</span>
                        <input type="number" name="discount_value" value="{{ old('discount_value') }}" required min="1" step="0.01" placeholder="e.g. 20"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm font-bold">
                    </div>
                </div>
            </div>

            <div id="capField" class="{{ old('discount_type','percentage') === 'flat' ? 'hidden' : '' }} grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Discount Cap (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                        <input type="number" name="max_discount_cap" value="{{ old('max_discount_cap') }}" min="1" placeholder="e.g. 500 (optional)"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm">
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Maximum discount even if % exceeds this.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Min Order Value (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                        <input type="number" name="min_order_value" value="{{ old('min_order_value', 0) }}" min="0"
                            class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm">
                    </div>
                </div>
            </div>

            <div id="minOrderFlat" class="{{ old('discount_type','percentage') !== 'flat' ? 'hidden' : '' }}">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Min Order Value (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                    <input type="number" name="min_order_value_flat" value="{{ old('min_order_value', 0) }}" min="0"
                        class="w-full rounded-xl border border-slate-200 pl-8 pr-4 py-2.5 text-sm">
                </div>
            </div>
        </div>

        {{-- Targeting --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Targeting & Limits</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Applicable For <span class="text-red-500">*</span></label>
                    <select name="applicable_for" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="all" @selected(old('applicable_for','all')=='all')>All Services</option>
                        <option value="owner_plans" @selected(old('applicable_for')=='owner_plans')>Owner Plans Only</option>
                        <option value="user_plans" @selected(old('applicable_for')=='user_plans')>User Plans Only</option>
                        <option value="broker_plans" @selected(old('applicable_for')=='broker_plans')>Broker Plans Only</option>
                        <option value="unlocks" @selected(old('applicable_for')=='unlocks')>Contact Unlocks Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Target Audience <span class="text-red-500">*</span></label>
                    <select name="target_audience" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                        <option value="both" @selected(old('target_audience','both')=='both')>All Users</option>
                        <option value="user" @selected(old('target_audience')=='user')>Users Only</option>
                        <option value="owner" @selected(old('target_audience')=='owner')>Owners Only</option>
                        <option value="broker" @selected(old('target_audience')=='broker')>Brokers Only</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Total Uses</label>
                    <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" placeholder="Leave blank for unlimited"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Max Uses Per User <span class="text-red-500">*</span></label>
                    <input type="number" name="per_user_limit" value="{{ old('per_user_limit', 1) }}" min="1" max="10" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid From</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid Till</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Options</h2>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded">
                <div>
                    <p class="text-sm font-bold text-slate-900">Active</p>
                    <p class="text-xs text-slate-500">Users can apply this coupon at checkout immediately.</p>
                </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="show_as_banner" value="1" @checked(old('show_as_banner')) class="h-4 w-4 rounded">
                <div>
                    <p class="text-sm font-bold text-slate-900">Show as Website Announcement</p>
                    <p class="text-xs text-slate-500">Display this promo code in the top announcement bar on the website for all visitors.</p>
                </div>
            </label>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('admin.offers.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="px-8 py-2.5 rounded-xl admin-theme-bg text-sm font-bold text-white hover:opacity-90 transition">
                <i class="fas fa-ticket-simple mr-2 text-xs"></i> Create Coupon
            </button>
        </div>
    </form>
</div>

<script>
function toggleCapField() {
    const type = document.getElementById('discountType').value;
    const capField = document.getElementById('capField');
    const minOrderFlat = document.getElementById('minOrderFlat');
    const prefix = document.getElementById('discountPrefix');
    if (type === 'flat') {
        capField.classList.add('hidden');
        minOrderFlat.classList.remove('hidden');
        prefix.textContent = '₹';
    } else {
        capField.classList.remove('hidden');
        minOrderFlat.classList.add('hidden');
        prefix.textContent = '%';
    }
}
function generateCode() {
    const words = ['FARM','SAVE','DEAL','MEGA','FLAT','RENT','HOME','BEST'];
    const word = words[Math.floor(Math.random() * words.length)];
    const num = Math.floor(10 + Math.random() * 80);
    document.getElementById('couponCode').value = word + num;
}
</script>
@endsection
