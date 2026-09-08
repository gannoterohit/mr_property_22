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
            @if($broker->broker_verification_status === 'approved' || $broker->is_broker_active)
                <form action="{{ route('admin.brokers.toggle-featured', $broker) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition {{ $broker->is_featured_agency ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-white border border-amber-300 text-amber-800 hover:bg-amber-50' }}">
                        <i class="{{ $broker->is_featured_agency ? 'fas' : 'far' }} fa-star"></i>
                        <span>{{ $broker->is_featured_agency ? 'Featured Agency (Spotlight ON)' : 'Spotlight as Featured Agency' }}</span>
                    </button>
                </form>
                <a href="{{ route('agency.show', $broker) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-xs">
                    <i class="fas fa-globe"></i> View Public Agency Page <i class="fas fa-external-link-alt text-xs"></i>
                </a>
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

            {{-- Client Reviews & Moderation Card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Client Reviews ({{ $reviews->total() }})</h3>
                        <p class="text-xs text-slate-500">Manage, hide or remove public reviews submitted for this agency.</p>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-black">
                        <i class="fas fa-star text-amber-500"></i>
                        <span>{{ number_format($broker->broker_rating ?: 5.0, 1) }} / 5.0</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="px-4 text-left pb-3">Client</th>
                                <th class="px-4 text-left pb-3">Rating</th>
                                <th class="px-4 text-left pb-3">Feedback</th>
                                <th class="px-4 text-left pb-3">Date</th>
                                <th class="px-4 text-left pb-3">Status</th>
                                <th class="px-4 text-right pb-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($reviews as $rev)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($rev->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div>{{ $rev->user->name ?? 'Deleted User' }}</div>
                                                <div class="text-[10px] text-slate-400 font-normal">{{ $rev->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-amber-500 font-bold whitespace-nowrap">
                                        {{ $rev->rating }} <i class="fas fa-star text-xs"></i>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 max-w-xs">
                                        <p class="text-xs line-clamp-2" title="{{ $rev->comment }}">{{ $rev->comment ?: '(Rating only)' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                        {{ $rev->created_at ? $rev->created_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $rev->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst($rev->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <form action="{{ route('admin.brokers.reviews.toggle-status', [$broker, $rev]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition" title="{{ $rev->status === 'approved' ? 'Hide Review' : 'Approve Review' }}">
                                                    <i class="fas {{ $rev->status === 'approved' ? 'fa-eye-slash text-amber-600' : 'fa-eye text-emerald-600' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.brokers.reviews.destroy', [$broker, $rev]) }}" method="POST" class="admin-confirm inline" data-confirm-title="Delete this review?" data-confirm-text="This will permanently delete this client review." data-confirm-button="Yes, delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition" title="Delete Review">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No client reviews submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($reviews->hasPages())
                    <div class="mt-4">{{ $reviews->links() }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            {{-- Featured Agency Spotlight Manager Card --}}
            <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/50 via-white to-white p-6 shadow-xs">
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-crown"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Featured Spotlight</h3>
                        <p class="text-[11px] text-slate-500">Pin agency to top of directory &amp; homepage</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 mb-4">
                    <div class="flex items-center justify-between text-xs font-bold mb-1">
                        <span class="text-slate-500">Current Status:</span>
                        @if($broker->is_featured_agency)
                            <span class="text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md font-black">
                                <i class="fas fa-check-circle text-amber-600 mr-1"></i>Active Spotlight
                            </span>
                        @else
                            <span class="text-slate-500 bg-slate-200 px-2 py-0.5 rounded-md">
                                Inactive
                            </span>
                        @endif
                    </div>
                    @if($broker->is_featured_agency)
                        <div class="text-[11px] text-slate-600 mt-1">
                            @if($broker->featured_agency_expires_at)
                                <i class="far fa-clock text-amber-500 mr-1"></i>Expires: <strong>{{ $broker->featured_agency_expires_at->format('M d, Y') }}</strong> ({{ $broker->featured_agency_expires_at->diffForHumans() }})
                            @else
                                <i class="fas fa-infinity text-emerald-500 mr-1"></i>Permanent (No Expiry Date)
                            @endif
                        </div>
                    @endif
                </div>

                <form action="{{ route('admin.brokers.set-featured', $broker) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Spotlight Duration</label>
                        <select name="duration" class="w-full rounded-xl border-slate-200 text-xs font-semibold focus:ring-amber-500 focus:border-amber-500 p-2.5">
                            <option value="1_month" {{ $broker->is_featured_agency ? 'selected' : '' }}>1 Month Spotlight</option>
                            <option value="3_months">3 Months Spotlight</option>
                            <option value="6_months">6 Months Spotlight</option>
                            <option value="1_year">1 Year Spotlight</option>
                            <option value="lifetime">Permanent / Lifetime Spotlight</option>
                            @if($broker->is_featured_agency)
                                <option value="remove">Remove / Turn Off Spotlight</option>
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-amber-600 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Update Spotlight Plan</span>
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Broker Info</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Agency</dt><dd class="font-bold text-slate-900">{{ $broker->agency_name ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">License</dt><dd class="font-bold text-slate-900">{{ $broker->broker_license ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">GST</dt><dd class="font-bold text-slate-900">{{ $broker->agency_gst ?: '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Active</dt><dd class="font-bold text-slate-900">{{ $broker->is_broker_active ? 'Yes' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Featured Spotlight</dt><dd class="font-bold {{ $broker->is_featured_agency ? 'text-amber-600' : 'text-slate-500' }}">{{ $broker->is_featured_agency ? 'Yes (Active)' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Client Rating</dt><dd class="font-bold text-amber-600">★ {{ number_format($broker->broker_rating ?: 5.0, 1) }} ({{ $broker->broker_reviews_count }})</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_total_listings }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Active Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_active_listings }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Featured Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_featured_listings }}</dd></div>
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