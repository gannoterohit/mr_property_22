            <!-- Rooms list -->
            @if($rooms->count() > 0)
                <!-- Desktop Columns Grid (Flexbox wrapper for guaranteed column layout) -->
                <div class="hidden md:flex flex-wrap -mx-2.5">
                    @foreach($rooms as $room)
                        <div class="w-full md:w-1/2 xl:w-1/3 px-2.5 mb-5 flex flex-col">
                            <div class="room-listing-card group bg-white rounded-2xl border transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                                <!-- Image Area -->
                                <a href="{{ route('rooms.show', $room->id) }}" class="room-image relative block overflow-hidden bg-slate-100">
                                    @if($room->photo_url)
                                        <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                            <i class="fas fa-image text-3xl mb-1"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                        </div>
                                    @endif

                                    <!-- Status Badges -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 z-10">
                                        @if($room->is_featured)
                                            <span class="bg-amber-500 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">Featured</span>
                                        @endif
                                        <span class="room-theme-type-badge bg-white/90 backdrop-blur-sm text-[8px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-lg border border-white/40 shadow-sm">
                                            {{ $room->roomTypeLabel() }}
                                        </span>
                                        @if($room->listing_type === 'broker')
                                            <span class="room-theme-secondary-badge text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">Broker Fee</span>
                                        @else
                                            <span class="room-theme-secondary-badge text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">No Broker Fee</span>
                                        @endif
                                    </div>

                                    <!-- Wishlist heart -->
                                    <button onclick="toggleWishlist(event, {{ $room->id }})" id="wishlist-btn-{{ $room->id }}"
                                            class="absolute top-2.5 right-2.5 w-8 h-8 rounded-xl bg-white/95 backdrop-blur-sm shadow-md text-slate-400 hover:text-red-500 active:scale-90 transition-all flex items-center justify-center">
                                        <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas text-red-500' : 'far' }} fa-heart text-sm"></i>
                                    </button>

                                    <!-- Price tag overlay -->
                                    <div class="absolute bottom-2.5 left-2.5">
                                        <div class="room-price-tag px-3 py-1 rounded-xl">
                                            <span class="text-sm font-black">₹{{ number_format($room->rent) }}</span>
                                            <span class="text-[8px] font-bold">/mo</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Card content -->
                                <div class="room-card-body flex flex-col flex-grow">
                                    <h3 class="font-bold text-sm text-slate-900 line-clamp-2 mb-2 transition-colors">
                                        <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
                                    </h3>

                                    <div class="flex items-center text-slate-500 text-xs mb-3">
                                        <i class="room-theme-primary-icon fas fa-location-dot mr-1.5"></i>
                                        <span>{{ $room->city }}</span>
                                        <div class="distance-tag hidden ml-2 flex items-center gap-1" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                            <div class="room-theme-secondary-dot w-1 h-1 rounded-full"></div>
                                            <span class="room-theme-secondary-text text-[9px] font-extrabold uppercase tracking-widest"><span class="distance-km">0</span> km</span>
                                        </div>
                                    </div>

                                    <!-- Quick Specs -->
                                    <div class="flex flex-wrap gap-1.5 mb-4 mt-auto">
                                        <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                            <i class="room-theme-primary-icon fas fa-couch"></i> {{ $room->furnishingTypeLabel() }}
                                        </span>
                                        @if($room->tenantTypeLabel() !== 'N/A')
                                            <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                <i class="room-theme-primary-icon fas fa-users"></i> {{ $room->tenantTypeLabel() }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="room-owner-row">
                                        <img src="{{ $room->user?->avatar ? asset('storage/'.$room->user->avatar) : asset('assets/images/default-avatar.svg') }}"
                                             alt="{{ $room->user?->name ?? 'Property owner' }}"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'">
                                        <span>Owner: <strong>{{ $room->user?->name ?? 'Verified owner' }}</strong></span>
                                    </div>

                                    <!-- Bottom actions -->
                                    @auth
                                        @if(Auth::user()->role === 'owner' && Auth::id() === $room->user_id)
                                            <div class="grid grid-cols-2 gap-2 mt-auto">
                                                <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-center bg-amber-50 text-amber-700 font-extrabold py-2 rounded-xl hover:bg-amber-100 transition-colors text-xs">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="delete-room-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center justify-center bg-red-50 text-red-600 font-extrabold py-2 rounded-xl hover:bg-red-100 transition-colors text-xs">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('rooms.show', $room->id) }}" class="room-theme-primary-button w-full py-2 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                                View Details <i class="fas fa-arrow-right text-[10px]"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('rooms.show', $room->id) }}" class="room-theme-primary-button w-full py-2 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                            View Details <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Mobile listing support -->
                <div class="md:hidden">
                    @include('rooms.partials.listing-mobile')
                </div>

                <!-- Custom premium layout pagination -->
                <div class="flex justify-center mt-8">
                    {{ $rooms->withQueryString()->links() }}
                </div>
            @else
                <!-- Empty state fallback -->
                <div class="text-center py-16 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
                    <div class="max-w-md mx-auto">
                        <div class="room-theme-primary-soft inline-flex items-center justify-center w-20 h-20 rounded-full mb-6 shadow-sm">
                            <i class="fas fa-house-circle-xmark text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">No Rooms Found</h3>
                        <p class="text-slate-500 mb-6 text-sm">We couldn't find any rooms matching your search criteria. Try modifying your filters or view all rooms.</p>
                        <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="room-theme-primary-button inline-flex items-center justify-center font-extrabold py-2.5 px-6 rounded-xl transition-all shadow-md text-xs">
                            <i class="fas fa-rotate-left mr-1.5"></i> Clear All Filters
                        </a>
                        
                        @if(request('city'))
                            <div class="room-theme-alert-box mt-8 p-5 border rounded-2xl">
                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Get Alerted</h4>
                                <p class="text-slate-500 text-xs mb-3">Subscribe and we will email you when new rooms open up in <strong>{{ request('city') }}</strong>.</p>
                                <button onclick="subscribeToAlerts('{{ request('city') }}')" id="notify-btn"
                                        class="room-theme-primary-button py-2 px-4 font-extrabold rounded-xl text-xs transition-all shadow-sm">
                                    <i class="fas fa-bell mr-1"></i> Notify Me
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
