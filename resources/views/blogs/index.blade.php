@extends('layouts.public')

@section('title', 'RoomRental Blog - The Rental Journal')
@section('description', 'Discover the latest trends in rental living, property management issues, and city guides.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/blogs-index.css') }}">
@endpush

@section('content')
<!-- App-like Blog Header -->
<div class="bg-white border-b border-gray-100 sticky top-0 z-30 lg:relative">
    <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="w-full md:w-auto text-center md:text-left">
             <h1 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight leading-none mb-1">The Rental Journal</h1>
             <p class="text-[10px] md:text-sm text-gray-500 font-bold uppercase tracking-widest">Insights for modern living</p>
        </div>
        <div class="relative w-full md:w-64">
            <form action="{{ route('blogs.index') }}" method="GET">
                <label for="search-input" class="sr-only">Search articles</label>
                <input id="search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500/20 placeholder-gray-400">
                <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 p-2" aria-label="Search">
                    <i class="fas fa-search text-base"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">

        @if($blogs->count() > 0)
            <!-- Trending Horizontal Scroll (Mobile) -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 blog-theme-secondary-soft rounded-lg flex items-center justify-center" aria-hidden="true">
                            <i class="fas fa-bolt text-sm"></i>
                        </div>
                        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Trending</h2>
                    </div>
                </div>
                
                <div class="flex overflow-x-auto pb-4 gap-4 no-scrollbar -mx-4 px-4 md:grid md:grid-cols-4 md:mx-0 md:px-0">
                    @foreach($blogs->take(4) as $trending)
                        <a href="{{ route('blogs.show', $trending->slug) }}" class="blog-trending-card flex-shrink-0 w-64 md:w-full group relative block h-40 rounded-3xl overflow-hidden shadow-lg transition active:scale-95">
                             @if($trending->image)
                                <img src="{{ $trending->featured_image }}" alt="{{ $trending->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                             @else
                                <div class="w-full h-full blog-theme-gradient" aria-hidden="true"></div>
                             @endif
                             <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" aria-hidden="true"></div>
                             <div class="absolute bottom-4 left-4 right-4">
                                 <span class="inline-block bg-white/20 backdrop-blur-md text-[8px] text-white font-bold px-2 py-0.5 rounded-full uppercase tracking-widest mb-2 border border-white/20">Featured</span>
                                 <h3 class="blog-trending-title text-white text-sm font-bold line-clamp-2 leading-tight transition">{{ $trending->title }}</h3>
                             </div>
                        </a>
                    @endforeach
                </div>

                <!-- Mobile Offer Banner (Restoring original design but adding this) -->
                <div class="lg:hidden mt-6">
                    @include('partials.offer-banner', ['placement' => 'mobile_feed'])
                </div>
            </div>

            <!-- Main Content Grid with Sidebar -->
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Articles Column -->
                <div class="lg:w-3/4">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 blog-theme-primary-soft rounded-xl flex items-center justify-center" aria-hidden="true">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-gray-900 leading-none">Latest Stories</h2>
                            </div>
                        </div>
                        <span class="bg-gray-200 text-gray-700 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest">{{ $blogs->total() }} Posts</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($blogs as $blog)
                            <article class="blog-card bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                                <!-- Image -->
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="block relative aspect-[16/10] overflow-hidden">
                                     @if($blog->image)
                                        <img src="{{ $blog->featured_image }}" alt="{{ $blog->title }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700" loading="lazy">
                                     @else
                                        <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300" aria-hidden="true">
                                            <i class="fas fa-image text-3xl"></i>
                                        </div>
                                     @endif
                                     <div class="absolute top-4 left-4">
                                         <span class="blog-theme-primary-bg backdrop-blur-md text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest">Guide</span>
                                     </div>
                                </a>
                                
                                <!-- Content -->
                                <div class="p-6">
                                    <h3 class="blog-card-title text-base font-bold text-gray-900 leading-tight mb-3 transition duration-300 line-clamp-2">
                                        <a href="{{ route('blogs.show', $blog->slug) }}">
                                            {{ $blog->title }}
                                        </a>
                                    </h3>
                                    
                                    <p class="text-gray-500 text-xs line-clamp-2 mb-4 leading-relaxed">
                                        {{ Str::limit(strip_tags($blog->content), 80) }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                        <span class="text-[9px] font-bold text-gray-600 uppercase tracking-widest">{{ $blog->created_at->format('M d, Y') }}</span>
                                        <div class="flex items-center gap-1.5 text-[9px] font-bold blog-theme-primary uppercase tracking-widest">
                                            <i class="far fa-clock" aria-hidden="true"></i>
                                            <span>{{ max(1, ceil(str_word_count(strip_tags($blog->content)) / 200)) }}m read</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-center text-sm">
                        {{ $blogs->links() }}
                    </div>
                </div>

                <!-- Sidebar (Desktop Only) -->
                <div class="hidden lg:block lg:w-1/4 space-y-8">
                    <!-- Trending Widget -->
                    <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm">
                         <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-4 blog-theme-secondary-bg rounded-full" aria-hidden="true"></span>
                            Trending
                         </h3>
                         <div class="space-y-6">
                            @foreach($blogs->take(3) as $pop)
                                <a href="{{ route('blogs.show', $pop->slug) }}" class="blog-popular-link flex gap-4 group">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden flex-shrink-0">
                                        @if($pop->image)
                                            <img src="{{ $pop->featured_image }}" alt="{{ $pop->title }}" class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300 text-xs" aria-hidden="true">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="blog-popular-title text-xs font-bold text-gray-900 line-clamp-2 transition">{{ $pop->title }}</h4>
                                        <span class="text-[10px] text-gray-600 mt-1 block">{{ $pop->created_at->format('M d') }}</span>
                                    </div>
                                </a>
                            @endforeach
                         </div>
                    </div>

                    <!-- Sidebar Offers Inject (Added here for visibility) -->
                    @include('partials.offer-banner', ['placement' => 'sidebar'])

                    <!-- Newsletter Widget -->
                    <div class="blog-newsletter rounded-[2rem] p-6 text-white relative overflow-hidden shadow-xl">
                        <div class="absolute -right-4 -bottom-4 opacity-20 transform rotate-12" aria-hidden="true">
                             <i class="fas fa-paper-plane text-6xl"></i>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-lg font-black mb-2">Subscribe</h3>
                            <p class="text-xs mb-4">Get the best rental tips in your inbox.</p>
                            <form id="sidebar-newsletter" class="space-y-2">
                                <label for="email-input" class="sr-only">Your email address</label>
                                <input id="email-input" type="email" placeholder="Your email" class="w-full bg-white/10 border border-white/20 text-white placeholder-white/60 px-4 py-2 rounded-xl text-xs focus:ring-1 focus:ring-white/30 backdrop-blur-sm">
                                <button type="submit" class="w-full bg-white font-bold py-2 rounded-xl text-xs transition shadow-lg">Join us</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full text-gray-400 mb-3" aria-hidden="true">
                    <i class="fas fa-archive"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-900">No content yet</h3>
            </div>
        @endif
        
    </div>
</div>
@endsection
