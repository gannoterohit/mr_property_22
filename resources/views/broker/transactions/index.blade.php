@extends('layouts.broker')

@section('title', 'Transactions')

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
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Transactions</h1>
            </div>
        </header>
    </header>
    <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mt-6">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="px-5 text-left">Type</th><th class="px-5 text-left">Category</th><th class="px-5 text-left">Amount</th><th class="px-5 text-left">Description</th><th class="px-5 text-left">Date</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($transactions as $txn)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ ucfirst($txn->type) }}</span></td>
                                    <td class="px-5 py-3 font-bold text-slate-900">{{ ucfirst($txn->category) }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $txn->type === 'credit' ? '+' : '-' }}&#8377;{{ number_format($txn->amount, 2) }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $txn->description ?: '-' }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $txn->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-16 text-center text-slate-500">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
@endsection