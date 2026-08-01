@push('styles')
<style>
    @media (min-width: 768px) {
        .home-page-grid-5 {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .default-page-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .default-page-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1280px) {
        .default-page-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .theme-room-card:hover {
        border-color: rgba(var(--primary-rgb), 0.3);
    }

    .theme-room-empty-image {
        background: rgba(var(--primary-rgb), 0.06);
    }

    .theme-room-empty-image i {
        color: rgba(var(--primary-rgb), 0.25);
    }

    .theme-room-empty-image span,
    .theme-room-price-badge small {
        color: rgba(255, 255, 255, 0.75);
    }

    .theme-room-empty-image span {
        color: rgba(var(--primary-rgb), 0.42);
    }

    .theme-room-type-badge,
    .theme-room-card:hover .theme-room-title {
        color: var(--primary);
    }

    .theme-room-broker-badge,
    .theme-room-owner-badge {
        background: var(--secondary);
        color: #fff;
        border-color: rgba(var(--secondary-rgb), 0.55);
    }

    .theme-room-price-badge {
        background: var(--primary);
        color: #fff;
    }

    .theme-room-primary-icon {
        color: rgba(var(--primary-rgb), 0.65);
    }

    .theme-room-secondary-dot {
        background: var(--secondary);
    }

    .theme-room-secondary-text {
        color: var(--secondary);
    }
</style>
@endpush

<!-- Desktop Grid (Visible on Desktop/Tablet) -->
<div class="hidden md:grid grid-cols-1 gap-4 {{ ($homePage ?? false) ? 'home-page-grid-5' : 'default-page-grid' }}">
    @foreach($rooms as $room)
        <div class="theme-room-card group bg-white rounded-xl shadow-md hover:shadow-xl transition-all transition-colors duration-300 overflow-hidden border border-slate-100 transform hover:-translate-y-1 flex flex-col h-full">
            <!-- Image Container (Clickable) - Reduced Height -->
            <a href="{{ route('rooms.show', $room->id) }}" class="relative block h-44 overflow-hidden bg-slate-100">
                @if($room->photo_url)
                    @php
                        $photoUrl = $room->photo_url;
                        if (str_contains($photoUrl, 'unsplash.com')) {
                            $baseUrl = strtok($photoUrl, '?');
                            $optimizedUrl = $baseUrl . '?w=600&h=400&fm=webp&q=85&fit=crop';
                        } else {
                            $optimizedUrl = $photoUrl;
                        }
                    @endphp
                    <img src="{{ $optimizedUrl }}"
                         alt="{{ $room->title }}"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Room+Image+Coming+Soon';">
                @else
                    <div class="theme-room-empty-image w-full h-full flex flex-col items-center justify-center">
                        <i class="fas fa-house-chimney text-4xl mb-1"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Image Coming Soon</span>
                    </div>
                @endif
                
                <!-- Status Badges - Smaller -->
                <div class="absolute top-2 left-2 flex flex-col gap-1.5 z-10">
                    @if($room->is_featured)
                        <span class="bg-amber-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg shadow-lg">Featured</span>
                    @endif
                    <span class="theme-room-type-badge bg-white/90 backdrop-blur-md dark:bg-slate-800/90 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg shadow-sm border border-white/50 dark:border-slate-700">
                        {{ $room->roomTypeLabel() }}
                    </span>
                    
                    @if($room->listing_type === 'broker')
                        <span class="theme-room-broker-badge text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg shadow-lg border">
                            Broker: ₹{{ $room->broker_fee }}
                        </span>
                    @else
                        <span class="theme-room-owner-badge text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg shadow-lg border">
                            No Broker Fee
                        </span>
                    @endif
                </div>

                {{-- Wishlist Toggle - Smaller --}}
                <button onclick="toggleWishlist(event, {{ $room->id }})" 
                        class="absolute top-2 right-2 w-8 h-8 bg-white/90 backdrop-blur-md rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 transition-all shadow-lg active:scale-90 z-10 hover:bg-white"
                        id="wishlist-btn-{{ $room->id }}">
                    <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas text-red-500' : 'far' }} fa-heart text-sm"></i>
                </button>
                
                <!-- Price Floating Tag - Smaller -->
                <div class="absolute bottom-2 left-2">
                    <div class="theme-room-price-badge px-3 py-1.5 rounded-lg shadow-xl border border-white/20">
                        <span class="text-base font-black">₹{{ number_format($room->rent) }}</span>
                        <small class="text-[8px] uppercase font-bold ml-1">/mo</small>
                    </div>
                </div>
            </a>
            
            <!-- Content - Reduced Padding -->
            <div class="p-3 flex flex-col flex-grow">
                <div class="mb-2">
                    <h3 class="theme-room-title font-bold text-sm mb-1 text-slate-900 line-clamp-1 transition-colors font-heading">
                        <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
                    </h3>
                    <div class="flex flex-col">
                        <div class="flex items-center text-slate-500 text-xs">
                            <i class="theme-room-primary-icon fas fa-location-dot mr-1.5 text-[10px]"></i>
                            <span class="font-medium">{{ $room->city }}</span>
                        </div>
                        <!-- Distance Tag -->
                        <div class="distance-tag hidden mt-0.5 flex items-center gap-1" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                            <div class="theme-room-secondary-dot w-1 h-1 rounded-full animate-pulse"></div>
                            <span class="theme-room-secondary-text text-[8px] font-black uppercase tracking-widest"><span class="distance-km">0</span> km away</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Tags - Smaller -->
                <div class="flex flex-wrap gap-1.5 mb-3 mt-auto">
                    <div class="flex items-center bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                        <i class="theme-room-primary-icon fas fa-couch text-[10px] mr-1.5"></i>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->furnishingTypeLabel() }}</span>
                    </div>
                    @if($room->tenantTypeLabel() !== 'N/A')
                    <div class="flex items-center bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                        <i class="theme-room-primary-icon fas fa-users text-[10px] mr-1.5"></i>
                        <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->tenantTypeLabel() }}</span>
                    </div>
                    @endif
                </div>
                
                <!-- Action - Smaller Button -->
                @auth
                    @if(Auth::user()->role === 'owner' && Auth::id() === $room->user_id)
                        <div class="grid grid-cols-2 gap-2 mt-auto">
                            <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-center bg-amber-50 text-amber-700 font-bold py-2 rounded-lg hover:bg-amber-100 transition-colors text-xs">
                                <i class="fas fa-edit mr-1.5 text-[10px]"></i>Edit
                            </a>
                            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="delete-room-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center bg-red-50 text-red-600 font-bold py-2 rounded-lg hover:bg-red-100 transition-colors text-xs">
                                    <i class="fas fa-trash mr-1.5 text-[10px]"></i>Delete
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('rooms.show', $room->id) }}" 
                           class="group/btn relative overflow-hidden text-white text-center font-bold py-2.5 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg mt-auto text-xs"
                           style="background-color: var(--primary);">
                            <span class="relative z-10 flex items-center justify-center">
                                View Details
                                <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform text-[10px]"></i>
                            </span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('rooms.show', $room->id) }}" 
                       class="group/btn relative overflow-hidden text-white text-center font-bold py-2.5 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg mt-auto text-xs"
                       style="background-color: var(--primary);">
                        <span class="relative z-10 flex items-center justify-center">
                            View Details
                            <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform text-[10px]"></i>
                        </span>
                    </a>
                @endauth
            </div>
        </div>
    @endforeach
</div>

@unless($homePage ?? false)
    <!-- Pagination (Desktop) -->
    <div class="hidden md:flex justify-center mt-3 mb-8 custom-pagination">
        <div class="inline-block">
            {{ $rooms->withQueryString()->links() }}
        </div>
    </div>
@endunless
