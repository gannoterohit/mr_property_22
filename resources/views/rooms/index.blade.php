@extends('layouts.public')

@section('title', (request('city') ? 'Verified Rooms & PG in ' . request('city') : 'Browse Rooms & PG for Rent') . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', (request('city') ? 'Find the best verified rooms, apartments, and PG in ' . request('city') . '. Browse listings with photos, rents, and owner contacts.' : 'Browse verified room listings in your city. Find apartments, houses, and rooms for rent with verified owners.'))
@section('keywords', (request('city') ? 'pg in ' . request('city') . ', room for rent in ' . request('city') . ', ' : '') . 'browse rooms, room listings, ' . \App\Models\Setting::get('seo_meta_keywords', 'apartment, house, property'))

@php
    $cityContext = $cityContext ?? ['isFallback' => false, 'activeCityName' => request('city') ?? session('user_city'), 'launchingSoonCityName' => null];
    $displayCity = $cityContext['launchingSoonCityName'] ?? $cityContext['activeCityName'] ?? request('city') ?? session('user_city');
    $hasMetaSearchIntent = request()->hasAny(['city', 'min_rent', 'max_rent', 'min_area_sqft', 'max_area_sqft', 'property_type_id', 'property_category_id', 'tenant_type', 'furnishing_type', 'available_now', 'availability_from']);
@endphp

@push('styles')
@include('partials.listings-ld')
<style>
    @media (max-width: 1023px) {
        .navbar, footer { display: none !important; }
        body { padding-bottom: 70px; background-color: #f8fafc; }
    }
    .custom-shadow {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
    }
    .filter-sticky {
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    .rooms-filter-form {
        padding-bottom: 74px;
    }
    .rooms-amenities-scroll {
        max-height: 190px;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
    }
    .rooms-amenities-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .rooms-amenities-scroll::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 999px;
    }
    .rooms-amenities-scroll::-webkit-scrollbar-thumb {
        background: rgba(var(--primary-rgb), .5);
        border-radius: 999px;
    }
    .rooms-filter-actions {
        position: sticky;
        bottom: 0;
        z-index: 8;
        margin: 0 -1.25rem -1.25rem;
        padding: 12px 1.25rem;
        border-top: 1px solid #e2e8f0;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(10px);
    }
    /* Scrollbar styling for sidebar */
    .filter-sticky {
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--primary-rgb), .35) transparent;
    }
    .filter-sticky::-webkit-scrollbar { width: 4px; }
    .filter-sticky::-webkit-scrollbar-track { background: transparent; }
    .filter-sticky::-webkit-scrollbar-thumb { background: rgba(var(--primary-rgb), .35); border-radius: 999px; }
    .filter-sticky::-webkit-scrollbar-thumb:hover { background: rgba(var(--primary-rgb), .58); }

    .rooms-search-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        padding: 1rem 0;
    }
    .rooms-search-panel {
        border-radius: 1.125rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .055);
    }
    .rooms-main {
        background: #f8fafc;
        min-height: 65vh;
    }
    .rooms-filter-panel {
        border-color: #e2e8f0;
        border-radius: 1.125rem !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
    }
    .rooms-results-head {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: .9rem 1rem;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .035);
    }
    .room-listing-card {
        border-color: #e2e8f0;
        border-radius: 1.125rem !important;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
    }
    .room-listing-card:hover {
        border-color: color-mix(in srgb, var(--primary) 35%, #e2e8f0);
        box-shadow: 0 18px 38px rgba(15, 23, 42, .11);
    }
    .room-listing-card .room-image {
        height: 13.25rem;
    }
    .room-listing-card .room-card-body {
        padding: 1.125rem;
    }
    .rooms-search-shell > .container,
    .rooms-main > .container {
        max-width: 1320px !important;
    }
    .rooms-search-panel input,
    .rooms-search-panel select {
        min-height: 42px;
        font-size: .8125rem !important;
    }
    .rooms-search-panel button[type="submit"] {
        min-height: 42px;
        font-size: .8125rem !important;
    }
    .rooms-search-panel input:focus,
    .rooms-search-panel select:focus,
    .rooms-filter-panel input:focus,
    .rooms-filter-panel select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .14);
    }
    .rooms-search-panel button[type="submit"],
    .rooms-filter-panel button[type="submit"] {
        background: var(--primary);
        color: #fff;
    }
    .rooms-search-panel button[type="submit"]:hover,
    .rooms-filter-panel button[type="submit"]:hover {
        background: var(--primary-dark);
    }
    .rooms-filter-panel input[type="checkbox"],
    .rooms-filter-panel input[type="radio"] {
        accent-color: var(--primary);
    }
    .rooms-filter-panel label:hover,
    .rooms-breadcrumb-link:hover,
    .rooms-theme-link {
        color: var(--primary);
    }
    .rooms-filter-panel form > div {
        padding-bottom: 1.15rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .rooms-filter-panel form > div:last-of-type {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .rooms-filter-panel label {
        line-height: 1.35;
    }
    .rooms-results-head h2 {
        font-size: 1.5rem !important;
        letter-spacing: -.025em;
    }
    .room-listing-card h3 {
        font-size: .9375rem !important;
        line-height: 1.4;
        min-height: 2.625rem;
    }
    .room-listing-card .room-card-body > div.flex.items-center {
        font-size: .8125rem !important;
    }
    .room-listing-card .room-card-body > a,
    .room-listing-card .room-card-body button {
        min-height: 40px;
        font-size: .8125rem !important;
    }
    .room-listing-card .room-card-body .flex.flex-wrap span {
        font-size: .625rem !important;
        padding: .35rem .5rem !important;
    }
    .city-fallback-banner { border: 1px solid #fed7aa; background: #fff7ed; color: #9a3412; border-radius: 1rem; padding: .95rem 1rem; }
    @media (max-width: 1279px) {
        .room-listing-card .room-image { height: 13rem; }
    }
    @media (min-width: 1024px) and (max-width: 1279px) {
        .rooms-main > .container > div:last-child { gap: 1.25rem !important; }
    }
    @media (max-width: 767px) {
        .rooms-main { padding-top: .75rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .room-listing-card, .room-listing-card img { transition: none !important; }
        .room-listing-card:hover { transform: none !important; }
    }
    /* Reference-led rooms directory */
    .rooms-search-shell{padding:1.5rem 0;background:#f8fafc}
    .rooms-search-panel{padding:1.15rem 1.25rem!important;border-radius:1rem;box-shadow:0 12px 34px rgba(15,23,42,.07)}
    .rooms-search-panel form{gap:1rem!important;flex-wrap:nowrap!important}
    .rooms-search-panel form>div{min-width:0!important;border-right:0!important;padding-right:0!important}
    .rooms-search-panel form>div:last-child{width:160px!important;min-width:160px!important}
    .rooms-search-panel label{color:#64748b!important;font-size:.65rem!important;letter-spacing:.04em!important;text-transform:none!important}
    .rooms-search-panel input,.rooms-search-panel select{height:46px;border-radius:.65rem!important;background:#fff!important}
    .rooms-search-panel button[type="submit"]{height:46px;border-radius:.65rem!important}
    .rooms-main{background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
    .rooms-main>.container{padding-top:1.4rem!important;padding-bottom:3rem!important}
    .rooms-filter-panel{padding:1.25rem!important;border-radius:1rem!important;box-shadow:0 8px 25px rgba(15,23,42,.05)}
    .rooms-filter-panel h3{font-size:1.1rem!important}
    .rooms-filter-panel form{font-size:.78rem}
    .rooms-filter-panel input[type="text"],.rooms-filter-panel input[type="number"],.rooms-filter-panel input[type="date"],.rooms-filter-panel select{min-height:40px;border-radius:.6rem!important}
    .rooms-results-head{padding:.25rem 0 1rem;border:0;background:transparent;box-shadow:none}
    .rooms-results-head h2{font-size:1.65rem!important}
    .rooms-results-head h2+span{display:block;margin-top:.25rem;color:var(--primary)}
    .room-listing-card{border-radius:.9rem!important;box-shadow:0 8px 24px rgba(15,23,42,.07)}
    .room-listing-card:hover{transform:translateY(-5px)!important;box-shadow:0 20px 42px rgba(15,23,42,.13)}
    .room-listing-card .room-image{height:14.5rem}
    .room-listing-card .room-card-body{padding:1rem}
    .room-listing-card h3{min-height:2.7rem;font-size:1rem;color:var(--primary)}
    .room-price-tag{display:inline-flex;align-items:baseline;gap:.2rem;border:1px solid rgba(var(--primary-rgb),.2);background:#fff;color:var(--primary);box-shadow:0 8px 18px rgba(15,23,42,.13)}
    .room-price-tag span:last-child{color:#64748b}
    .room-theme-type-badge{color:var(--primary)}
    .room-theme-secondary-badge{background:var(--secondary);color:#fff}
    .room-theme-primary-icon{color:rgba(var(--primary-rgb),.65)}
    .room-theme-secondary-dot{background:var(--secondary)}
    .room-theme-secondary-text{color:var(--secondary)}
    .room-theme-primary-button{background:var(--primary);color:#fff}
    .room-theme-primary-button:hover{background:var(--primary-dark)}
    .room-theme-primary-soft{background:rgba(var(--primary-rgb),.1);color:var(--primary)}
    .room-theme-secondary-soft{background:rgba(var(--secondary-rgb),.1);color:var(--secondary)}
    .room-theme-alert-box{border-color:rgba(var(--primary-rgb),.12);background:rgba(var(--primary-rgb),.04)}
    .room-owner-row{display:flex;align-items:center;gap:.55rem;margin:.15rem 0 .85rem;padding-top:.75rem;border-top:1px solid #eef2f7}
    .room-owner-row img{width:1.65rem;height:1.65rem;border-radius:999px;object-fit:cover;background:#eef2ff}
    .room-owner-row span{font-size:.7rem;color:#64748b}
    .room-owner-row strong{color:#334155}
    .rooms-trust-strip{margin-top:0!important;padding:2.4rem 0!important;border-top:1px solid #e8edf5!important;background:#f8fafc!important;color:#0f172a!important}
    .rooms-trust-strip>.container{max-width:1320px}
    .rooms-trust-strip .rooms-trust-grid{gap:1rem!important}
    .rooms-trust-strip .rooms-trust-item{padding:1rem;border:1px solid #e2e8f0;border-radius:.85rem;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.035)}
    .rooms-trust-strip .rooms-trust-item span:first-child{color:#0f172a!important;font-size:.78rem!important}
    .rooms-trust-strip .rooms-trust-item span:last-child{color:#64748b!important;font-size:.68rem!important}
    @media(max-width:1199px){
        .rooms-search-panel form{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))}
        .rooms-search-panel form>div:last-child{width:auto!important;grid-column:1/-1}
        .room-listing-card .room-image{height:13.25rem}
    }
    @media(max-width:1023px){
        .rooms-main>.container{padding-inline:1rem!important}
    }
    @media(max-width:767px){
        .rooms-results-head{align-items:flex-start!important}
        .rooms-results-head h2{font-size:1.35rem!important}
        .rooms-trust-strip{display:none}
    }
</style>
@endpush

@section('content')
@include('rooms.partials.index.search-header')

<!-- ===== MAIN CONTAINER ===== -->
<div class="rooms-main">
<div class="container mx-auto px-4 sm:px-6 py-6">
    @if($cityContext['isFallback'])
        <div class="city-fallback-banner mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <strong class="block text-sm">Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong>
                <span class="text-xs">We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span>
            </div>
            <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}" class="rooms-theme-link text-xs font-black">View {{ $cityContext['activeCityName'] }}</a>
        </div>
    @endif

    <!-- Breadcrumb -->
    <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-4 font-semibold">
        <a href="{{ url('/') }}" class="rooms-breadcrumb-link transition-colors">Home</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span class="text-slate-600">Rooms in {{ $displayCity ?? 'India' }}</span>
    </div>

    <!-- Outer container (Flexbox for robust layout) -->
    <div class="flex flex-col lg:flex-row gap-6 xl:gap-7 items-start">

        @include('rooms.partials.index.filter-sidebar')

        <!-- ===== RIGHT COLUMN (ROOMS GRID) ===== -->
        <div class="flex-grow min-w-0">
            @include('rooms.partials.index.results-header')

            @include('rooms.partials.index.rooms-list')

        </div>

    </div>
</div>
</div>

@include('rooms.partials.index.trust-strip')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hasSearchIntent = @json($hasMetaSearchIntent);
    if (hasSearchIntent) {
        window.trackRoomNestEvent?.('Search', {
            search_string: @json(request('city') ?? session('user_city') ?? ''),
            city: @json($displayCity ?? ''),
            content_type: 'room'
        });
    }
});
</script>
@auth
    @if(Auth::user()->role === 'owner')
        @push('sweetalert')
            <script src="{{ asset('assets/js/sweetalert2.min.js') }}" defer></script>
        @endpush
    @endif
    <script defer>
    async function toggleWishlist(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        try {
            const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to toggle wishlist');
            const data = await response.json();

            if (data.success) {
                const updateBtn = (id) => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        const icon = btn.querySelector('i');
                        if (data.status === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas', 'text-red-500');
                        } else {
                            icon.classList.remove('fas', 'text-red-500');
                            icon.classList.add('far');
                        }
                    }
                };
                updateBtn(`wishlist-btn-${roomId}`);
                updateBtn(`wishlist-btn-mobile-${roomId}`);
            }
        } catch (error) {
            console.error(error);
        }
    }
    const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-room-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const result = await Swal.fire({
                    title: 'Delete Room?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (!result.isConfirmed) return;
                
                const formData = new FormData(this);
                const roomId = this.dataset.roomId;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    }
                    
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to delete room');
                        }
                        return data;
                    } else {
                        const text = await response.text();
                        throw new Error(text || 'Invalid response from server');
                    }
                })
                .then(data => {
                    if (data.success) {
                        toastr.success('Room deleted successfully', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(data.message || 'Failed to delete room', 'Error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error(error.message || 'Failed to delete room', 'Error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });
    });

    async function markBooked(roomId) {
        const result = await Swal.fire({
            title: 'Mark as Rented?',
            text: "This room will be hidden from users.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: ROOM_PRIMARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch(`{{ route('rooms.markBooked', ':id') }}`.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire(
                    'Rented!',
                    'Room has been marked as rented.',
                    'success'
                ).then(() => {
                    location.reload();
                });
            } else {
                toastr.error(data.message || 'Failed to mark room as rented', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function markAvailable(roomId) {
        const result = await Swal.fire({
            title: 'Make Available?',
            text: "Making this room available will charge listing fee.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: ROOM_SECONDARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Continue'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch(`{{ route('rooms.markAvailable', ':id') }}`.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.subscription_used) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room made available using subscription!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else if (data.payment_id) {
                    await initiatePayment(data.payment_id, data.amount, 'listing', roomId);
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room marked as available.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } else {
                toastr.error(data.message || 'Failed to make room available', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function initiatePayment(paymentId, amount, type, referenceId) {
        try {
            const Razorpay = await loadRazorpaySDK();

            if (!razorpayKey || razorpayKey === '' || razorpayKey === 'null') {
                toastr.error('Razorpay key not configured. Please add it in Business Settings.', 'Error');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const orderResponse = await fetch('{{ route("razorpay.createOrder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ payment_id: paymentId }),
                credentials: 'same-origin'
            });
            
            if (!orderResponse.ok) {
                const errorData = await orderResponse.json().catch(() => ({ message: 'Failed to create order' }));
                throw new Error(errorData.message || 'Failed to create order');
            }
            
            const orderData = await orderResponse.json();
            
            if (!orderData.success || !orderData.order_id) {
                throw new Error(orderData.message || 'Failed to create order');
            }
            
            const options = {
                key: razorpayKey,
                amount: orderData.amount * 100,
                currency: 'INR',
                name: '{{ \App\Models\Setting::get("website_name", "RoomRental") }}',
                description: 'Make Room Available - Listing Fee',
                order_id: orderData.order_id,
                handler: async function(response) {
                    if (!response.razorpay_order_id || !response.razorpay_signature) {
                        alert('Payment failed: Missing order ID or signature. Please try again.');
                        return;
                    }

                    try {
                        const verifyResponse = await fetch('{{ route("razorpay.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id || orderData.order_id,
                                razorpay_signature: response.razorpay_signature,
                                payment_id: paymentId,
                                type: type,
                                reference_id: referenceId
                            }),
                            credentials: 'same-origin'
                        });
                        
                        if (!verifyResponse.ok) {
                            const errorData = await verifyResponse.json().catch(() => ({ message: 'Payment verification failed' }));
                            throw new Error(errorData.message || 'Payment verification failed');
                        }
                        
                        const verifyData = await verifyResponse.json();
                        
                        if (verifyData.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Payment successful! Room is now available.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            toastr.error(verifyData.message || 'Payment verification failed', 'Error');
                        }
                    } catch (error) {
                        console.error('Verification error:', error);
                        toastr.error(error.message || 'Payment verification failed', 'Error');
                    }
                },
                prefill: {
                    name: '{{ Auth::user()->name ?? "" }}',
                    email: '{{ Auth::user()->email ?? "" }}'
                },
                theme: {
                    color: ROOM_PRIMARY_COLOR
                },
                method: {
                    upi: true,
                    card: true,
                    netbanking: true,
                    wallet: true
                }
            };
            
            const razorpay = new Razorpay(options);
            razorpay.on('payment.failed', function(response) {
                toastr.error('Payment failed: ' + (response.error.description || 'Unknown error'), 'Payment Failed');
            });
            razorpay.open();
            
        } catch (error) {
            console.error('Payment error:', error);
            toastr.error('Payment initialization failed: ' + error.message, 'Error');
        }
    }
    async function subscribeToAlerts(city) {
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        const btn = document.getElementById('notify-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';

        try {
            const response = await fetch('{{ route("city-alerts.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ city })
            });

            const data = await response.json();
            if (data.success) {
                toastr.success(data.message, 'Success');
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Subscribed';
                btn.classList.replace('bg-indigo-50', 'bg-green-50');
                btn.classList.replace('text-indigo-700', 'text-green-700');
            } else {
                toastr.error(data.message || 'Failed to subscribe', 'Error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong. Please try again.', 'Error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
    </script>
@endauth

<!-- Auto-City Detection -->
<script>
const ROOM_PRIMARY_COLOR = '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}';
const ROOM_SECONDARY_COLOR = '{{ \App\Models\Setting::get("secondary_color", "#10B981") }}';
    async function detectLocation(force = false) {
        if (!navigator.geolocation) return;

        const cityInput = document.getElementById('hero-city-input');
        const originalPlaceholder = cityInput ? cityInput.placeholder : '';
        if (cityInput) cityInput.placeholder = 'Detecting location...';

        navigator.geolocation.getCurrentPosition(async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                const data = await response.json();
                const city = data.address.city || data.address.town || data.address.village || data.address.suburb || data.address.state_district;
                
                if (city) {
                    if (cityInput) {
                        cityInput.value = city;
                        cityInput.placeholder = originalPlaceholder;
                    }
                    await fetch(`{{ route('set-city') }}?city=${encodeURIComponent(city)}&lat=${lat}&lng=${lng}&verified=true`);
                    window.location.href = window.location.pathname + `?lat=${lat}&lng=${lng}&city=${encodeURIComponent(city)}`;
                } else if (cityInput) {
                    cityInput.placeholder = originalPlaceholder;
                }
            } catch (error) {
                console.error('Location error:', error);
                if (cityInput) cityInput.placeholder = originalPlaceholder;
            }
        }, (error) => {
            console.warn('Geolocation failed:', error);
            if (cityInput) cityInput.placeholder = 'Location denied. Type city manually.';
        });
    }

    @if(!request('city') && !session('user_city') && !session('no_auto'))
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => detectLocation(), 2000);
        });
    @endif
</script>

<script defer>
    document.addEventListener('DOMContentLoaded', () => {
        detectUserLocation((coords) => {
            const tags = document.querySelectorAll('.distance-tag');
            tags.forEach(tag => {
                const roomLat = parseFloat(tag.dataset.lat);
                const roomLng = parseFloat(tag.dataset.lng);
                
                if (roomLat && roomLng) {
                    const dist = calculateDistance(coords.lat, coords.lng, roomLat, roomLng);
                    if (dist) {
                        const kmSpan = tag.querySelector('.distance-km');
                        if (kmSpan) kmSpan.textContent = dist;
                        tag.classList.remove('hidden');
                    }
                }
            });
        });
    });
</script>

<!-- Infinite Scroll Script for Mobile -->
<script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileRoomList = document.getElementById('mobile-room-list');
        const loader = document.getElementById('infinite-loader');
        let isLoading = false;
        let hasMore = @json($rooms->hasMorePages());
        let nextPage = @json($rooms->currentPage() + 1);

        if (!mobileRoomList || !loader) return;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !isLoading && hasMore) {
                loadMoreRooms();
            }
        }, { threshold: 0.1 });

        observer.observe(loader);

        async function loadMoreRooms() {
            isLoading = true;
            loader.classList.remove('hidden');

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('page', nextPage);

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load rooms');

                const data = await response.json();
                
                if (data.html) {
                    mobileRoomList.insertAdjacentHTML('beforeend', data.html);
                    nextPage++;
                    hasMore = data.hasMore;
                    
                    if (!hasMore) {
                        loader.remove();
                        observer.disconnect();
                    }
                }
            } catch (error) {
                console.error('Error loading more rooms:', error);
            } finally {
                isLoading = false;
                if (hasMore) {
                    loader.classList.add('hidden');
                }
            }
        }
    });
</script>
@endpush
@endsection
