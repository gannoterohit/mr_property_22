@php
    $googleEnabled = \App\Models\Setting::isEnabled('google_login_enabled', false);
    $facebookEnabled = \App\Models\Setting::isEnabled('facebook_login_enabled', false);

    $otpMode = \App\Models\Setting::get('otp_delivery', 'email');

    $authModalImage = \App\Models\Setting::mediaUrl(
        \App\Models\Setting::get('auth_modal_image')
    );

    $authLogo = \App\Models\Setting::mediaUrl(
        \App\Models\Setting::get('navbar_logo')
        ?: \App\Models\Setting::get('website_logo')
    );

    $websiteName = \App\Models\Setting::get('website_name', 'ApnaNest');
@endphp

<div
    id="publicAuthModal"
    data-otp-mode="{{ $otpMode }}"
    class="fixed inset-0 z-[9999] hidden"
    aria-modal="true"
    role="dialog"
    aria-labelledby="publicAuthTitle"
>
    {{-- Backdrop --}}
    <div class="auth-modal-backdrop"></div>

    {{-- Modal --}}
    <div class="auth-modal-card">

        {{-- Close --}}
        <button
            type="button"
            id="publicAuthClose"
            class="auth-close-btn"
            aria-label="Close login modal"
        >
            <i class="fas fa-xmark"></i>
        </button>

        {{-- =========================================================
             RIGHT VISUAL PANEL
        ========================================================== --}}
        <div
            class="auth-visual-panel"
            data-auth-hero-banner
            @if($authModalImage)
                style="background-image: url('{{ $authModalImage }}');"
            @endif
        >
            <div class="auth-visual-overlay"></div>

            {{-- Top badge --}}
            <div class="auth-secure-badge">
                <span class="auth-secure-icon">
                    <i class="fas fa-shield-halved"></i>
                </span>
                <span>Secure access</span>
            </div>

            {{-- Trust card --}}
            <div class="auth-trust-card">
                <span class="auth-trust-title">
                    Trusted rentals
                </span>

                <span class="auth-trust-text">
                    Made for your next move
                </span>
            </div>

            {{-- Bottom feature text --}}
            <div class="auth-visual-footer">
                <span>
                    <i class="fas fa-circle-check"></i>
                    Verified listings
                </span>

                <span>•</span>

                <span>
                    <i class="fas fa-shield-halved"></i>
                    Secure OTP
                </span>

                <span>•</span>

                <span>
                    Direct access
                </span>
            </div>
        </div>

        {{-- =========================================================
             LEFT CONTENT PANEL
        ========================================================== --}}
        <div class="auth-content-panel">

            {{-- Logo --}}
            <div class="auth-brand">
                @if($authLogo)
                    <img
                        src="{{ $authLogo }}"
                        alt="{{ $websiteName }}"
                        class="auth-modal-logo"
                    >
                @else
                    <div class="auth-modal-wordmark">
                        {{ $websiteName }}
                    </div>
                @endif
            </div>

            {{-- Login/Register tabs
                 Hidden visually in desktop design but retained
                 for existing JS functionality.
            --}}
            <div class="auth-tabs" aria-hidden="true">
                <button
                    type="button"
                    data-auth-tab="login"
                    class="auth-modal-tab active"
                >
                    Login
                </button>

                <button
                    type="button"
                    data-auth-tab="register"
                    class="auth-modal-tab"
                >
                    Create account
                </button>
            </div>

            {{-- =====================================================
                 LOGIN
            ====================================================== --}}
            <div
                data-auth-panel="login"
                class="auth-modal-panel"
            >
                <div class="auth-heading">

                    <h3 id="publicAuthTitle">
                        Welcome back
                    </h3>

                    <p>
                        @if($otpMode === 'phone')
                            Use your mobile number to receive a secure OTP.
                        @elseif($otpMode === 'both')
                            Use your email or mobile number to receive a secure OTP.
                        @else
                            Use your email address to receive a secure OTP.
                        @endif
                    </p>

                </div>

                <form
                    id="publicAuthLoginForm"
                    class="auth-login-form"
                >
                    <div class="auth-form-group">

                        <label
                            class="auth-field-label"
                            for="publicAuthLoginIdentifier"
                        >
                            <span class="auth-label-icon">
                                <i class="fas {{
                                    $otpMode === 'phone'
                                        ? 'fa-mobile-screen-button'
                                        : ($otpMode === 'email'
                                            ? 'fa-envelope'
                                            : 'fa-address-card')
                                }}"></i>
                            </span>

                            @if($otpMode === 'phone')
                                Mobile number
                            @elseif($otpMode === 'both')
                                Email or mobile
                            @else
                                Email address
                            @endif
                        </label>

                        <div class="auth-input-shell">

                            @if($otpMode === 'phone')
                                <span class="auth-country-code">
                                    +91
                                </span>
                            @endif

                            <span class="auth-input-icon">
                                <i class="fas {{
                                    $otpMode === 'phone'
                                        ? 'fa-mobile-screen-button'
                                        : ($otpMode === 'email'
                                            ? 'fa-envelope'
                                            : 'fa-user')
                                }}"></i>
                            </span>

                            <input
                                id="publicAuthLoginIdentifier"
                                type="{{
                                    $otpMode === 'phone'
                                        ? 'tel'
                                        : ($otpMode === 'email'
                                            ? 'email'
                                            : 'text')
                                }}"
                                name="identifier"
                                autocomplete="email tel"
                                placeholder="{{
                                    $otpMode === 'phone'
                                        ? '9876543210'
                                        : ($otpMode === 'email'
                                            ? 'name@example.com'
                                            : 'name@example.com or 9876543210')
                                }}"
                                class="auth-modal-input"
                            >

                        </div>
                    </div>

                    <button
                        type="submit"
                        class="auth-cta-btn"
                    >
                        <span>Continue securely</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                {{-- Social --}}
                @if($googleEnabled || $facebookEnabled)

                    <div class="auth-divider">
                        <span></span>
                        <strong>OR CONTINUE WITH</strong>
                        <span></span>
                    </div>

                    <div class="auth-social-grid">

                        @if($googleEnabled)
                            <a
                                href="{{ route('social.redirect', 'google') }}"
                                class="auth-social-btn google"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    width="18"
                                    height="18"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                                    />
                                    <path
                                        fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                    />
                                    <path
                                        fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                    />
                                    <path
                                        fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                    />
                                </svg>

                                <span>Google</span>
                            </a>
                        @endif

                        @if($facebookEnabled)
                            <a
                                href="{{ route('social.redirect', 'facebook') }}"
                                class="auth-social-btn facebook"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    width="18"
                                    height="18"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill="#fff"
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                                    />
                                </svg>

                                <span>Facebook</span>
                            </a>
                        @endif

                    </div>

                @endif

                <p class="auth-bottom-text">
                    New here?
                    <button
                        type="button"
                        data-auth-tab-switch="register"
                    >
                        Create an account
                    </button>
                </p>
            </div>


            {{-- =====================================================
                 REGISTER
            ====================================================== --}}
            <div
                data-auth-panel="register"
                class="auth-modal-panel hidden"
            >
                <div class="auth-heading">
                    <h3>Create account</h3>

                    <p>
                        Choose your role and verify your identity quickly.
                    </p>
                </div>

                <form
                    id="publicAuthRegisterForm"
                    class="auth-register-form"
                >

                    <div class="auth-form-group">
                        <label class="auth-field-label">
                            I am
                        </label>

                        <div class="auth-role-grid">

                            <label class="auth-role-card">
                                <input
                                    type="radio"
                                    name="auth_role"
                                    value="user"
                                    checked
                                >

                                <span>
                                    <i class="fas fa-search"></i>
                                    <small>Find room</small>
                                </span>
                            </label>

                            <label class="auth-role-card">
                                <input
                                    type="radio"
                                    name="auth_role"
                                    value="owner"
                                >

                                <span>
                                    <i class="fas fa-building"></i>
                                    <small>Owner</small>
                                </span>
                            </label>

                            <label class="auth-role-card">
                                <input
                                    type="radio"
                                    name="auth_role"
                                    value="broker"
                                >

                                <span>
                                    <i class="fas fa-handshake"></i>
                                    <small>Broker</small>
                                </span>
                            </label>

                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label
                            class="auth-field-label"
                            for="publicAuthRegisterName"
                        >
                            Full name
                        </label>

                        <input
                            id="publicAuthRegisterName"
                            type="text"
                            name="name"
                            placeholder="Your full name"
                            class="auth-modal-input standalone"
                        >
                    </div>

                    <div class="auth-form-group">
                        <label
                            class="auth-field-label"
                            for="publicAuthRegisterEmail"
                        >
                            Email
                        </label>

                        <input
                            id="publicAuthRegisterEmail"
                            type="email"
                            name="email"
                            placeholder="name@example.com"
                            class="auth-modal-input standalone"
                        >
                    </div>

                    <div class="auth-form-group">
                        <label
                            class="auth-field-label"
                            for="publicAuthRegisterPhone"
                        >
                            Phone
                        </label>

                        <input
                            id="publicAuthRegisterPhone"
                            type="tel"
                            name="phone"
                            placeholder="9876543210"
                            class="auth-modal-input standalone"
                        >
                    </div>

                    <button
                        type="submit"
                        class="auth-cta-btn"
                    >
                        <span>Continue</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                </form>

                <p class="auth-bottom-text">
                    Already have an account?
                    <button
                        type="button"
                        data-auth-tab-switch="login"
                    >
                        Login
                    </button>
                </p>
            </div>


            {{-- =====================================================
                 OTP
            ====================================================== --}}
            <div
                data-auth-panel="otp"
                class="auth-modal-panel hidden"
            >

                <div class="auth-otp-heading">

                    <div class="auth-otp-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>

                    <h3>Verify your code</h3>

                    <p id="publicAuthOtpMessage">
                        We sent a 6-digit code to your email or phone.
                    </p>

                </div>

                <form
                    id="publicAuthOtpForm"
                    class="auth-otp-form"
                >

                    <div class="auth-form-group">

                        <label class="auth-field-label">
                            Verification code
                        </label>

                        <input
                            id="publicAuthOtpInput"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="000000"
                            class="auth-otp-input"
                        >

                    </div>

                    <div class="auth-otp-actions">
                        <span>
                            Expires in 10 minutes
                        </span>

                        <button
                            type="button"
                            id="publicAuthResendOtp"
                        >
                            Resend code
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="auth-cta-btn"
                    >
                        <span>Verify & continue</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <button
                        type="button"
                        id="publicAuthBackToForm"
                        class="auth-back-btn"
                    >
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </button>

                </form>
            </div>


            {{-- Status --}}
            <div
                id="publicAuthStatus"
                class="auth-status hidden"
            ></div>

        </div>
    </div>
</div>


<script>
(function () {

    const modal = document.getElementById('publicAuthModal');

    if (!modal) return;

    const tabs = document.querySelectorAll('[data-auth-tab]');
    const panels = document.querySelectorAll('.auth-modal-panel');
    const tabSwitchButtons = document.querySelectorAll('[data-auth-tab-switch]');
    const closeBtn = document.getElementById('publicAuthClose');
    const statusBox = document.getElementById('publicAuthStatus');

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    const showStatus = (message, type = 'error') => {

        if (!statusBox) return;

        statusBox.classList.remove(
            'hidden',
            'success',
            'error'
        );

        statusBox.classList.add(type === 'success' ? 'success' : 'error');

        statusBox.textContent = message;
    };

    const hideStatus = () => {

        if (!statusBox) return;

        statusBox.classList.add('hidden');
        statusBox.textContent = '';

    };


    /*
    |--------------------------------------------------------------------------
    | Tabs
    |--------------------------------------------------------------------------
    */

    const switchTab = (tabName) => {

        tabs.forEach((btn) => {

            const active = btn.dataset.authTab === tabName;

            btn.classList.toggle('active', active);

        });

        panels.forEach((panel) => {

            const isTarget =
                panel.dataset.authPanel === tabName;

            panel.classList.toggle(
                'hidden',
                !isTarget
            );

        });

        hideStatus();
    };


    tabs.forEach((btn) => {

        btn.addEventListener('click', () => {

            switchTab(btn.dataset.authTab);

        });

    });


    tabSwitchButtons.forEach((btn) => {

        btn.addEventListener('click', () => {

            switchTab(btn.dataset.authTabSwitch);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Open / Close
    |--------------------------------------------------------------------------
    */

    const openModal = (tabName = 'login') => {

        modal.classList.remove('hidden');

        document.body.classList.add('overflow-hidden');

        switchTab(tabName);

    };


    const closeModal = () => {

        modal.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');

        hideStatus();

        document.getElementById('publicAuthOtpForm')?.reset();
        document.getElementById('publicAuthLoginForm')?.reset();
        document.getElementById('publicAuthRegisterForm')?.reset();

    };


    closeBtn?.addEventListener('click', closeModal);


    modal.addEventListener('click', (event) => {

        if (event.target === modal) {
            closeModal();
        }

    });


    document.addEventListener('keydown', (event) => {

        if (
            event.key === 'Escape' &&
            !modal.classList.contains('hidden')
        ) {
            closeModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | AJAX Request
    |--------------------------------------------------------------------------
    */

    const csrf =
        document.querySelector('meta[name="csrf-token"]')?.content || '';


    const makeRequest = async (url, payload) => {

        try {

            const response = await fetch(url, {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },

                body: JSON.stringify(payload)

            });

            let data = {};

            try {
                data = await response.json();
            } catch (error) {}

            if (!response.ok && !data.message) {

                data.message =
                    'The request could not be completed.';

            }

            return data;

        } catch (error) {

            return {
                success: false,
                message: 'Network error. Please try again.'
            };

        }

    };


    /*
    |--------------------------------------------------------------------------
    | OTP Panel
    |--------------------------------------------------------------------------
    */

    const showOtpStep = (mode, targetText) => {

        const otpPanel =
            document.querySelector('[data-auth-panel="otp"]');

        if (!otpPanel) return;

        const otpMessage =
            document.getElementById('publicAuthOtpMessage');

        if (otpMessage) {

            otpMessage.textContent =
                'We sent a 6-digit code to ' +
                targetText +
                '.';

        }

        panels.forEach((panel) => {
            panel.classList.add('hidden');
        });

        otpPanel.classList.remove('hidden');

        const otpInput =
            document.getElementById('publicAuthOtpInput');

        setTimeout(() => {

            otpInput?.focus();

        }, 80);

        window.__apnanestAuthMode = mode;
        window.__apnanestAuthTarget = targetText;

    };


    /*
    |--------------------------------------------------------------------------
    | Send OTP
    |--------------------------------------------------------------------------
    */

    const validateAndSendOtp = async (mode) => {

        hideStatus();

        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        if (mode === 'login') {

            const identifier =
                document
                    .getElementById('publicAuthLoginIdentifier')
                    ?.value
                    .trim();

            if (!identifier) {

                showStatus(
                    'Please enter your email or mobile number.'
                );

                return;
            }

            const data = await makeRequest(
                '{{ route('send.otp') }}',
                {
                    identifier
                }
            );

            if (!data.success) {

                showStatus(
                    data.message ||
                    'Unable to send OTP. Please try again.'
                );

                return;
            }

            showOtpStep(
                'login',
                identifier
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | REGISTER
        |--------------------------------------------------------------------------
        */

        const name =
            document
                .getElementById('publicAuthRegisterName')
                ?.value
                .trim();

        const email =
            document
                .getElementById('publicAuthRegisterEmail')
                ?.value
                .trim();

        const phone =
            document
                .getElementById('publicAuthRegisterPhone')
                ?.value
                .trim();

        const role =
            document
                .querySelector(
                    'input[name="auth_role"]:checked'
                )
                ?.value || 'user';


        if (!name || name.length < 2) {

            showStatus(
                'Please enter your full name.'
            );

            return;
        }


        if (
            !email ||
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        ) {

            showStatus(
                'Please enter a valid email address.'
            );

            return;
        }


        const data = await makeRequest(
            '{{ route('send.otp') }}',
            {
                email,
                name,
                phone,
                role
            }
        );


        if (!data.success) {

            showStatus(
                data.message ||
                'Unable to send OTP. Please try again.'
            );

            return;
        }


        showOtpStep(
            'register',
            email
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Login Submit
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('publicAuthLoginForm')
        ?.addEventListener('submit', async (event) => {

            event.preventDefault();

            await validateAndSendOtp('login');

        });


    /*
    |--------------------------------------------------------------------------
    | Register Submit
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('publicAuthRegisterForm')
        ?.addEventListener('submit', async (event) => {

            event.preventDefault();

            await validateAndSendOtp('register');

        });


    /*
    |--------------------------------------------------------------------------
    | Back From OTP
    |--------------------------------------------------------------------------
    */

    const backToForm = () => {

        const mode =
            window.__apnanestAuthMode || 'login';

        switchTab(
            mode === 'register'
                ? 'register'
                : 'login'
        );

    };


    document
        .getElementById('publicAuthBackToForm')
        ?.addEventListener('click', backToForm);


    /*
    |--------------------------------------------------------------------------
    | Resend OTP
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('publicAuthResendOtp')
        ?.addEventListener('click', async () => {

            const mode =
                window.__apnanestAuthMode || 'login';

            await validateAndSendOtp(mode);

        });


    /*
    |--------------------------------------------------------------------------
    | Verify OTP
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('publicAuthOtpForm')
        ?.addEventListener('submit', async (event) => {

            event.preventDefault();

            hideStatus();

            const otp =
                document
                    .getElementById('publicAuthOtpInput')
                    ?.value
                    .replace(/\D/g, '')
                    .slice(0, 6);


            if (!otp || otp.length !== 6) {

                showStatus(
                    'Please enter the complete 6-digit code.'
                );

                return;
            }


            const mode =
                window.__apnanestAuthMode || 'login';

            const payload = {
                otp
            };


            if (mode === 'login') {

                payload.identifier =
                    document
                        .getElementById(
                            'publicAuthLoginIdentifier'
                        )
                        ?.value
                        .trim();

            } else {

                payload.name =
                    document
                        .getElementById(
                            'publicAuthRegisterName'
                        )
                        ?.value
                        .trim();

                payload.email =
                    document
                        .getElementById(
                            'publicAuthRegisterEmail'
                        )
                        ?.value
                        .trim();

                payload.phone =
                    document
                        .getElementById(
                            'publicAuthRegisterPhone'
                        )
                        ?.value
                        .trim();

                payload.role =
                    document
                        .querySelector(
                            'input[name="auth_role"]:checked'
                        )
                        ?.value || 'user';
            }


            const url =
                mode === 'login'
                    ? '{{ route('verify.login.otp') }}'
                    : '{{ route('verify.registration.otp') }}';


            const data =
                await makeRequest(
                    url,
                    payload
                );


            if (!data.success) {

                showStatus(
                    data.message ||
                    'Verification failed. Please try again.'
                );

                return;
            }


            showStatus(
                data.message || 'Success',
                'success'
            );


            setTimeout(() => {

                closeModal();

                window.location.href =
                    data.redirect ||
                    '{{ route('home') }}';

            }, 700);

        });


    /*
    |--------------------------------------------------------------------------
    | OTP Input
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('publicAuthOtpInput')
        ?.addEventListener('input', (event) => {

            event.target.value =
                event.target.value
                    .replace(/\D/g, '')
                    .slice(0, 6);

        });


    /*
    |--------------------------------------------------------------------------
    | Auth Trigger
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-auth-trigger]')
        .forEach((button) => {

            button.addEventListener('click', (event) => {

                event.preventDefault();

                const target =
                    button.dataset.authTrigger || 'login';

                openModal(target);

            });

        });


    /*
    |--------------------------------------------------------------------------
    | URL ?auth=login / ?auth=register
    |--------------------------------------------------------------------------
    */

    const requestedAuthTab =
        new URLSearchParams(
            window.location.search
        ).get('auth');


    if (
        requestedAuthTab === 'login' ||
        requestedAuthTab === 'register'
    ) {

        openModal(requestedAuthTab);

    }


    /*
    |--------------------------------------------------------------------------
    | Global Functions
    |--------------------------------------------------------------------------
    */

    window.openPublicAuthModal = openModal;
    window.closePublicAuthModal = closeModal;

})();
</script>