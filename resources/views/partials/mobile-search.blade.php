<div class="bg-white pb-2 shadow-sm relative z-10 rounded-b-2xl">
    <!-- Main Search Input -->
    <div class="px-4 pt-2">
        <h1 class="sr-only">Find Rooms & PG</h1>
        <form action="{{ route('rooms.index') }}" method="GET" class="group">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-indigo-600 transition-colors text-sm"></i>
                </div>
                <input type="text" 
                       name="city" 
                       value="{{ request('city') }}" 
                       class="block w-full pl-12 pr-3 py-3 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 sm:text-sm font-medium shadow-inner" 
                       placeholder="Search for city, location...">
            </div>
            
            <!-- Advanced Filters -->
            <div class="flex gap-2 mt-3 overflow-x-auto hide-scrollbar pb-1">
                <div class="relative min-w-[30%] flex-1">
                    <label for="mobile_furnishing_type" class="sr-only">Furnishing Type</label>
                    <select id="mobile_furnishing_type" name="furnishing_type" aria-label="Select furnishing type" class="w-full pl-3 pr-6 py-2 bg-white border border-indigo-100 rounded-full text-[11px] font-bold text-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none shadow-sm shadow-indigo-100/50">
                        <option value="">Furnish</option>
                        @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                            <option value="{{ $option->id }}" {{ request('furnishing_type') == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-indigo-400 text-[9px]"></i>
                    </div>
                </div>

                <div class="relative min-w-[30%] flex-1">
                    <label for="mobile_tenant_type" class="sr-only">Tenant Type</label>
                    <select id="mobile_tenant_type" name="tenant_type" aria-label="Select tenant type" class="w-full pl-3 pr-6 py-2 bg-white border border-indigo-100 rounded-full text-[11px] font-bold text-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none shadow-sm shadow-indigo-100/50">
                        <option value="">Tenant</option>
                        @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                            <option value="{{ $option->id }}" {{ request('tenant_type') == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-indigo-400 text-[9px]"></i>
                    </div>
                </div>

                <div class="relative min-w-[30%] flex-1">
                    <label for="mobile_room_type" class="sr-only">Room Type</label>
                    <select id="mobile_room_type" name="room_type" aria-label="Select room type" class="w-full pl-3 pr-6 py-2 bg-white border border-indigo-100 rounded-full text-[11px] font-bold text-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none shadow-sm shadow-indigo-100/50">
                        <option value="">Type</option>
                        @foreach(App\Models\RoomOption::optionsFor('room_type') as $option)
                            <option value="{{ $option->id }}" {{ request('room_type') == $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-indigo-400 text-[9px]"></i>
                    </div>
                </div>
            </div>

            <!-- Price Filters (Collapsible/Inline) -->
            <div class="flex gap-2 mt-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-rupee-sign text-gray-400 text-xs"></i>
                    </div>
                    <input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="Min" class="pl-10 w-full py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none placeholder-slate-400 transition-all">
                </div>
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-rupee-sign text-gray-400 text-xs"></i>
                    </div>
                    <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="Max" class="pl-10 w-full py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none placeholder-slate-400 transition-all">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-xs font-black shadow-lg shadow-indigo-600/20 active:scale-95 transition-all uppercase tracking-wider">
                    GO
                </button>
            </div>
        </form>
    </div>
    
    <!-- Popular Cities (Dynamic) -->
    <div class="mt-4 px-4 pb-2">
        <p class="text-[10px] uppercase font-bold text-gray-500 tracking-wider mb-2">Popular Cities</p>
        <div class="flex gap-4 overflow-x-auto hide-scrollbar pb-2">
            <!-- All Cities -->
            <a href="{{ route('rooms.index') }}" class="flex flex-col items-center gap-1 min-w-[60px] group">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-active:scale-95 transition-transform border border-indigo-500">
                    <i class="fas fa-th-large text-white text-lg"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-700">All</span>
            </a>

            <!-- Near Me -->
            <button type="button" onclick="detectUserCity()" class="flex flex-col items-center gap-1 min-w-[60px] cursor-pointer group">
                <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-active:scale-95 transition-transform border border-emerald-500">
                    <i class="fas fa-location-arrow text-white text-lg transform -rotate-45"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-700">Near me</span>
            </button>
            
            @if(isset($popularCities) && $popularCities->count() > 0)
                @foreach($popularCities as $city)
                    <a href="{{ route('rooms.index', ['city' => $city->name]) }}" class="flex flex-col items-center gap-1 min-w-[60px] group">
                        <div class="w-12 h-12 bg-gray-200 rounded-2xl overflow-hidden relative shadow-md group-active:scale-95 transition-transform border border-gray-300">
                             {{-- Dynamic Gradient based on City Name Length/Hash for variety --}}
                             @php
                                $colors = [
                                    'from-indigo-600 to-indigo-800',
                                    'from-amber-500 to-orange-600',
                                    'from-purple-600 to-indigo-700',
                                    'from-slate-700 to-slate-900',
                                    'from-indigo-500 to-purple-600',
                                ];
                                $colorClass = $colors[crc32($city->name) % count($colors)];
                             @endphp
                             <div class="absolute inset-0 bg-gradient-to-br {{ $colorClass }} flex items-center justify-center text-white font-black text-xs uppercase shadow-inner" style="text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                                {{ substr($city->name, 0, 3) }}
                             </div>
                        </div>
                        <span class="text-[10px] font-medium text-gray-600 truncate max-w-[60px]">{{ $city->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</div>

<script>
async function detectUserCity() {
    const input = document.querySelector('input[name="city"]');
    const originalPlaceholder = input.placeholder;
    
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    input.value = "";
    input.placeholder = "Detecting location...";
    
    navigator.geolocation.getCurrentPosition(async (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        try {
            // Using Nominatim for free reverse geocoding
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`, {
                headers: {
                    'Accept-Language': 'en'
                }
            });
            const data = await response.json();
            
            // Try to get city, town, or village
            const city = data.address.city || data.address.town || data.address.village || data.address.county;
            
            if (city) {
                input.value = city;
                input.form.submit();
            } else {
                throw new Error("City not found");
            }
        } catch (error) {
            console.error("Error detecting city:", error);
            input.placeholder = "Location not found";
            setTimeout(() => { input.placeholder = originalPlaceholder; }, 2000);
        }
    }, (error) => {
        console.error("Geolocation error:", error);
        input.placeholder = "Access denied";
        setTimeout(() => { input.placeholder = originalPlaceholder; }, 2000);
    }, { enableHighAccuracy: true, timeout: 5000 });
}
</script>
