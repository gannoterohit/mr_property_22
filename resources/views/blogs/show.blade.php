@extends('layouts.app')

@section('title', $blog->meta_title ?: $blog->title)
@section('description', $blog->meta_description ?: Str::limit(strip_tags($blog->content), 160))
@section('keywords', $blog->meta_keywords)

@push('styles')
<style>
.article-progress{position:fixed;z-index:1100;top:0;left:0;width:0;height:3px;background:#2563eb}
.article-page{min-height:100vh;background:#f8fafc;color:#0f172a}
.article-wrap{width:min(1180px,calc(100% - 48px));margin-inline:auto}
.article-breadcrumb{display:flex;align-items:center;gap:8px;padding:22px 0;color:#64748b;font-size:12px;font-weight:700}
.article-breadcrumb a{color:#2563eb;text-decoration:none}
.article-header{padding:44px 0 40px;border-block:1px solid #e7edf5;background:#fff}
.article-header-inner{max-width:940px}
.article-label{display:inline-flex;align-items:center;gap:7px;margin-bottom:17px;padding:7px 11px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.1em}
.article-header h1{margin:0;color:#0b1733;font-size:clamp(38px,4.2vw,62px);line-height:1.1;font-weight:900;letter-spacing:-2px}
.article-summary{max-width:800px;margin:18px 0 0;color:#64748b;font-size:16px;line-height:1.75}
.article-meta{display:flex;align-items:center;justify-content:space-between;gap:22px;margin-top:26px}
.article-author{display:flex;align-items:center;gap:12px}
.article-author-icon{display:grid;place-items:center;width:42px;height:42px;border:1px solid #dbeafe;border-radius:12px;background:#eff6ff;color:#2563eb}
.article-author strong{display:block;font-size:12px}.article-author span{display:block;margin-top:3px;color:#64748b;font-size:10px}
.article-share{display:flex;align-items:center;gap:8px}.article-share>span{margin-right:4px;color:#64748b;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.article-share a{display:grid;place-items:center;width:38px;height:38px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;color:#475569;text-decoration:none}
.article-share a:hover{border-color:#bfdbfe;color:#2563eb;background:#eff6ff}
.article-feature{overflow:hidden;margin:34px auto 0;border:1px solid #e2e8f0;border-radius:18px;background:#eaf0f7;box-shadow:0 18px 42px -28px rgba(15,23,42,.42)}
.article-feature img{display:block;width:100%;max-height:600px;object-fit:cover}
.article-feature-empty{display:grid;place-items:center;min-height:390px;color:#94a3b8;font-size:54px}
.article-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:38px;align-items:start;padding:48px 0 72px}
.article-main{min-width:0;padding:34px 38px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.035)}
.article-body{color:#334155;font-size:16px;line-height:1.85}
.article-body p{margin:0 0 1.5em}.article-body h2{margin:2em 0 .75em;color:#0f172a;font-size:28px;line-height:1.3;font-weight:850;letter-spacing:-.5px}
.article-body h3{margin:1.8em 0 .7em;color:#0f172a;font-size:22px;line-height:1.35;font-weight:800}
.article-body h4{margin:1.5em 0 .6em;color:#0f172a;font-size:18px;font-weight:800}
.article-body ul,.article-body ol{margin:0 0 1.5em;padding-left:1.5em}.article-body li{margin:.55em 0}
.article-body a{color:#2563eb;text-decoration:underline;text-underline-offset:3px}
.article-body blockquote{margin:2em 0;padding:18px 20px;border-left:4px solid #2563eb;border-radius:0 10px 10px 0;background:#eff6ff;color:#1e3a8a;font-weight:650}
.article-body img{display:block;max-width:100%;height:auto;margin:2em auto;border-radius:12px}
.article-body table{display:block;width:100%;overflow-x:auto;margin:2em 0;border-collapse:collapse}.article-body th,.article-body td{padding:12px;border:1px solid #e2e8f0;text-align:left}.article-body th{background:#f8fafc;color:#0f172a}
.article-thanks{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:36px;padding:22px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff}
.article-thanks strong{display:block;font-size:14px}.article-thanks p{margin:4px 0 0;color:#64748b;font-size:12px}
.article-thanks a{flex:none;padding:11px 15px;border-radius:9px;background:#2563eb;color:#fff;font-size:12px;font-weight:850;text-decoration:none}
.article-sidebar{position:sticky;top:84px;display:grid;gap:20px}
.article-widget{padding:22px;border:1px solid #e2e8f0;border-radius:15px;background:#fff;box-shadow:0 6px 20px rgba(15,23,42,.035)}
.article-widget-title{display:flex;align-items:center;gap:9px;margin:0 0 18px;font-size:14px;font-weight:900}.article-widget-title:before{content:"";width:4px;height:20px;border-radius:9px;background:#2563eb}
.recent-posts{display:grid;gap:17px}.recent-post{display:grid;grid-template-columns:78px 1fr;gap:12px;color:inherit;text-decoration:none}
.recent-post-image{overflow:hidden;height:70px;border-radius:9px;background:#f1f5f9}.recent-post-image img{width:100%;height:100%;object-fit:cover}.recent-post-image span{display:grid;place-items:center;height:100%;color:#94a3b8}
.recent-post h3{display:-webkit-box;overflow:hidden;margin:2px 0 7px;color:#1e293b;font-size:12px;line-height:1.4;font-weight:800;-webkit-box-orient:vertical;-webkit-line-clamp:2}
.recent-post:hover h3{color:#2563eb}.recent-post small{color:#94a3b8;font-size:9px;font-weight:700;text-transform:uppercase}
.article-cta{padding:26px;border-radius:15px;background:#102a56;color:#fff}.article-cta i{font-size:24px;color:#93c5fd}.article-cta h2{margin:16px 0 9px;font-size:21px;line-height:1.3;font-weight:900}.article-cta p{margin:0;color:#cbd5e1;font-size:12px;line-height:1.65}
.article-cta a{display:block;margin-top:20px;padding:12px;border-radius:9px;background:#fff;color:#1d4ed8;text-align:center;font-size:12px;font-weight:900;text-decoration:none}
.article-mobile-bar{display:none}
@media(max-width:1023px){
 .article-wrap{width:min(100% - 36px,860px)}.article-layout{grid-template-columns:1fr}.article-sidebar{position:static;grid-template-columns:1fr 1fr}.article-header{padding-top:34px}.article-header h1{font-size:44px}
}
@media(max-width:767px){
 .article-mobile-bar{position:sticky;z-index:30;top:0;display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #e2e8f0;background:#fff}
 .article-mobile-bar a{display:grid;place-items:center;width:38px;height:38px;border:1px solid #e2e8f0;border-radius:10px;color:#334155}.article-mobile-bar strong{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
 .article-wrap{width:calc(100% - 24px)}.article-breadcrumb{display:none}.article-header{padding:28px 0}.article-header h1{font-size:34px;letter-spacing:-1px}.article-summary{font-size:14px}
 .article-meta{align-items:flex-start;flex-direction:column}.article-share>span{display:none}.article-feature{margin-top:20px;border-radius:12px}.article-feature img{min-height:230px}.article-feature-empty{min-height:230px}
 .article-layout{gap:22px;padding:24px 0 45px}.article-main{padding:24px 18px}.article-body{font-size:15px}.article-body h2{font-size:24px}.article-body h3{font-size:20px}
 .article-thanks{align-items:flex-start;flex-direction:column}.article-thanks a{width:100%;text-align:center}.article-sidebar{grid-template-columns:1fr}.article-cta{padding:22px}
}
@media(prefers-reduced-motion:reduce){.article-share a,.recent-post h3{transition:none}}
</style>
@endpush

@section('content')
<div id="article-progress" class="article-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>

<div class="article-mobile-bar">
    <a href="{{ route('blogs.index') }}" aria-label="Back to articles"><i class="fas fa-arrow-left"></i></a>
    <strong>{{ $blog->title }}</strong>
</div>

<main class="article-page">
    <div class="article-wrap">
        <nav class="article-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><i class="fas fa-chevron-right text-[8px]"></i>
            <a href="{{ route('blogs.index') }}">Blog</a><i class="fas fa-chevron-right text-[8px]"></i>
            <span>{{ Str::limit($blog->title, 56) }}</span>
        </nav>
    </div>

    <header class="article-header">
        <div class="article-wrap article-header-inner">
            <span class="article-label"><i class="fas fa-book-open"></i>Rental guide</span>
            <h1>{{ $blog->title }}</h1>
            @if($blog->meta_description)
                <p class="article-summary">{{ $blog->meta_description }}</p>
            @endif
            <div class="article-meta">
                <div class="article-author">
                    <span class="article-author-icon"><i class="fas fa-pen-nib"></i></span>
                    <div>
                        <strong>{{ \App\Models\Setting::get('website_name', 'ApnaNest') }} Editorial</strong>
                        <span>{{ $blog->created_at->format('d M Y') }} · {{ max(1, ceil(str_word_count(strip_tags($blog->content)) / 200)) }} min read</span>
                    </div>
                </div>
                <div class="article-share">
                    <span>Share</span>
                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode($blog->title.' '.request()->url()) }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
            </div>
        </div>
    </header>

    <div class="article-wrap">
        <figure class="article-feature">
            @if($blog->featured_image)
                <img src="{{ $blog->featured_image }}" alt="{{ $blog->title }}" fetchpriority="high">
            @else
                <div class="article-feature-empty"><i class="fas fa-newspaper"></i></div>
            @endif
        </figure>

        <div class="article-layout">
            <article class="article-main">
                <div class="article-body">{!! $blog->content !!}</div>
                <div class="article-thanks">
                    <div><strong>Was this guide helpful?</strong><p>Explore more verified rooms and practical rental guides on ApnaNest.</p></div>
                    <a href="{{ route('rooms.index') }}">Browse verified rooms</a>
                </div>
            </article>

            <aside class="article-sidebar">
                @if($recentBlogs->isNotEmpty())
                    <section class="article-widget">
                        <h2 class="article-widget-title">Latest guides</h2>
                        <div class="recent-posts">
                            @foreach($recentBlogs as $recent)
                                <a href="{{ route('blogs.show', $recent->slug) }}" class="recent-post">
                                    <span class="recent-post-image">
                                        @if($recent->featured_image)<img src="{{ $recent->featured_image }}" alt="" loading="lazy">@else<span><i class="fas fa-newspaper"></i></span>@endif
                                    </span>
                                    <span><h3>{{ $recent->title }}</h3><small>{{ $recent->created_at->format('d M Y') }}</small></span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="article-cta">
                    <i class="fas fa-house-circle-check"></i>
                    <h2>Find your next home with confidence</h2>
                    <p>Compare verified listings, clear rents and property details before connecting with an owner.</p>
                    <a href="{{ route('rooms.index') }}">Explore rooms</a>
                </section>
            </aside>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('scroll', function () {
    const progress = document.getElementById('article-progress');
    if (!progress) return;
    const available = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const value = available > 0 ? Math.min(100, Math.max(0, (window.scrollY / available) * 100)) : 0;
    progress.style.width = value + '%';
    progress.setAttribute('aria-valuenow', Math.round(value));
}, { passive: true });
</script>
@endpush
