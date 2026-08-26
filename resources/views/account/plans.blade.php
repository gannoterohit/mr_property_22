@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', (Auth::user()->role === 'owner' ? 'Room Listing Plans' : (Auth::user()->role === 'broker' ? 'Broker Listing Plans' : 'Contact Unlock Plans')) . ' - ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@if(Auth::user()->role === 'owner')
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}">
    @endpush
@endif
@push('styles')
<link rel="stylesheet" href="{{ asset('css/account-plans.css') }}">
@endpush
@php
    $role = Auth::user()->role;
    $isOwner = $role === 'owner';
    $isBroker = $role === 'broker';
    $isCustomer = $role === 'user';
    $limitField = ($isOwner || $isBroker) ? 'listing_limit' : 'contacts_limit';
    $usageType = ($isOwner || $isBroker) ? 'listing' : 'contact';
    $feeEnabledKey = ($isOwner || $isBroker) ? 'listing_fee_enabled' : 'unlock_fee_enabled';
    $feeEnabled = filter_var(\App\Models\Setting::get($feeEnabledKey, '0'), FILTER_VALIDATE_BOOLEAN);
    $singleFee = $feeEnabled
        ? (float) \App\Models\Setting::get(($isOwner || $isBroker) ? 'listing_fee' : 'unlock_fee', ($isOwner || $isBroker) ? 199 : 49)
        : 0;
    $usedCredits = $activeSubscription?->usages()->where('usage_type', $usageType)->count() ?? 0;
    $activeLimit = $activeSubscription?->plan?->{$limitField};
    $remainingCredits = $activeLimit === -1 ? 'Unlimited' : max(0, (int) $activeLimit - $usedCredits);
    $contentSection = $isOwner ? 'owner-content' : ($isBroker ? 'broker-content' : ($isCustomer ? 'customer-content' : 'content'));
@endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
    @if($activeSubscription)
        <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white"><i class="fas fa-check"></i></span>
                    <div><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Current plan</p><h2 class="font-heading text-lg font-bold text-slate-950">{{ $activeSubscription->plan->name }}</h2></div>
                </div>
                <div class="grid grid-cols-2 gap-6 sm:text-right">
                    <div><p class="text-xs text-slate-500">Credits left</p><p class="text-lg font-extrabold text-slate-950">{{ $remainingCredits }}</p></div>
                    <div><p class="text-xs text-slate-500">Valid until</p><p class="text-sm font-bold text-slate-900">{{ $activeSubscription->end_date->format('d M Y') }}</p></div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between gap-4">
        <div><h2 class="font-heading text-xl font-bold text-slate-950">Available plans</h2><p class="mt-1 text-sm text-slate-500">Transparent pricing. No automatic renewal.</p></div>
        <span class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-slate-500"><i class="fas fa-shield-halved text-emerald-600"></i> Secure payment</span>
    </div>

    @if($plans->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-box-open text-xl"></i></span>
            <h3 class="mt-4 font-heading text-lg font-bold text-slate-900">No plans available right now</h3>
            <p class="mt-2 text-sm text-slate-500">You can still use the single {{ $isBroker || $isOwner ? 'listing' : 'room unlock' }} option.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($plans as $plan)
                @php
                    $limit = (int) $plan->{$limitField};
                    $regularCost = $limit === -1 ? null : $limit * $singleFee;
                    $saving = $regularCost === null ? null : max(0, $regularCost - $plan->price);
                @endphp
                <article class="plan-card {{ $loop->iteration === 2 ? 'popular' : '' }}">
                    @if($loop->iteration === 2)
                        <span class="plan-badge">Most popular</span>
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <div><h3 class="font-heading text-lg font-bold text-slate-950">{{ $plan->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $plan->duration_days }} days validity</p></div>
                         <span class="plan-icon"><i class="fas {{ $isOwner ? 'fa-house-circle-check' : 'fa-address-card' }}"></i></span>
                    </div>
                    <div class="mt-6 flex items-end gap-2"><span class="text-4xl font-extrabold tracking-tight text-slate-950">&#8377;{{ number_format($plan->price) }}</span><span class="pb-1 text-sm text-slate-500">one time</span></div>
                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Includes</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-950">{{ $limit === -1 ? 'Unlimited' : $limit }} {{ $isBroker ? 'broker listings' : ($isOwner ? 'room listings' : 'room unlocks') }}</p>
                        @if($saving > 0)<p class="mt-1 text-xs font-bold text-emerald-600">You save &#8377;{{ number_format($saving) }}</p>@endif
                    </div>
                    <ul class="my-6 space-y-3 text-sm text-slate-600">
                        <li class="flex gap-3"><i class="fas fa-check mt-1 text-emerald-600"></i><span>{{ $isBroker || $isOwner ? 'One credit for every new room' : 'One credit for every unlocked room' }}</span></li>
                        <li class="flex gap-3"><i class="fas fa-check mt-1 text-emerald-600"></i><span>Credits valid for {{ $plan->duration_days }} days</span></li>
                        <li class="flex gap-3"><i class="fas fa-check mt-1 text-emerald-600"></i><span>No automatic renewal</span></li>
                        @foreach(array_slice($plan->benefits ?? [], 0, 2) as $benefit)
                            <li class="flex gap-3"><i class="fas fa-check mt-1 text-emerald-600"></i><span>{{ $benefit }}</span></li>
                        @endforeach
                    </ul>
                     <button type="button" data-plan='@json(['id' => $plan->id, 'name' => $plan->name, 'price' => (float) $plan->price])' class="plan-btn {{ $loop->iteration === 2 ? 'primary' : 'secondary' }}">
                          Choose plan <i class="fas fa-arrow-right text-xs"></i>
                     </button>
                </article>
            @endforeach
        </div>
    @endif

    <div class="mt-8 grid sm:grid-cols-3 gap-3 text-sm text-slate-600">
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><i class="fas fa-lock plan-trust-icon"></i> Secure checkout</div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><i class="fas fa-receipt plan-trust-icon"></i> Payment history saved</div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><i class="fas fa-headset plan-trust-icon"></i> Support available</div>
    </div>
</div>
@include('account.partials.page-styles')
<link rel="stylesheet" href="{{ asset('css/account-plans.css') }}">
<div id="planPaymentModal" class="fixed inset-0 z-[1100] hidden items-end sm:items-center justify-center bg-slate-950/60 p-0 sm:p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-md rounded-t-3xl sm:rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4"><div><p class="plan-modal-title">Complete purchase</p><h3 id="selectedPlanName" class="mt-1 font-heading text-xl font-bold text-slate-950"></h3><p id="selectedPlanPrice" class="mt-1 text-sm text-slate-500"></p></div><button onclick="closePlanPayment()" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200"><i class="fas fa-xmark"></i></button></div>
        <div class="mt-6 space-y-3">
            <button onclick="purchasePlan('online')" class="plan-pay-online flex w-full items-center justify-between rounded-xl p-4 text-left"><span class="flex items-center gap-3"><i class="fas fa-credit-card"></i><span><strong class="block text-sm">Pay online</strong><small class="text-indigo-100">UPI, card or net banking</small></span></span><i class="fas fa-arrow-right text-xs"></i></button>
            <button onclick="purchasePlan('wallet')" class="flex w-full items-center justify-between rounded-xl border border-slate-200 p-4 text-left text-slate-900 hover:bg-slate-50"><span class="flex items-center gap-3"><i class="fas fa-wallet plan-trust-icon"></i><span><strong class="block text-sm">Use wallet balance</strong><small class="text-slate-500">Available &#8377;{{ number_format(Auth::user()->wallet_balance ?? 0, 2) }}</small></span></span><i class="fas fa-arrow-right text-xs text-slate-400"></i></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const planRazorpayKey = @js(\App\Models\Setting::get('razorpay_key', ''));
let selectedPlan = null;

function openPlanPayment(id, name, price) {
    selectedPlan = { id, name, price };
    document.getElementById('selectedPlanName').textContent = name;
    document.getElementById('selectedPlanPrice').textContent = `\u20B9${Number(price).toLocaleString('en-IN')} \u00B7 one-time payment`;
    const modal = document.getElementById('planPaymentModal');
    modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closePlanPayment() { const modal = document.getElementById('planPaymentModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); }

async function purchasePlan(method) {
    if (!selectedPlan) return;
    const planId = selectedPlan.id; closePlanPayment();
    try {
        const response = await fetch(@js(route('subscription.purchase')), { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':@js(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: JSON.stringify({plan_id: planId, payment_method: method}) });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Unable to purchase this plan');
        if (data.wallet_used) { toastr.success('Plan activated successfully'); return setTimeout(() => location.reload(), 900); }
        await payForPlan(data.payment_id);
    } catch (error) { toastr.error(error.message || 'Something went wrong'); }
}

async function payForPlan(paymentId) {
    if (!planRazorpayKey) throw new Error('Online payment is not configured');
    const RazorpayClass = await loadRazorpaySDK();
    const orderResponse = await fetch(@js(route('razorpay.createOrder')), { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@js(csrf_token()),'Accept':'application/json'}, body:JSON.stringify({payment_id: paymentId}) });
    const order = await orderResponse.json();
    if (!orderResponse.ok || !order.success) throw new Error(order.message || 'Unable to start payment');
    new RazorpayClass({ key: planRazorpayKey, amount: order.amount * 100, currency:'INR', name:@js(\App\Models\Setting::get('website_name', 'RoomRental')), description:'Subscription Plan', order_id:order.order_id,
        handler: async function(result) {
            const verifyResponse = await fetch(@js(route('razorpay.verify')), { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':@js(csrf_token()),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body:JSON.stringify({...result, payment_id:paymentId}) });
            const verified = await verifyResponse.json();
            if (!verifyResponse.ok || verified.status !== 'success') return toastr.error(verified.message || 'Payment verification failed');
            toastr.success('Plan activated successfully'); setTimeout(() => location.reload(), 1000);
        }, theme:{color: '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}'}, prefill:{name:@js(Auth::user()->name), email:@js(Auth::user()->email)} }).open();
}

document.getElementById('planPaymentModal').addEventListener('click', e => { if (e.target.id === 'planPaymentModal') closePlanPayment(); });

document.querySelectorAll('[data-plan]').forEach(btn => {
    btn.addEventListener('click', () => {
        try {
            const plan = JSON.parse(btn.dataset.plan);
            openPlanPayment(plan.id, plan.name, plan.price);
        } catch (err) {
            console.error('Invalid plan data', err);
        }
    });
});
</script>
@endpush
