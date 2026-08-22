@extends('layouts.broker')

@section('title', 'Subscription')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent panel</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Subscription & Credits</h1>
                <p class="mt-2 text-sm text-slate-500">Manage your subscription plan and listing credits.</p>
            </div>
        </header>
    </header>
    <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 mt-6">
                    <i class="fas fa-circle-check"></i>{{ session('success') }}
                </div>
            @endif

            @if($subscription)
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Current Subscription</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div><dt class="text-slate-500">Plan</dt><dd class="mt-1 font-bold text-slate-900">{{ $subscription->plan->name ?? 'N/A' }}</dd></div>
                        <div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-bold text-slate-900">{{ ucfirst($subscription->status) }}</dd></div>
                        <div><dt class="text-slate-500">Expires</dt><dd class="mt-1 font-bold text-slate-900">{{ $subscription->expires_at?->format('M d, Y') ?? 'N/A' }}</dd></div>
                        <div><dt class="text-slate-500">Listings Limit</dt><dd class="mt-1 font-bold text-slate-900">{{ $subscription->max_listings ?? 'Unlimited' }}</dd></div>
                        <div><dt class="text-slate-500">Listings Used</dt><dd class="mt-1 font-bold text-slate-900">{{ $subscription->listings_used }}</dd></div>
                        <div><dt class="text-slate-500">Remaining</dt><dd class="mt-1 font-bold text-emerald-600">{{ $subscription->remaining_listings }}</dd></div>
                    </dl>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="text-sm text-slate-500">No active subscription. Choose a plan below to get started.</p>
                </div>
            @endif

            <h2 class="text-lg font-bold text-slate-900 mt-8 mb-4">Available Plans</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($plans as $plan)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                        <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ ucfirst($plan->type) }} plan</p>
                        <p class="text-3xl font-extrabold text-slate-950 mt-4">&#8377;{{ number_format($plan->price) }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600 flex-1">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i> Max {{ $plan->max_listings ?? 'Unlimited' }} listings</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i> Valid {{ $plan->duration_days ?? 30 }} days</li>
                            @if($plan->is_featured_included)
                                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i> Featured listings included</li>
                            @endif
                        </ul>
                        <a href="{{ route('agent.subscription.purchase', $plan) }}" class="mt-6 admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold shadow-sm">Subscribe</a>
                    </div>
                @empty
                    <div class="col-span-full text-center text-sm text-slate-500 py-8">No plans available.</div>
                @endforelse
            </div>

            <h2 class="text-lg font-bold text-slate-900 mt-8 mb-4">Listing Credits</h2>
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr><th class="px-5 text-left">Source</th><th class="px-5 text-left">Type</th><th class="px-5 text-left">Remaining</th><th class="px-5 text-left">Expires</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($credits as $credit)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3 font-bold text-slate-900">{{ ucfirst($credit->source) }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ ucfirst($credit->type) }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $credit->credits_remaining }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $credit->expires_at?->format('M d, Y') ?? 'Never' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No credits available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection