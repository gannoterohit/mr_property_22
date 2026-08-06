<section class="market-section">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">{{ $text('home_category_eyebrow','Explore your options') }}</span><h2>{{ $text('home_category_title','Find the right kind of home') }}</h2><p>{{ $text('home_category_description','Start with a property type that matches your lifestyle and budget.') }}</p></div><a href="{{ route('rooms.index') }}">Browse all rooms <i class="fas fa-arrow-right"></i></a></div>
        <div class="market-types">
            @forelse($propertyCategories->take(6) as $category)
                <a href="{{ route('rooms.index',['property_category_id' => $category->property_category_id]) }}" class="market-type"><span><i class="fas fa-building"></i></span><div><strong>{{ $category->label }}</strong><small>{{ $category->total ?? 0 }} available</small></div></a>
            @empty
                <div class="market-empty"><i class="fas fa-building"></i><p>Property categories will appear here.</p></div>
            @endforelse
        </div>
    </div>
</section>
