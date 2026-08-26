@extends('layouts.broker')

@section('title', 'Transactions')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
<div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-section-heading mb-6">
        <div>
            <h2>Transactions</h2>
            <p class="text-sm text-slate-500 mt-1">Your wallet credits and debit history.</p>
        </div>
    </div>

    <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg
                                        {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                        <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down-left' : 'fa-arrow-up-right' }} text-xs"></i>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                                        {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </div>
                            </td>
                            <td class="font-bold text-slate-900">{{ ucfirst($txn->category) }}</td>
                            <td class="font-semibold {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $txn->type === 'credit' ? '+' : '−' }}&#8377;{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="text-slate-500 max-w-[200px]">
                                <span class="block truncate">{{ $txn->description ?: '—' }}</span>
                            </td>
                            <td class="text-slate-500">{{ $txn->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="py-16 text-center">
                                    <i class="fas fa-receipt text-3xl text-slate-200 block mb-3"></i>
                                    <p class="text-sm text-slate-500">No transactions yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($transactions as $txn)
                <div class="p-4 hover:bg-slate-50/60 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl
                                {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down-left' : 'fa-arrow-up-right' }} text-xs"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm">{{ ucfirst($txn->category) }}</h3>
                                <p class="text-xs text-slate-500 truncate">{{ $txn->description ?: $txn->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-sm flex-shrink-0 {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $txn->type === 'credit' ? '+' : '−' }}&#8377;{{ number_format($txn->amount, 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <i class="fas fa-receipt text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No transactions yet.</p>
                </div>
            @endforelse
        </div>

        @if($transactions->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection