            <!-- Header bar inside list -->
            <div class="rooms-results-head flex items-center justify-between mb-5 flex-wrap gap-3">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 font-heading">
                        All Properties in {{ $cityContext['activeCityName'] ?? $displayCity ?? 'India' }}
                    </h2>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">
                        {{ $rooms->total() }}+ Properties Found
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Sort selection -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-400">Sort by:</span>
                        <select onchange="const url = new URL(window.location.href); url.searchParams.set('sort_by', this.value); window.location.href = url.toString();"
                                class="py-1.5 pl-3 pr-8 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none cursor-pointer">
                            <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="rent_asc" {{ request('sort_by') == 'rent_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="rent_desc" {{ request('sort_by') == 'rent_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Layout Grid/List selectors -->
                    <div class="hidden sm:flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                        <button class="rooms-theme-link w-7 h-7 bg-white rounded-lg flex items-center justify-center text-xs shadow-sm" title="Grid view">
                            <i class="fas fa-grip-vertical"></i>
                        </button>
                        <button class="w-7 h-7 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center text-xs transition-colors" title="List view">
                            <i class="fas fa-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filters Pills row -->
            @php
                $activeFilters = [];
                if (request('city')) $activeFilters['city'] = ['label' => 'City: ' . request('city'), 'param' => 'city'];
                if (request('property_type_id')) {
                    foreach((array)request('property_type_id') as $t) {
                        $activeFilters['property_type_' . $t] = ['label' => \App\Models\PropertyType::find($t)?->name ?? 'Type', 'param' => 'property_type_id', 'value' => $t];
                    }
                }
                if (request('property_category_id')) {
                    foreach((array)request('property_category_id') as $c) {
                        $activeFilters['property_category_' . $c] = ['label' => \App\Models\PropertyCategory::find($c)?->name ?? 'Category', 'param' => 'property_category_id', 'value' => $c];
                    }
                }
                if (request('room_type')) {
                    foreach((array)request('room_type') as $t) {
                        $activeFilters['room_type_' . $t] = ['label' => \App\Models\RoomOption::getLabel('room_type', $t), 'param' => 'room_type', 'value' => $t];
                    }
                }
                if (request('min_rent') || request('max_rent')) {
                    $label = 'Budget: ';
                    if (request('min_rent') && request('max_rent')) $label .= '₹' . request('min_rent') . ' - ₹' . request('max_rent');
                    elseif (request('min_rent')) $label .= 'Min ₹' . request('min_rent');
                    else $label .= 'Max ₹' . request('max_rent');
                    $activeFilters['budget'] = ['label' => $label, 'param' => ['min_rent', 'max_rent']];
                }
                if (request('min_area_sqft') || request('max_area_sqft')) {
                    $label = 'Area: ';
                    if (request('min_area_sqft') && request('max_area_sqft')) $label .= request('min_area_sqft') . ' - ' . request('max_area_sqft') . ' sqft';
                    elseif (request('min_area_sqft')) $label .= 'Min ' . request('min_area_sqft') . ' sqft';
                    else $label .= 'Max ' . request('max_area_sqft') . ' sqft';
                    $activeFilters['area_sqft'] = ['label' => $label, 'param' => ['min_area_sqft', 'max_area_sqft']];
                }
                if (request('tenant_type')) {
                    foreach((array)request('tenant_type') as $t) {
                        $activeFilters['tenant_type_' . $t] = ['label' => \App\Models\RoomOption::getLabel('tenant_type', $t), 'param' => 'tenant_type', 'value' => $t];
                    }
                }
                if (request('furnishing_type')) {
                    foreach((array)request('furnishing_type') as $f) {
                        $activeFilters['furnishing_type_' . $f] = ['label' => \App\Models\RoomOption::getLabel('furnishing_type', $f), 'param' => 'furnishing_type', 'value' => $f];
                    }
                }
                if (request('amenities')) {
                    foreach((array)request('amenities') as $a) {
                        $activeFilters['amenity_' . $a] = ['label' => ucwords(str_replace('_', ' ', $a)), 'param' => 'amenities', 'value' => $a];
                    }
                }
            @endphp
            @if(!empty($activeFilters))
                <div class="flex items-center gap-2 flex-wrap mb-6 bg-slate-50 border border-slate-200/60 rounded-2xl p-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Active Filters:</span>
                    @foreach($activeFilters as $key => $filter)
                        @php
                            if (is_array($filter['param'])) {
                                $newParams = request()->except($filter['param']);
                            } else {
                                if (isset($filter['value'])) {
                                    $arr = (array)request($filter['param']);
                                    $newArr = array_filter($arr, fn($val) => $val !== $filter['value']);
                                    $newParams = request()->except($filter['param']);
                                    if(!empty($newArr)) {
                                        $newParams[$filter['param']] = array_values($newArr);
                                    }
                                } else {
                                    $newParams = request()->except($filter['param']);
                                }
                            }
                            unset($newParams['clear']);
                        @endphp
                        <a href="{{ route('rooms.index', $newParams) }}"
                           class="inline-flex items-center gap-1 bg-white border border-slate-200/80 hover:border-red-300 text-slate-600 hover:text-red-500 text-[10px] font-bold px-2.5 py-0.5 rounded-full transition-all">
                            <span>{{ $filter['label'] }}</span>
                            <i class="fas fa-times text-[8px]"></i>
                        </a>
                    @endforeach
                    <a href="{{ route('rooms.index', ['clear' => 1]) }}"
                       class="text-[10px] font-black text-red-500 hover:text-red-700 transition-colors uppercase tracking-wider ml-1">
                        Clear All
                    </a>
                </div>
            @endif
