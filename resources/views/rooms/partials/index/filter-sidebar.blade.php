        <!-- ===== LEFT SIDEBAR (FILTERS) ===== -->
        <div class="w-full lg:w-[280px] xl:w-[300px] flex-shrink-0 hidden lg:block">
            <div class="rooms-filter-panel filter-sticky bg-white border rounded-2xl p-5 space-y-4">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-800 text-base">Filters</h3>
                    <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors flex items-center gap-1">
                        <i class="fas fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <style>
                    .rooms-filter-panel .hover-text-primary:hover { color: var(--primary) !important; }
                    .rooms-filter-panel .text-primary { color: var(--primary) !important; }
                    .rooms-filter-panel .bg-primary-soft { background: rgba(var(--primary-rgb), 0.08) !important; }
                    .rooms-filter-panel .text-primary-soft { color: var(--primary) !important; }
                    .rooms-filter-panel input[type="checkbox"]:checked { background-color: var(--primary); border-color: var(--primary); }
                    .rooms-filter-panel input[type="radio"]:checked { background-color: var(--primary); border-color: var(--primary); }
                    .rooms-filter-panel input:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15) !important; }
                    .rooms-filter-panel select:focus { border-color: var(--primary) !important; }
                    .room-theme-primary-button { background: var(--primary); color: #fff; }
                    .room-theme-primary-button:hover { background: var(--primary-dark); }
                </style>

                <form action="{{ route('rooms.index') }}" method="GET" class="rooms-filter-form space-y-4">
                    <!-- Locality Input -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Location</label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Enter locality or area..."
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                        
                        {{-- City dropdown — fully dynamic from DB popular cities --}}
                        <select name="city_dropdown" onchange="if(this.value){ document.querySelector('input[name=city]').value = this.value; }"
                                class="w-full py-2 px-3 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold focus:ring-2 outline-none appearance-none transition-all">
                            <option value="">Select City</option>
                            @foreach($popularCities as $pCity)
                                <option value="{{ $pCity->name }}" {{ $displayCity === $pCity->name ? 'selected' : '' }}>
                                    {{ $pCity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Listed By Filter -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Listed By</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                <input type="radio" name="listing_type" value="" {{ !request('listing_type') ? 'checked' : '' }}
                                       class="text-indigo-600 border-slate-300">
                                <span>All Listings</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                <input type="radio" name="listing_type" value="owner" {{ request('listing_type') === 'owner' ? 'checked' : '' }}
                                       class="text-emerald-600 border-slate-300">
                                <span class="flex items-center gap-1.5"><i class="fas fa-shield-check text-emerald-600 text-[10px]"></i> Direct Owner (0% Brokerage)</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                <input type="radio" name="listing_type" value="broker" {{ request('listing_type') === 'broker' ? 'checked' : '' }}
                                       class="text-amber-500 border-slate-300">
                                <span class="flex items-center gap-1.5"><i class="fas fa-user-tie text-amber-500 text-[10px]"></i> Verified Agent</span>
                            </label>
                        </div>
                    </div>

                    <!-- Property Type — dynamic: only shows types that exist in DB -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Property Type</label>
                        <div class="space-y-2.5">
                            @forelse($propertyTypes as $type)
                                @php
                                    $count = $propertyTypeCounts[$type->id] ?? 0;
                                    $isChecked = in_array($type->id, (array)request('property_type_id'));
                                @endphp
                                <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" name="property_type_id[]" value="{{ $type->id }}" {{ $isChecked ? 'checked' : '' }}
                                               class="rounded border-slate-300">
                                        <span>{{ $type->name }}</span>
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $count }}</span>
                                </label>
                            @empty
                                <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                    No active property types configured.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Property Category</label>
                        <div class="space-y-2.5">
                            @forelse(($propertyCategories ?? collect()) as $category)
                                @php
                                    $count = $propertyCategoryCounts[$category->id] ?? 0;
                                    $isChecked = in_array($category->id, (array)request('property_category_id'));
                                @endphp
                                @if($count > 0 || $isChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="property_category_id[]" value="{{ $category->id }}" {{ $isChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300">
                                            <span>{{ $category->name }}</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $count }}</span>
                                    </label>
                                @endif
                            @empty
                                <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                    No active property categories configured.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Budget Range -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Budget (per month)</label>
                        <div class="space-y-2">
                            @php
                                $budgetRanges = [
                                    ['label' => 'Under ₹5,000', 'min' => 0, 'max' => 5000],
                                    ['label' => '₹5,000 - ₹10,000', 'min' => 5000, 'max' => 10000],
                                    ['label' => '₹10,000 - ₹15,000', 'min' => 10000, 'max' => 15000],
                                    ['label' => '₹15,000 - ₹20,000', 'min' => 15000, 'max' => 20000],
                                    ['label' => 'Above ₹20,000', 'min' => 20000, 'max' => 999999],
                                ];
                            @endphp
                            @foreach($budgetRanges as $range)
                                @php
                                    $isSel = request('min_rent') == $range['min'] && request('max_rent') == $range['max'];
                                @endphp
                                <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                    <input type="radio" name="budget_range" onchange="document.querySelector('input[name=min_rent]').value='{{ $range['min'] }}'; document.querySelector('input[name=max_rent]').value='{{ $range['max'] }}';"
                                           {{ $isSel ? 'checked' : '' }}
                                           class="rounded border-slate-300">
                                    <span>{{ $range['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <!-- Manual Min / Max inputs -->
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Min</span>
                                <input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="₹ Min"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Max</span>
                                <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="₹ Max"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Gender Preference — dynamic from DB tenant type counts -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Area (sq ft)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Min</span>
                                <input type="number" name="min_area_sqft" value="{{ request('min_area_sqft') }}" placeholder="Min sqft"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Max</span>
                                <input type="number" name="max_area_sqft" value="{{ request('max_area_sqft') }}" placeholder="Max sqft"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Gender Preference</label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                @php
                                    $tCount = $tenantTypeCounts[$option->id] ?? 0;
                                    $tChecked = in_array($option->id, (array)request('tenant_type'));
                                @endphp
                                @if($tCount > 0 || $tChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="tenant_type[]" value="{{ $option->id }}" {{ $tChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $tCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Furnishing Type — dynamic from DB -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Furnishing</label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                                @php
                                    $fCount = $furnishingCounts[$option->id] ?? 0;
                                    $fChecked = in_array($option->id, (array)request('furnishing_type'));
                                @endphp
                                @if($fCount > 0 || $fChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="furnishing_type[]" value="{{ $option->id }}" {{ $fChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $fCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="space-y-2">
                        @php
                            $selectedAmenities = (array) request('amenities');
                        @endphp
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Facilities</label>
                            @if(count($selectedAmenities))
                                <span class="rounded-full bg-primary-soft px-2 py-0.5 text-[9px] font-black text-primary-soft">{{ count($selectedAmenities) }} selected</span>
                            @endif
                        </div>
                        <div class="rooms-amenities-scroll space-y-2">
                            @php
                                $amenityOpts = \App\Models\RoomOption::optionsFor('amenity')
                                    ->pluck('label', 'label')
                                    ->all();
                            @endphp
                            @foreach($amenityOpts as $key => $lbl)
                                <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                    <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, (array)request('amenities')) ? 'checked' : '' }}
                                           class="rounded border-slate-300">
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Availability</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover-text-primary transition-colors">
                                <input type="checkbox" name="available_now" value="1" {{ request('available_now') == '1' ? 'checked' : '' }}
                                       class="rounded border-slate-300">
                                <span>Available Now</span>
                            </label>
                            
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Available From</span>
                                <input type="date" name="availability_from" value="{{ request('availability_from') }}"
                                       class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="rooms-filter-actions">
                        <button type="submit" class="room-theme-primary-button w-full py-2.5 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 text-xs">
                            <i class="fas fa-filter text-[10px]"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
            <div class="mt-5">
                @include('partials.offer-banner', ['placement' => 'sidebar'])
            </div>
        </div>
