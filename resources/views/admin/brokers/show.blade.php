@extends('layouts.admin')

@section('title', 'Broker Details')

@section('admin-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-full bg-slate-200 flex items-center justify-center text-xl font-bold text-slate-600">{{ substr($broker->name, 0, 1) }}</div>
            <div>
                <p class="admin-theme-text text-xs font-bold uppercase tracking-wider">Broker Profile</p>
                <h2 class="text-2xl font-bold text-slate-950">{{ $broker->name }}</h2>
                <p class="text-sm text-slate-500">{{ $broker->email }} · {{ $broker->phone }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($broker->broker_verification_status === 'pending')
                <form action="{{ route('admin.brokers.approve', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Approve broker?" data-confirm-button="Yes, approve">@csrf @method('POST')<button type="submit" class="admin-theme-bg inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm"><i class="fas fa-check"></i> Approve</button></form>
                <form action="{{ route('admin.brokers.reject', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Reject broker?" data-confirm-text="Please provide a reason." data-confirm-button="Yes, reject">@csrf @method('POST')<input type="hidden" name="reason" value="Rejected by admin"><button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-100"><i class="fas fa-times"></i> Reject</button></form>
            @endif
            @if($broker->broker_verification_status === 'approved')
                <form action="{{ route('admin.brokers.suspend', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Suspend broker?" data-confirm-button="Yes, suspend">@csrf @method('POST')<button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-700 hover:bg-amber-100"><i class="fas fa-pause"></i> Suspend</button></form>
            @endif
            @if($broker->broker_verification_status === 'suspended')
                <form action="{{ route('admin.brokers.activate', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Activate broker?" data-confirm-button="Yes, activate">@csrf @method('POST')<button type="submit" class="admin-theme-bg inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm"><i class="fas fa-play"></i> Activate</button></form>
            @endif
            <a href="{{ route('admin.brokers.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    @if($broker->broker_rejected_reason)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <strong>Rejection Reason:</strong> {{ $broker->broker_rejected_reason }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Properties ({{ $properties->total() }})</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr><th class="px-4 text-left pb-3">Title</th><th class="px-4 text-left pb-3">City</th><th class="px-4 text-left pb-3">Status</th><th class="px-4 text-left pb-3">Listed</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($properties as $property)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $property->title }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $property->city }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $property->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($property->status) }}</span></td>
                                    <td class="px-4 py-3 text-slate-500">{{ $property->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No properties listed yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $properties->links() }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Recent Payments</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr><th class="px-4 text-left pb-3">Type</th><th class="px-4 text-left pb-3">Amount</th><th class="px-4 text-left pb-3">Status</th><th class="px-4 text-left pb-3">Date</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ ucfirst($payment->payment_type) }}</td>
                                    <td class="px-4 py-3 text-slate-600">&#8377;{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payment->status) }}</span></td>
                                    <td class="px-4 py-3 text-slate-500">{{ $payment->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Broker Info</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Agency</dt><dd class="font-bold text-slate-900">{{ $broker->agency_name ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">License</dt><dd class="font-bold text-slate-900">{{ $broker->broker_license ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">GST</dt><dd class="font-bold text-slate-900">{{ $broker->agency_gst ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Active</dt><dd class="font-bold text-slate-900">{{ $broker->is_broker_active ? 'Yes' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_total_listings }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Active Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_active_listings }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Featured</dt><dd class="font-bold text-slate-900">{{ $broker->broker_featured_listings }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Subscription</h3>
                @if($broker->brokerSubscription)
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Plan</dt><dd class="font-bold text-slate-900">{{ $broker->brokerSubscription->plan->name ?? 'N/A' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-bold text-slate-900">{{ ucfirst($broker->brokerSubscription->status) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Expires</dt><dd class="font-bold text-slate-900">{{ $broker->brokerSubscription->expires_at?->format('M d, Y') ?? 'N/A' }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-slate-500">No active subscription.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection