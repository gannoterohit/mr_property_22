@include('partials.adsense-slot', ['placement' => 'home_bottom'])

<section class="market-section soft">
    <div class="market-wrap market-editorial">
        <div class="market-blogs"><span class="market-kicker">Rental knowledge</span><h2>{{ $text('home_blog_title','Helpful guides and updates') }}</h2><div class="market-blog-list">@forelse($latestBlogs->take(3) as $blog)<a href="{{ route('blogs.show',$blog->slug) }}" class="market-blog">@if($blog->featured_image)<img src="{{ $blog->featured_image }}" alt="{{ $blog->title }}" loading="lazy">@else<div style="height:110px;border-radius:10px;background:#eef2ff;display:grid;place-items:center;color:#818cf8"><i class="fas fa-newspaper"></i></div>@endif<h3>{{ $blog->title }}</h3><small>{{ optional($blog->published_at ?? $blog->created_at)->format('d M Y') }}</small></a>@empty<div class="market-empty"><p>Helpful rental guides will appear here.</p></div>@endforelse</div></div>
<<<<<<< HEAD
        @if($faqs->isNotEmpty())
            <div class="market-faq"><span class="market-kicker">Frequently asked questions</span><h2>Find answers to common questions</h2><div class="market-faq-list">@foreach($faqs as $faq)<details><summary>{{ $faq['question'] }}<i class="fas fa-plus"></i></summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div></div>
        @endif
=======
        <div class="market-faq"><span class="market-kicker">Frequently asked questions</span><h2>Find answers to common questions</h2><div class="market-faq-list">@foreach([['Is there any brokerage on '.$siteName.'?','No. You can connect directly with property owners.'],['How can I contact the owner?','Open a verified listing and use the contact unlock option.'],['Is the property information verified?','Listings go through platform review before approval.'],['How do I list my property?','Create an owner account and submit your room details.'],['Is it safe to pay online?','Payments use the configured secure payment gateway.']] as $faq)<details><summary>{{ $faq[0] }}<i class="fas fa-plus"></i></summary><p>{{ $faq[1] }}</p></details>@endforeach</div></div>
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    </div>
</section>
