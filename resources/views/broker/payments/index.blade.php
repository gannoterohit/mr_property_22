@extends('layouts.broker')

@section('title', 'Payments')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
<div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-section-heading mb-6">
        <div>
            <h2>Payments</h2>
            <p class="text-sm text-slate-500 mt-1">All your payment history in one place.</p>
        </div>
    </div>

    <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 flex-shrink-0">
                                        <i class="fas fa-receipt text-xs"></i>
                                    </span>
                                    <span class="font-bold text-slate-900">{{ ucfirst($payment->payment_type) }}</span>
                                </div>
                            </td>
                            <td class="font-semibold text-slate-800">&#8377;{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 text-slate-600">
                                    <i class="fas fa-credit-card text-slate-300 text-xs"></i>
                                    {{ ucfirst($payment->method ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                                    {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $payment->status === 'completed' ? 'bg-emerald-500' : ($payment->status === 'pending' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="text-slate-500">{{ $payment->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="py-16 text-center">
                                    <i class="fas fa-credit-card text-3xl text-slate-200 block mb-3"></i>
                                    <p class="text-sm text-slate-500">No payments yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($payments as $payment)
                <div class="p-4 hover:bg-slate-50/60 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <i class="fas fa-receipt text-xs"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm">{{ ucfirst($payment->payment_type) }}</h3>
                                <p class="text-xs text-slate-500">&#8377;{{ number_format($payment->amount, 2) }} · {{ ucfirst($payment->method ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold flex-shrink-0
                            {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400 pl-12">{{ $payment->created_at->format('d M Y') }}</p>
                </div>
            @empty
                <div class="p-12 text-center">
                    <i class="fas fa-credit-card text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No payments yet.</p>
                </div>
            @endforelse
        </div>

        @if($payments->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection