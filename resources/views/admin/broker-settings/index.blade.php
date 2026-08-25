@extends('layouts.admin')

@section('title', 'Broker Settings')

@section('admin-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="admin-theme-text text-xs font-bold uppercase tracking-wider">Configuration</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">Broker Module Settings</h2>
            <p class="mt-1 text-sm text-slate-500">Control broker module toggles and pricing from one place.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.broker-settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-900">Module Controls</h3>
                <p class="text-sm text-slate-500">Enable or disable broker features globally.</p>
            </div>
            <div class="p-6 space-y-4">
                @php
                    $toggles = [
                        'broker_module_enabled' => 'Broker Module',
                        'broker_verification_enabled' => 'Broker Verification',
                        'broker_listing_charges_enabled' => 'Broker Listing Charges',
                        'broker_featured_enabled' => 'Featured Listing for Brokers',
                        'broker_future_brokerage_enabled' => 'Future Brokerage System',
                    ];
                @endphp
                @foreach($toggles as $key => $label)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 p-4">
                        <div>
                            <p class="font-bold text-slate-900">{{ $label }}</p>
                            <p class="text-xs text-slate-500">{{ $settings[$key]->description ?? '' }}</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="{{ $key }}" class="peer sr-only" value="1" {{ \App\Models\BrokerSetting::isEnabled($key) ? 'checked' : '' }}>
                            <div class="h-7 w-12 rounded-full bg-slate-200 peer-checked:bg-emerald-500 after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-900">Pricing</h3>
                <p class="text-sm text-slate-500">Configure charges and limits for broker listings.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $fields = [
                        'broker_per_listing_charge' => 'Per Listing Charge (INR)',
                        'broker_featured_charge' => 'Featured Listing Charge (INR)',
                        'broker_listing_expiry_days' => 'Listing Expiry (Days)',
                        'broker_free_listing_limit' => 'Free Listing Limit',
                        'broker_lead_charge' => 'Lead Charge (INR)',
                    ];
                @endphp
                @foreach($fields as $key => $label)
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">{{ $label }}</label>
                        <input type="number" name="{{ $key }}" value="{{ $settings[$key]->value ?? '' }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" step="0.01">
                        <p class="mt-1 text-xs text-slate-400">{{ $settings[$key]->description ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold shadow-sm">
                <i class="fas fa-save text-xs"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection