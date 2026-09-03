<!-- Mobile App Loading Indicator -->
<div id="mobile-loading" class="lg:hidden fixed inset-0 z-[3000] flex items-center justify-center" style="display: none;">
    <div class="loading-backdrop"></div>
    <div class="loading-content text-center relative z-10">
        <div class="loading-logo mx-auto mb-5">
            <div class="loading-icon-wrap">
                <i class="fas fa-home"></i>
            </div>
        </div>
        <h2 class="loading-brand text-white font-black text-lg tracking-tight mb-1">ApnaNest</h2>
        <p class="loading-tagline text-white/70 text-[11px] font-semibold tracking-wide uppercase mb-6">Find Your Perfect Stay</p>
        <div class="loading-spinner mx-auto mb-4"></div>
        <p class="loading-text text-white/90 text-xs font-semibold tracking-wide">Loading...</p>
    </div>
</div>

<style>
    #mobile-loading {
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    #mobile-loading.is-visible {
        opacity: 1;
    }
    #mobile-loading.is-hiding {
        opacity: 0;
    }
    .loading-backdrop {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, var(--primary) 0%, color-mix(in srgb, var(--primary) 85%, #000) 100%);
    }
    .loading-icon-wrap {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    }
    .loading-icon-wrap i {
        font-size: 32px;
        color: #fff;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }
    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid rgba(255,255,255,0.2);
        border-top-color: #fff;
        border-radius: 50%;
        animation: mobile-spin 0.8s linear infinite;
    }
    @keyframes mobile-spin {
        to { transform: rotate(360deg); }
    }
    .loading-brand {
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .loading-tagline {
        letter-spacing: 0.15em;
    }
    .loading-text {
        animation: loading-pulse 1.5s ease-in-out infinite;
    }
    @keyframes loading-pulse {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }
</style>

<script>
    (function() {
        const loading = document.getElementById('mobile-loading');
        if (!loading) return;

        let hideTimer = null;
        let isNavigating = false;

        function showLoading() {
            if (window.innerWidth >= 1024) return;
            clearTimeout(hideTimer);
            loading.style.display = 'flex';
            loading.classList.remove('is-hiding');
            requestAnimationFrame(function() {
                loading.classList.add('is-visible');
            });
        }

        function hideLoading() {
            loading.classList.add('is-hiding');
            hideTimer = setTimeout(function() {
                loading.classList.remove('is-visible', 'is-hiding');
                loading.style.display = 'none';
            }, 250);
        }

        document.addEventListener('click', function(e) {
            if (window.innerWidth >= 1024) return;
            const target = e.target.closest('a');
            if (!target) return;
            const href = target.getAttribute('href');
            if (!href || href.indexOf(window.location.hostname) < 0) return;
            if (href.indexOf('#') === 0) return;
            if (target.hasAttribute('download')) return;
            if (target.target === '_blank') return;
            showLoading();
        });

        window.addEventListener('pageshow', function() {
            hideLoading();
        });

        window.addEventListener('load', function() {
            hideLoading();
        });

        window.addEventListener('beforeunload', function() {
            showLoading();
        });
    })();
</script>
