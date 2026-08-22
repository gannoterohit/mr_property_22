@extends('layouts.broker')

@section('title', 'Payments')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent panel</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Payments</h1>
            </div>
        </header>
    </header>
    <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mt-6">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="px-5 text-left">Type</th><th class="px-5 text-left">Amount</th><th class="px-5 text-left">Method</th><th class="px-5 text-left">Status</th><th class="px-5 text-left">Date</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3 font-bold text-slate-900">{{ ucfirst($payment->payment_type) }}</td>
                                    <td class="px-5 py-3 text-slate-600">&#8377;{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ ucfirst($payment->method ?? 'N/A') }}</td>
                                    <td class="px-5 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">{{ ucfirst($payment->status) }}</span></td>
                                    <td class="px-5 py-3 text-slate-500">{{ $payment->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-16 text-center text-slate-500">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="lg:hidden divide-y divide-slate-100">
                    @forelse($payments as $payment)
                        <article class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ ucfirst($payment->payment_type) }}</h3>
                                    <p class="text-xs text-slate-500">&#8377;{{ number_format($payment->amount, 2) }} · {{ ucfirst($payment->method ?? 'N/A') }}</p>
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payment->status) }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">No payments yet.</div>
                    @endforelse
                </div>
                <div class="p-4">{{ $payments->links() }}</div>
            </div>
        </div>
    </div>
@endsection