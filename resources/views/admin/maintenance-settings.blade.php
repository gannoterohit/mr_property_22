@extends('layouts.admin')
@section('title', 'Maintenance Controls')
@push('styles')
<style>
    .availability-switch {
        position: relative !important;
        display: inline-block !important;
        width: 54px !important;
        min-width: 54px !important;
        max-width: 54px !important;
        height: 30px !important;
        min-height: 30px !important;
        padding: 0 !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 9999px !important;
        background: #cbd5e1 !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        vertical-align: middle !important;
    }
    .availability-switch.is-on { border-color: #4f46e5 !important; background: #4f46e5 !important; }
    .availability-switch.is-danger.is-on { border-color: #dc2626 !important; background: #dc2626 !important; }
    .availability-switch-knob {
        position: absolute !important;
        top: 2px !important;
        left: 2px !important;
        display: block !important;
        width: 22px !important;
        height: 22px !important;
        border-radius: 9999px !important;
        background: #fff !important;
        box-shadow: 0 1px 4px rgba(15,23,42,.28) !important;
        transform: translateX(0) !important;
        transition: transform .18s ease !important;
    }
    .availability-switch.is-on .availability-switch-knob { transform: translateX(24px) !important; }
    .availability-state { width: 28px; text-align: right; font-size: 11px; font-weight: 800; }
</style>
@endpush
@section('admin-content')
@php
    $on = fn ($key, $default = '0') => filter_var(\App\Models\Setting::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    $controls = [
        ['registration_enabled', 'Registration', 'Allow new users and owners to register.', 'fa-user-plus', '1'],
        ['new_listings_enabled', 'New property listings', 'Allow owners to submit new rooms. Existing listings remain manageable.', 'fa-building-circle-arrow-right', '1'],
        ['payments_enabled', 'Payments and contact unlock', 'Allow new purchases, subscriptions and contact unlocks.', 'fa-credit-card', '1'],
        ['owner_panel_enabled', 'Owner panel', 'Allow owners to enter their dashboard workspace.', 'fa-user-tie', '1'],
        ['user_panel_enabled', 'User panel', 'Allow users to enter dashboard, wallet, wishlist and complaints.', 'fa-users', '1'],
    ];
@endphp
<div class="space-y-6 p-5 lg:p-7" x-data="{
    maintenance_mode: {{ $on('maintenance_mode') ? 'true' : 'false' }},
    registration_enabled: {{ $on('registration_enabled', '1') ? 'true' : 'false' }},
    new_listings_enabled: {{ $on('new_listings_enabled', '1') ? 'true' : 'false' }},
    payments_enabled: {{ $on('payments_enabled', '1') ? 'true' : 'false' }},
    owner_panel_enabled: {{ $on('owner_panel_enabled', '1') ? 'true' : 'false' }},
    user_panel_enabled: {{ $on('user_panel_enabled', '1') ? 'true' : 'false' }}
}">
    <div><p class="text-xs font-bold uppercase tracking-widest admin-theme-text">System settings</p><h1 class="mt-1 text-2xl font-extrabold text-slate-950">Maintenance & Availability</h1><p class="mt-1 text-sm text-slate-500">Safely pause the whole website or only affected modules. Admin access and payment webhooks always remain available.</p></div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>@endif
    <form action="{{ route('admin.maintenance.update') }}" method="POST" class="space-y-6">@csrf
        <section class="overflow-hidden rounded-2xl border {{ $on('maintenance_mode') ? 'border-red-200' : 'border-slate-200' }} bg-white shadow-sm">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6"><div class="flex gap-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $on('maintenance_mode') ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}"><i class="fas fa-power-off"></i></span><div><h2 class="font-extrabold text-slate-900">Global maintenance mode</h2><p class="mt-1 text-sm text-slate-500">Public website and member workspaces show the maintenance page. Admin stays accessible.</p></div></div><div class="flex shrink-0 items-center gap-3"><input type="hidden" name="maintenance_mode" :value="maintenance_mode ? 1 : 0"><span class="availability-state" :class="maintenance_mode ? 'text-red-600' : 'text-slate-500'" x-text="maintenance_mode ? 'ON' : 'OFF'"></span><button type="button" role="switch" :aria-checked="maintenance_mode" @click="maintenance_mode = !maintenance_mode" class="availability-switch is-danger" :class="{ 'is-on': maintenance_mode }"><span class="availability-switch-knob"></span></button></div></div>
            <div class="grid gap-5 border-t border-slate-200 bg-slate-50/60 p-5 sm:p-6 lg:grid-cols-2"><div><label class="mb-2 block text-sm font-bold text-slate-700">Maintenance heading *</label><input name="maintenance_title" required maxlength="120" value="{{ old('maintenance_title', \App\Models\Setting::get('maintenance_title', 'Website is currently under maintenance')) }}" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm /20"></div><div><label class="mb-2 block text-sm font-bold text-slate-700">Expected reopening time</label><input type="datetime-local" name="maintenance_reopening_at" value="{{ old('maintenance_reopening_at', \App\Models\Setting::get('maintenance_reopening_at')) }}" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm /20"></div><div class="lg:col-span-2"><label class="mb-2 block text-sm font-bold text-slate-700">Visitor message *</label><textarea name="maintenance_message" required maxlength="500" rows="3" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm /20">{{ old('maintenance_message', \App\Models\Setting::get('maintenance_message', 'We are improving your experience and will be back soon.')) }}</textarea></div></div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><div class="mb-5"><h2 class="font-extrabold text-slate-900">Module availability</h2><p class="mt-1 text-sm text-slate-500">Keep unaffected parts of the business running.</p></div><div class="grid gap-3 lg:grid-cols-2">@foreach($controls as [$key,$label,$description,$icon,$default])<div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 transition "><span class="flex min-w-0 gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white admin-theme-text shadow-sm"><i class="fas {{ $icon }}"></i></span><span><span class="block text-sm font-bold text-slate-900">{{ $label }}</span><span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ $description }}</span></span></span><span class="flex shrink-0 items-center gap-2"><input type="hidden" name="{{ $key }}" :value="{{ $key }} ? 1 : 0"><span class="availability-state" :class="{{ $key }} ? 'admin-theme-text' : 'text-slate-400'" x-text="{{ $key }} ? 'ON' : 'OFF'"></span><button type="button" role="switch" :aria-checked="{{ $key }}" @click="{{ $key }} = !{{ $key }}" class="availability-switch" :class="{ 'is-on': {{ $key }} }"><span class="availability-switch-knob"></span></button></span></div>@endforeach</div></section>
        <div class="flex flex-col items-end gap-2"><p class="text-xs font-semibold text-amber-600"><i class="fas fa-circle-info mr-1"></i>Toggle change karne ke baad Save button zaroor dabayein.</p><button class="inline-flex items-center gap-2 rounded-xl admin-theme-bg px-6 py-3 text-sm font-bold text-white shadow-sm "><i class="fas fa-floppy-disk"></i>Save availability settings</button></div>
    </form>
</div>
@endsection
