{{-- Floating Compare Dock Component --}}
<div id="compare-bar-dock" 
     class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-10px_30px_rgba(0,0,0,0.12)] transition-transform duration-300 ease-out transform translate-y-full hidden"
     role="region" 
     aria-label="Property comparison dock">
    <div class="container mx-auto px-4 max-w-7xl py-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            
            {{-- Left: Badge & Slots --}}
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <div class="shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-600 text-white shadow-xs">
                        <i class="fas fa-code-compare text-xs"></i>
                        <span id="compare-count-badge">0/3</span>
                    </span>
                    <span class="hidden md:block text-[11px] font-bold text-slate-500 mt-0.5">Compare</span>
                </div>

                <div id="compare-slots-container" class="flex items-center gap-2">
                    {{-- Dynamically populated --}}
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                <button type="button" 
                        onclick="clearCompareRooms()" 
                        class="px-3 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                    Clear All
                </button>

                <a id="compare-now-btn" 
                   href="{{ route('rooms.compare') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-xs font-black shadow-md hover:shadow-lg transition-all transform active:scale-95 cursor-pointer">
                    <i class="fas fa-scale-balanced text-sm"></i>
                    <span id="compare-btn-label">Compare Now</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const DEFAULT_COMPARE_IMG = "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='260' viewBox='0 0 400 260' fill='%23f1f5f9'%3E%3Crect width='400' height='260' fill='%23f1f5f9'/%3E%3Cg transform='translate(130, 50)'%3E%3Cpolygon points='70,10 10,65 130,65' fill='%23cbd5e1'/%3E%3Crect x='25' y='65' width='90' height='65' fill='%23e2e8f0'/%3E%3Crect x='55' y='85' width='30' height='45' fill='%2394a3b8' rx='2'/%3E%3Crect x='32' y='74' width='16' height='16' rx='3' fill='%2394a3b8'/%3E%3Crect x='92' y='74' width='16' height='16' rx='3' fill='%2394a3b8'/%3E%3C/g%3E%3Ctext x='200' y='210' font-family='sans-serif' font-size='14' font-weight='700' fill='%2364748b' text-anchor='middle'%3EProperty%3C/text%3E%3C/svg%3E";
window.DEFAULT_COMPARE_FALLBACK = DEFAULT_COMPARE_IMG;

function handleCompareClick(btn, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (!btn) return;
    const id = btn.getAttribute('data-compare-id');
    const title = btn.getAttribute('data-compare-title') || 'Property';
    const rent = btn.getAttribute('data-compare-rent') || 0;
    const image = btn.getAttribute('data-compare-image') || DEFAULT_COMPARE_IMG;
    const url = btn.getAttribute('data-compare-url') || `{{ url('/rooms') }}/${id}`;
    toggleCompareRoom(id, title, rent, image, url);
}
window.handleCompareClick = handleCompareClick;

function getCompareRooms() {
    try {
        const raw = localStorage.getItem('compare_rooms');
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

function saveCompareRooms(rooms) {
    try {
        localStorage.setItem('compare_rooms', JSON.stringify(rooms));
    } catch (e) {}
    renderCompareBar();
    syncCompareCheckboxes();
}

function toggleCompareRoom(id, title, rent, image, url) {
    id = Number(id);
    let rooms = getCompareRooms();
    const existingIndex = rooms.findIndex(r => Number(r.id) === id);

    if (existingIndex > -1) {
        rooms.splice(existingIndex, 1);
        saveCompareRooms(rooms);
        if (typeof toastr !== 'undefined') {
            toastr.info('Removed from comparison');
        }
    } else {
        if (rooms.length >= 3) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('You can compare up to 3 properties at a time');
            } else {
                alert('You can compare up to 3 properties at a time. Please remove one first.');
            }
            syncCompareCheckboxes();
            return false;
        }

        rooms.push({
            id: id,
            title: title || 'Property',
            rent: Number(rent || 0),
            image: image || DEFAULT_COMPARE_IMG,
            url: url || `{{ url('/rooms') }}/${id}`
        });
        saveCompareRooms(rooms);

        if (typeof toastr !== 'undefined') {
            toastr.success(`Added to compare (${rooms.length}/3)`);
        }
    }
    return true;
}

function removeCompareRoom(id) {
    id = Number(id);
    let rooms = getCompareRooms();
    rooms = rooms.filter(r => Number(r.id) !== id);
    saveCompareRooms(rooms);
}

function clearCompareRooms() {
    saveCompareRooms([]);
    if (typeof toastr !== 'undefined') {
        toastr.info('Comparison cleared');
    }
}

function syncCompareCheckboxes() {
    const rooms = getCompareRooms();
    const ids = rooms.map(r => Number(r.id));

    // Checkboxes in room listings
    document.querySelectorAll('[data-compare-id]').forEach(el => {
        const id = Number(el.getAttribute('data-compare-id'));
        const isSelected = ids.includes(id);

        if (el.tagName === 'INPUT' && el.type === 'checkbox') {
            el.checked = isSelected;
        }

        // Parent wrapper styling if any
        const label = el.closest('.compare-btn-wrapper');
        if (label) {
            if (isSelected) {
                label.classList.add('bg-indigo-50', 'border-indigo-300', 'text-indigo-600');
            } else {
                label.classList.remove('bg-indigo-50', 'border-indigo-300', 'text-indigo-600');
            }
        }
    });

    // Detail page compare button
    document.querySelectorAll('[data-detail-compare-btn]').forEach(btn => {
        const id = Number(btn.getAttribute('data-detail-compare-btn'));
        const isSelected = ids.includes(id);
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');

        if (isSelected) {
            btn.classList.add('bg-indigo-50', 'border-indigo-300', 'text-indigo-600');
            btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            if (text) text.textContent = 'Comparing';
            if (icon) icon.className = 'fas fa-check text-sm text-indigo-600';
        } else {
            btn.classList.remove('bg-indigo-50', 'border-indigo-300', 'text-indigo-600');
            btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
            if (text) text.textContent = 'Compare';
            if (icon) icon.className = 'fas fa-code-compare text-sm text-slate-400';
        }
    });
}

function renderCompareBar() {
    const dock = document.getElementById('compare-bar-dock');
    const container = document.getElementById('compare-slots-container');
    const badge = document.getElementById('compare-count-badge');
    const compareNowBtn = document.getElementById('compare-now-btn');
    const compareBtnLabel = document.getElementById('compare-btn-label');
    if (!dock || !container) return;

    const rooms = getCompareRooms();

    if (rooms.length === 0) {
        dock.classList.add('translate-y-full');
        setTimeout(() => {
            if (getCompareRooms().length === 0) dock.classList.add('hidden');
        }, 300);
        return;
    }

    dock.classList.remove('hidden');
    void dock.offsetWidth;
    dock.classList.remove('translate-y-full');

    if (badge) badge.textContent = `${rooms.length}/3`;

    let html = '';
    rooms.forEach(r => {
        let imgSrc = r.image;
        if (!imgSrc || imgSrc.includes('undefined') || imgSrc.includes('placehold.co')) {
            imgSrc = DEFAULT_COMPARE_IMG;
        }

        html += `
        <div class="relative flex items-center gap-2 p-1.5 pr-2.5 rounded-xl border border-slate-200 bg-white shadow-2xs shrink-0 max-w-[200px] group">
            <img src="${imgSrc}" alt="${(r.title || '').replace(/"/g, '&quot;')}" class="w-9 h-9 rounded-lg object-cover shrink-0" onerror="this.onerror=null; this.src=DEFAULT_COMPARE_IMG;">
            <div class="min-w-0 pr-4">
                <p class="text-[11px] font-black text-slate-900 truncate leading-tight">${r.title || 'Room'}</p>
                <p class="text-[10px] font-bold text-indigo-600 leading-tight">₹${Number(r.rent || 0).toLocaleString('en-IN')}<span class="text-[8px] font-normal text-slate-400">/mo</span></p>
            </div>
            <button type="button" onclick="removeCompareRoom(${r.id})" class="absolute top-1 right-1 w-4 h-4 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center text-[9px] transition cursor-pointer" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
        `;
    });

    for (let i = rooms.length; i < 3; i++) {
        html += `
        <div class="hidden sm:flex items-center justify-center w-28 h-12 rounded-xl border border-dashed border-slate-300/80 bg-slate-50/50 text-[10px] font-bold text-slate-400 shrink-0">
            + Add Property
        </div>
        `;
    }

    container.innerHTML = html;

    if (compareNowBtn) {
        const ids = rooms.map(r => r.id).join(',');
        compareNowBtn.href = `{{ route('rooms.compare') }}?ids=${ids}`;
        if (compareBtnLabel) {
            compareBtnLabel.textContent = `Compare Now (${rooms.length})`;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderCompareBar();
    syncCompareCheckboxes();
});
</script>
