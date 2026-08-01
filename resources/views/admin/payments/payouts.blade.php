@extends('layouts.admin')

@section('title', 'Payout Requests - Admin')

@section('admin-content')
<div class="min-h-screen bg-gray-50 uppercase-none">
    <div class="flex">
        {{-- SIDEBAR --}}
        {{-- MAIN --}}
        <div class="flex-1 min-w-0 p-4 md:p-8">
            {{-- HEADER --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 admin-theme-soft rounded-full blur-3xl -mr-16 -mt-16 transition-all"></div>
                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center">
                            <i class="fas fa-hand-holding-usd admin-theme-text mr-4 p-3 admin-theme-soft rounded-2xl"></i>
                            Payout Requests
                        </h1>
                        <p class="text-slate-500 mt-2 font-medium">Manage and process transfers to property owners</p>
                    </div>
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-widest">Reference / Date</th>
                                <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-widest">Owner Details</th>
                                <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                <th class="px-8 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-5 text-right text-[11px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payouts as $payout)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="text-sm font-bold text-slate-900">#PAY-{{ $payout->id }}</div>
                                    <div class="text-[11px] text-slate-400 font-bold mt-1 uppercase tracking-tight">
                                        {{ $payout->created_at->format('d M Y, h:i A') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-xl admin-theme-soft flex items-center justify-center admin-theme-text font-black mr-3">
                                            {{ substr($payout->owner->name ?? 'N', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">{{ $payout->owner->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-400">{{ $payout->owner->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-lg font-black text-slate-900">₹{{ number_format($payout->amount, 2) }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5">
                                        Ref: {{ $payout->booking_id ? 'Booking #'.$payout->booking_id : 'Manual Request' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'completed' => 'bg-emerald-100 text-emerald-700',
                                            'failed' => 'bg-rose-100 text-rose-700',
                                        ];
                                        $currentClass = $statusClasses[$payout->status] ?? 'bg-slate-100 text-slate-700';
                                    @endphp
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-current {{ $currentClass }}">
                                        {{ $payout->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    @if($payout->status == 'pending')
                                    <form action="{{ route('admin.payouts.process', $payout->id) }}" method="POST" class="admin-confirm inline" data-confirm-title="Process this payout?" data-confirm-text="The payout will be marked as processed and finalized." data-confirm-button="Yes, process payout" data-confirm-color="#059669">
                                        @csrf
                                        <button type="submit" 
                                                class="px-6 py-2.5 admin-theme-bg hover:bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md hover:shadow-xl transform hover:-translate-y-0.5">
                                            Process Payout
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-slate-300 pointer-events-none">
                                        <i class="fas fa-check-double mr-1"></i> Finalized
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mb-4 border border-slate-100">
                                            <i class="fas fa-inbox text-slate-300 text-3xl"></i>
                                        </div>
                                        <p class="text-slate-500 font-bold">No payout requests found at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payouts->hasPages())
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $payouts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Table Styling */
    .divide-slate-100 > :not([hidden]) ~ :not([hidden]) {
        border-color: #f1f5f9;
    }
    nav svg {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endsection
