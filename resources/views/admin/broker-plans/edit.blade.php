@extends('layouts.admin')

@section('title', 'Edit Broker Plan')

@section('admin-content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.broker-plans.index') }}" class="rounded-lg bg-slate-100 p-2 text-sm font-bold text-slate-700 hover:bg-slate-200"><i class="fas fa-arrow-left"></i></a>
        <div>
            <p class="admin-theme-text text-xs font-bold uppercase tracking-wider">Finance & Growth</p>
            <h2 class="text-2xl font-bold text-slate-950">Edit Broker Plan</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.broker-plans.update', $brokerPlan) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Plan Name</label>
            <input type="text" name="name" value="{{ old('name', $brokerPlan->name) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" required>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $brokerPlan->slug) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" required>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" required>
                    <option value="monthly" {{ old('type', $brokerPlan->type) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('type', $brokerPlan->type) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="per_listing" {{ old('type', $brokerPlan->type) === 'per_listing' ? 'selected' : '' }}>Per Listing</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Price (INR)</label>
                <input type="number" name="price" value="{{ old('price', $brokerPlan->price) }}" step="0.01" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" required>
            </div>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Max Listings <span class="text-slate-400 text-xs font-medium">(-1 for unlimited)</span></label>
            <input type="number" name="max_listings" value="{{ old('max_listings', $brokerPlan->max_listings) }}" min="-1" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" placeholder="-1 for Unlimited">
            <p class="mt-1 text-xs text-slate-500">How many properties broker can post with this plan.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_featured_included" id="is_featured" value="1" {{ old('is_featured_included', $brokerPlan->is_featured_included) ? 'checked' : '' }}>
            <label for="is_featured" class="text-sm font-bold text-slate-700">Featured listing included</label>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $brokerPlan->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="text-sm font-bold text-slate-700">Active</label>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $brokerPlan->sort_order) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.broker-plans.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="admin-theme-bg inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold shadow-sm"><i class="fas fa-save text-xs"></i> Update Plan</button>
        </div>
    </form>
</div>
@endsection