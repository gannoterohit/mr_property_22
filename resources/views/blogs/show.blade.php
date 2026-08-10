@extends('layouts.public')

@section('title', $blog->meta_title ?: $blog->title)
@section('description', $blog->meta_description ?: Str::limit(strip_tags($blog->content), 160))
@section('keywords', $blog->meta_keywords)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/blog-show.css') }}">
@endpush

@section('content')
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
