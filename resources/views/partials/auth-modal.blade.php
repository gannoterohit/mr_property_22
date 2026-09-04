@php
    $googleEnabled = \App\Models\Setting::isEnabled('google_login_enabled', false);
    $facebookEnabled = \App\Models\Setting::isEnabled('facebook_login_enabled', false);
    $otpMode = \App\Models\Setting::get('otp_delivery', 'email');
    $authHeroImages = \App\Models\City::resolveHeroImages(session('user_city') ?? request('city'));
@endphp<div id="publicAuthModal" data-otp-mode="{{ $otpMode }}" class="fixed inset-0 z-[9999] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm"></div>    <div class="relative mx-auto mt-10 w-[min(94vw,880px)] overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-[0_40px_90px_rgba(15,23,42,0.20)]">
        <button type="button" id="publicAuthClose" class="absolute right-5 top-5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200" aria-label="Close login modal">
            <i class="fas fa-xmark text-sm"></i>
        </button>        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4" data-auth-hero-banner style="background-image: linear-gradient(180deg, rgba(15, 23, 42, .12), rgba(15, 23, 42, .7)), url('{{ $authHeroImages[0] ?? '' }}');">
            <div class="flex items-center justify-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-blue-700">
                    <i class="fas fa-shield-alt"></i>
                    Secure access
                </div>
            </div>
        </div>        <div class="px-5 pt-5 pb-5 md:px-6 md:pt-6">
            <div class="mb-5 flex gap-2 rounded-2xl bg-slate-100 p-1.5">
                <button type="button" data-auth-tab="login" class="auth-modal-tab flex-1 rounded-2xl bg-white px-3 py-2.5 text-sm font-bold text-slate-900 shadow-sm">Login</button>
                <button type="button" data-auth-tab="register" class="auth-modal-tab flex-1 rounded-2xl px-3 py-2.5 text-sm font-bold text-slate-600">Create account</button>
            </div>            <div data-auth-panel="login" class="auth-modal-panel">
                <div class="mb-4">
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Welcome back</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($otpMode === 'phone') Use your mobile number to receive a secure OTP.
                        @elseif($otpMode === 'both') Use your email or mobile number to receive a secure OTP.
                        @else Use your email address to receive a secure OTP. @endif
                    </p>
                </div>                <form id="publicAuthLoginForm" class="space-y-4">
                    <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">
                                @if($otpMode === 'phone') Mobile number @elseif($otpMode === 'both') Email or mobile @else Email address @endif
                            </label>
                        <div class="relative">
                            <i class="fas {{ $otpMode === 'phone' ? 'fa-mobile-screen-button' : ($otpMode === 'email' ? 'fa-envelope' : 'fa-user') }} absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="publicAuthLoginIdentifier" type="{{ $otpMode === 'phone' ? 'tel' : ($otpMode === 'email' ? 'email' : 'text') }}" name="identifier" autocomplete="email tel" placeholder="{{ $otpMode === 'phone' ? '9876543210' : ($otpMode === 'email' ? 'name@example.com' : 'name@example.com or 9876543210') }}" class="auth-modal-input w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>                    <button type="submit" class="auth-cta-btn flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-[0_14px_30px_rgba(59,130,246,0.30)] transition hover:translate-y-[-1px] hover:shadow-[0_16px_34px_rgba(59,130,246,0.34)]">
                        <span>Send verification code</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </form>                @if($googleEnabled || $facebookEnabled)
                    <div class="my-5 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span>or continue with</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>                    <div class="grid grid-cols-2 gap-3">
                        @if($googleEnabled)
                            <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                <span>Google</span>
                            </a>
                        @endif                        @if($facebookEnabled)
                            <a href="{{ route('social.redirect', 'facebook') }}" class="flex items-center justify-center gap-2 rounded-2xl border border-blue-500 bg-blue-600 px-3 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#fff" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                        @endif
                    </div>
                @endif                <p class="mt-5 text-center text-xs text-slate-500">New here? <button type="button" data-auth-tab-switch="register" class="font-bold text-blue-600">Create an account</button></p>
            </div>            <div data-auth-panel="register" class="auth-modal-panel hidden">
                <div class="mb-4">
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Create account</h3>
                    <p class="mt-1 text-sm text-slate-500">Choose your role and verify your identity quickly.</p>
                </div>                <form id="publicAuthRegisterForm" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">I am</label>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                <label class="auth-role-card">
                                    <input type="radio" name="auth_role" value="user" checked>
                                    <span><i class="fas fa-search"></i><small>Find room</small></span>
                                </label>
                                <label class="auth-role-card">
                                    <input type="radio" name="auth_role" value="owner">
                                    <span><i class="fas fa-building"></i><small>Owner</small></span>
                                </label>
                                <label class="auth-role-card">
                                    <input type="radio" name="auth_role" value="broker">
                                    <span><i class="fas fa-handshake"></i><small>Broker</small></span>
                                </label>
                            </div>
                        </div>                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Full name</label>
                            <input id="publicAuthRegisterName" type="text" name="name" placeholder="Your full name" class="auth-modal-input w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Email</label>
                            <input id="publicAuthRegisterEmail" type="email" name="email" placeholder="name@example.com" class="auth-modal-input w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Phone</label>
                            <input id="publicAuthRegisterPhone" type="tel" name="phone" placeholder="9876543210" class="auth-modal-input w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>                    <button type="submit" class="auth-cta-btn flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-[0_14px_30px_rgba(59,130,246,0.30)] transition hover:translate-y-[-1px] hover:shadow-[0_16px_34px_rgba(59,130,246,0.34)]">
                        <span>Continue</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </form>                <p class="mt-5 text-center text-xs text-slate-500">Already have an account? <button type="button" data-auth-tab-switch="login" class="font-bold text-blue-600">Login</button></p>
            </div>            <div data-auth-panel="otp" class="auth-modal-panel hidden">
                <div class="mb-4 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-xl text-blue-600">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Verify your code</h3>
                    <p id="publicAuthOtpMessage" class="mt-2 text-sm text-slate-500">We sent a 6-digit code to your email or phone.</p>
                </div>                <form id="publicAuthOtpForm" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Verification code</label>
                        <input id="publicAuthOtpInput" type="text" inputmode="numeric" maxlength="6" placeholder="000000" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-center text-2xl font-black tracking-[0.5em] text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Expires in 10 minutes</span>
                        <button type="button" id="publicAuthResendOtp" class="font-bold text-blue-600">Resend code</button>
                    </div>                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700">
                        <span>Verify & continue</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>                    <button type="button" id="publicAuthBackToForm" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Back</span>
                    </button>
                </form>
            </div>            <div id="publicAuthStatus" class="mt-4 hidden rounded-2xl border px-3 py-2.5 text-xs font-medium"></div>
        </div>
    </div>
</div><style>
    .auth-modal-tab { transition: all 0.2s ease; }
    .auth-modal-tab:hover { background: rgba(255,255,255,0.75); }
    .auth-modal-input { box-shadow: inset 0 1px 2px rgba(15,23,42,0.02); }
    .auth-modal-input:focus { box-shadow: 0 0 0 4px rgba(59,130,246,0.08); }
    .auth-cta-btn { letter-spacing: 0.01em; }
    .auth-role-card { display: block; cursor: pointer; position: relative; }
    .auth-role-card input { position: absolute; opacity: 0; pointer-events: none; }
    .auth-role-card span {
        display: flex; align-items: center; justify-content: center; gap: 6px; flex-direction: column;
        min-height: 76px; border-radius: 16px; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569;
        text-align: center; transition: all 0.2s ease;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }
    .auth-role-card i { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 10px; background: #e2e8f0; color: #475569; font-size: 10px; }
    .auth-role-card small { display: block; font-size: 10px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; }
    .auth-role-card input:checked + span {
        background: rgba(59,130,246,0.08); border-color: rgba(59,130,246,0.4); color: #1d4ed8; box-shadow: 0 0 0 3px rgba(59,130,246,0.08), inset 0 1px 0 rgba(255,255,255,0.7);
    }
    .auth-role-card input:checked + span i { background: #2563eb; color: #fff; }
    @media (max-width: 640px) {
        #publicAuthModal .w-\[min\(94vw\,880px\)\] { width: min(94vw, 620px); }
    }
</style><script>
(function () {
    const modal = document.getElementById('publicAuthModal');
    if (!modal) return;

    const authHeroBanner = modal.querySelector('[data-auth-hero-banner]');
    const authHeroImages = @json($authHeroImages);
    if (authHeroBanner && authHeroImages.length > 1) {
        let authHeroIndex = 0;
        setInterval(() => {
            authHeroIndex = (authHeroIndex + 1) % authHeroImages.length;
            authHeroBanner.style.backgroundImage = 'linear-gradient(180deg, rgba(15, 23, 42, .12), rgba(15, 23, 42, .7)), url("' + authHeroImages[authHeroIndex] + '")';
        }, 5000);
    }

    const tabs = document.querySelectorAll('[data-auth-tab]');
    const panels = document.querySelectorAll('.auth-modal-panel');
    const tabSwitchButtons = document.querySelectorAll('[data-auth-tab-switch]');
    const closeBtn = document.getElementById('publicAuthClose');
    const statusBox = document.getElementById('publicAuthStatus');    const showStatus = (message, type = 'error') => {
        if (!statusBox) return;
        statusBox.classList.remove('hidden');
        statusBox.classList.remove('border-red-200', 'bg-red-50', 'text-red-700', 'border-green-200', 'bg-green-50', 'text-green-700');
        if (type === 'success') {
            statusBox.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
        } else {
            statusBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
        }
        statusBox.textContent = message;
    };    const hideStatus = () => {
        if (!statusBox) return;
        statusBox.classList.add('hidden');
        statusBox.textContent = '';
    };    const switchTab = (tabName) => {
        tabs.forEach((btn) => {
            const active = btn.dataset.authTab === tabName;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-900', active);
            btn.classList.toggle('text-slate-600', !active);
        });        panels.forEach((panel) => {
            const isTarget = panel.dataset.authPanel === tabName;
            panel.classList.toggle('hidden', !isTarget);
        });        hideStatus();
    };    tabs.forEach((btn) => {
        btn.addEventListener('click', () => switchTab(btn.dataset.authTab));
    });    tabSwitchButtons.forEach((btn) => {
        btn.addEventListener('click', () => switchTab(btn.dataset.authTabSwitch));
    });    const openModal = (tabName = 'login') => {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        switchTab(tabName);
    };    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        hideStatus();
        if (document.getElementById('publicAuthOtpForm')) {
            document.getElementById('publicAuthOtpForm').reset();
        }
        if (document.getElementById('publicAuthLoginForm')) {
            document.getElementById('publicAuthLoginForm').reset();
        }
        if (document.getElementById('publicAuthRegisterForm')) {
            document.getElementById('publicAuthRegisterForm').reset();
        }
    };    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';    const makeRequest = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });        let data = {};
        try { data = await response.json(); } catch (error) {}
        if (!response.ok && !data.message) {
            data.message = 'The request could not be completed.';
        }
        return data;
    };    const showOtpStep = (mode, targetText) => {
        const otpPanel = document.querySelector('[data-auth-panel="otp"]');
        if (!otpPanel) return;        const otpMessage = document.getElementById('publicAuthOtpMessage');
        if (otpMessage) {
            otpMessage.textContent = 'We sent a 6-digit code to ' + targetText + '.';
        }        panels.forEach((panel) => {
            panel.classList.add('hidden');
        });
        otpPanel.classList.remove('hidden');
        const otpInput = document.getElementById('publicAuthOtpInput');
        setTimeout(() => otpInput && otpInput.focus(), 50);        window.__apnanestAuthMode = mode;
        window.__apnanestAuthTarget = targetText;
    };    const validateAndSendOtp = async (mode) => {
        hideStatus();        if (mode === 'login') {
            const identifier = document.getElementById('publicAuthLoginIdentifier')?.value.trim();
            if (!identifier) {
                showStatus('Please enter your email or mobile number.');
                return;
            }            const data = await makeRequest('{{ route('send.otp') }}', { identifier });
            if (!data.success) {
                showStatus(data.message || 'Unable to send OTP. Please try again.');
                return;
            }            showOtpStep('login', identifier);
            return;
        }        const name = document.getElementById('publicAuthRegisterName')?.value.trim();
        const email = document.getElementById('publicAuthRegisterEmail')?.value.trim();
        const phone = document.getElementById('publicAuthRegisterPhone')?.value.trim();
        const role = document.querySelector('input[name="auth_role"]:checked')?.value || 'user';        if (!name || name.length < 2) {
            showStatus('Please enter your full name.');
            return;
        }        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showStatus('Please enter a valid email address.');
            return;
        }        const data = await makeRequest('{{ route('send.otp') }}', { email, name, phone, role });
        if (!data.success) {
            showStatus(data.message || 'Unable to send OTP. Please try again.');
            return;
        }        showOtpStep('register', email);
    };    document.getElementById('publicAuthLoginForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await validateAndSendOtp('login');
    });    document.getElementById('publicAuthRegisterForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await validateAndSendOtp('register');
    });    const backToForm = () => {
        panels.forEach((panel) => {
            if (panel.dataset.authPanel === 'login' || panel.dataset.authPanel === 'register') {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
        const active = document.querySelector('[data-auth-tab].bg-white')?.dataset.authTab || 'login';
        switchTab(active);
    };    document.getElementById('publicAuthBackToForm')?.addEventListener('click', () => {
        backToForm();
    });    document.getElementById('publicAuthResendOtp')?.addEventListener('click', async () => {
        const mode = window.__apnanestAuthMode || 'login';
        await validateAndSendOtp(mode);
    });    document.getElementById('publicAuthOtpForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideStatus();        const otp = document.getElementById('publicAuthOtpInput')?.value.replace(/\D/g, '').slice(0, 6);
        if (!otp || otp.length !== 6) {
            showStatus('Please enter the complete 6-digit code.');
            return;
        }        const mode = window.__apnanestAuthMode || 'login';
        const payload = { otp };        if (mode === 'login') {
            const identifier = document.getElementById('publicAuthLoginIdentifier')?.value.trim();
            payload.identifier = identifier;
        } else {
            payload.name = document.getElementById('publicAuthRegisterName')?.value.trim();
            payload.email = document.getElementById('publicAuthRegisterEmail')?.value.trim();
            payload.phone = document.getElementById('publicAuthRegisterPhone')?.value.trim();
            payload.role = document.querySelector('input[name="auth_role"]:checked')?.value || 'user';
        }        const url = mode === 'login' ? '{{ route('verify.login.otp') }}' : '{{ route('verify.registration.otp') }}';
        const data = await makeRequest(url, payload);        if (!data.success) {
            showStatus(data.message || 'Verification failed. Please try again.');
            return;
        }        showStatus(data.message || 'Success', 'success');
        setTimeout(() => {
            closeModal();
            window.location.href = data.redirect || '{{ route('home') }}';
        }, 700);
    });    document.getElementById('publicAuthOtpInput')?.addEventListener('input', (event) => {
        event.target.value = event.target.value.replace(/\D/g, '').slice(0, 6);
    });    document.querySelectorAll('[data-auth-trigger]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const target = button.dataset.authTrigger || 'login';
            openModal(target);
        });
    });

    const requestedAuthTab = new URLSearchParams(window.location.search).get('auth');
    if (requestedAuthTab === 'login' || requestedAuthTab === 'register') {
        openModal(requestedAuthTab);
    }

    window.openPublicAuthModal = openModal;
    window.closePublicAuthModal = closeModal;
})();
</script>