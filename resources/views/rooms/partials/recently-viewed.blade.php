{{-- Recently Viewed Properties Component (Client-Side Storage) --}}
<section id="recently-viewed-section" class="hidden my-8 py-6 border-t border-slate-200">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i class="fas fa-clock-rotate-left text-xs"></i> Browsing History
                </span>
                <h2 class="mt-1.5 text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Recently Viewed Properties</h2>
                <p class="text-xs text-slate-500">Easily revisit rooms and flats you checked out earlier.</p>
            </div>
            <button type="button" onclick="clearRecentlyViewed()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-500 hover:text-rose-600 hover:border-rose-200 transition shadow-2xs cursor-pointer">
                <i class="fas fa-trash-can text-[11px]"></i> Clear History
            </button>
        </div>

        <div id="recently-viewed-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Injected dynamically by JavaScript --}}
        </div>
    </div>
</section>

<script>
const DEFAULT_ROOM_SVG = "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='260' viewBox='0 0 400 260' fill='%23f1f5f9'%3E%3Crect width='400' height='260' fill='%23f1f5f9'/%3E%3Cg transform='translate(130, 50)'%3E%3Cpolygon points='70,10 10,65 130,65' fill='%23cbd5e1'/%3E%3Crect x='25' y='65' width='90' height='65' fill='%23e2e8f0'/%3E%3Crect x='55' y='85' width='30' height='45' fill='%2394a3b8' rx='2'/%3E%3Crect x='32' y='74' width='16' height='16' rx='3' fill='%2394a3b8'/%3E%3Crect x='92' y='74' width='16' height='16' rx='3' fill='%2394a3b8'/%3E%3C/g%3E%3Ctext x='200' y='210' font-family='sans-serif' font-size='14' font-weight='700' fill='%2364748b' text-anchor='middle'%3EApnaNest Property%3C/text%3E%3C/svg%3E";

function resolveRoomImage(img) {
    if (!img || img === 'undefined' || img.includes('undefined') || img.includes('placehold.co')) {
        return DEFAULT_ROOM_SVG;
    }
    if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('data:')) {
        return img;
    }
    const clean = img.replace(/^\/+/, '');
    return '{{ url("/") }}/' + clean;
}

function renderRecentlyViewed(excludeId = null) {
    try {
        const raw = localStorage.getItem('recently_viewed_rooms');
        if (!raw) return;
        let rooms = JSON.parse(raw);
        if (!Array.isArray(rooms) || rooms.length === 0) return;

        // Auto-heal older corrupt localStorage entries
        let dirty = false;
        rooms = rooms.map(r => {
            if (r.image && (r.image.includes('placehold.co') || r.image === 'undefined')) {
                r.image = DEFAULT_ROOM_SVG;
                dirty = true;
            }
            return r;
        });
        if (dirty) {
            try { localStorage.setItem('recently_viewed_rooms', JSON.stringify(rooms)); } catch(e) {}
        }

        if (excludeId) {
            rooms = rooms.filter(r => String(r.id) !== String(excludeId));
        }

        if (rooms.length === 0) {
            const section = document.getElementById('recently-viewed-section');
            if (section) section.classList.add('hidden');
            return;
        }

        const container = document.getElementById('recently-viewed-grid');
        const section = document.getElementById('recently-viewed-section');
        if (!container || !section) return;

        container.innerHTML = rooms.slice(0, 4).map(r => {
            const imgUrl = resolveRoomImage(r.image);
            const title = (r.title || 'Rental Property').replace(/"/g, '&quot;');
            const url = r.url || ('{{ url("/rooms") }}/' + (r.slug || r.id));
            const rentFormatted = Number(r.rent || 0).toLocaleString('en-IN');
            const city = (r.city || 'India').replace(/"/g, '&quot;');
            const roomType = r.room_type ? r.room_type.replace(/"/g, '&quot;') : '';

            return `
            <a href="${url}" class="group block rounded-2xl border border-slate-200/90 bg-white overflow-hidden hover:shadow-xl hover:border-indigo-300 transition-all duration-300">
                <div class="relative h-44 w-full overflow-hidden bg-slate-100">
                    <img src="${imgUrl}" 
                         alt="${title}" 
                         class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" 
                         loading="lazy" 
                         onerror="this.onerror=null; this.src=DEFAULT_ROOM_SVG;">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                    <span class="absolute bottom-2.5 left-2.5 rounded-lg bg-slate-900/85 backdrop-blur-xs px-2.5 py-1 text-xs font-black text-white shadow-xs">
                        ₹${rentFormatted}<span class="text-[10px] font-normal text-slate-300">/mo</span>
                    </span>
                    ${roomType ? `<span class="absolute top-2.5 right-2.5 rounded-md bg-white/95 backdrop-blur-xs px-2 py-0.5 text-[10px] font-extrabold text-slate-800 shadow-2xs">${roomType}</span>` : ''}
                </div>
                <div class="p-3.5">
                    <h3 class="font-bold text-xs text-slate-900 line-clamp-1 group-hover:text-indigo-600 transition" title="${title}">${title}</h3>
                    <p class="mt-1 text-[11px] text-slate-500 flex items-center gap-1 truncate">
                        <i class="fas fa-location-dot text-indigo-500 text-[10px]"></i> ${city}
                    </p>
                </div>
            </a>
            `;
        }).join('');

        section.classList.remove('hidden');
    } catch (e) {
        console.warn('Recently viewed render error:', e);
    }
}

function clearRecentlyViewed() {
    try {
        localStorage.removeItem('recently_viewed_rooms');
        const section = document.getElementById('recently-viewed-section');
        if (section) section.classList.add('hidden');
        if (typeof toastr !== 'undefined') {
            toastr.info('Browsing history cleared');
        }
    } catch (e) {}
}
</script>
