{{-- Payment Selection Modal --}}
<div id="paymentSelectionModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] p-6 max-w-sm w-full shadow-2xl transform transition-all scale-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600"><i class="fas fa-coins text-2xl"></i></div>
            <h3 class="text-xl font-black text-gray-900">Select Payment Method</h3>
            <p class="text-gray-500 text-sm mt-1">Amount to Pay: <span id="payAmount" class="font-bold text-gray-900">₹0</span></p>
        </div>
        <div class="space-y-3">
            <button onclick="confirmPaymentSelection('wallet')" class="w-full group relative flex items-center p-4 border-2 border-gray-100 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 mr-3 text-lg group-hover:scale-110 transition-transform"><i class="fas fa-wallet"></i></div>
                <div class="text-left"><p class="font-bold text-gray-900 group-hover:text-indigo-700">Wallet Balance</p><p class="text-xs text-gray-500">Available: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</p></div>
                <i class="fas fa-arrow-right ml-auto text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </button>
            <button onclick="confirmPaymentSelection('online')" class="w-full group relative flex items-center p-4 border-2 border-gray-100 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mr-3 text-lg group-hover:scale-110 transition-transform"><i class="fas fa-credit-card"></i></div>
                <div class="text-left"><p class="font-bold text-gray-900 group-hover:text-indigo-700">Pay Online</p><p class="text-xs text-gray-500">UPI, Cards, Netbanking</p></div>
                <i class="fas fa-arrow-right ml-auto text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </button>
        </div>
        <button onclick="closePaymentSelectionModal()" class="mt-6 w-full text-gray-400 font-bold text-sm hover:text-gray-600 transition">Cancel</button>
    </div>
</div>

{{-- Image Lightbox Modal --}}
<div id="lightboxModal" class="hidden fixed inset-0 bg-black/95 z-[100] flex items-center justify-center p-4 backdrop-blur-sm cursor-zoom-out" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white text-3xl hover:text-gray-300 transition-colors" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <div class="relative max-w-5xl w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
        <img id="lightboxImage" src="" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl transition-all duration-300 transform scale-95" alt="Room Photo">
        <button class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors" onclick="navigateLightbox(-1)"><i class="fas fa-chevron-left text-xl"></i></button>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors" onclick="navigateLightbox(1)"><i class="fas fa-chevron-right text-xl"></i></button>
    </div>
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-black mb-4 flex items-center gap-2"><i class="fas fa-shield-alt text-indigo-600"></i>Complete Payment</h3>
        <div id="razorpay-container" class="mb-4 min-h-[100px] flex items-center justify-center bg-slate-50 rounded-xl border-2 border-dashed border-slate-200"><p class="text-slate-400 text-sm italic">Loading secure payment gateway...</p></div>
        <button onclick="closePaymentModal()" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition-colors">Cancel Transaction</button>
    </div>
</div>
