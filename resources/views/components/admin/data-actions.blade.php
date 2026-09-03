@props([
    'dataset',
    'importable' => false,
    'label' => 'Data actions',
])

<div class="admin-data-actions relative inline-flex" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button type="button" class="admin-btn" @click="open = !open" aria-haspopup="true" :aria-expanded="open ? 'true' : 'false'">
        <i class="fas fa-file-export"></i>
        {{ $label }}
        <i class="fas fa-chevron-down text-[9px]"></i>
    </button>
    <div x-cloak x-show="open" x-transition class="absolute right-0 top-full z-40 mt-2 w-72 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
        <a href="{{ route('admin.data-tools.export', $dataset) }}" class="admin-data-action-item">
            <i class="fas fa-file-excel text-emerald-600"></i>
            <span><strong>Export Excel</strong><small>Download current table data</small></span>
        </a>
        <a href="{{ route('admin.data-tools.report', $dataset) }}" target="_blank" class="admin-data-action-item">
            <i class="fas fa-file-pdf text-red-600"></i>
            <span><strong>PDF Download</strong><small>Open print-ready report</small></span>
        </a>
        @if($importable)
            <a href="{{ route('admin.data-tools.template', $dataset) }}" class="admin-data-action-item">
                <i class="fas fa-download text-slate-600"></i>
                <span><strong>Excel Template</strong><small>Use this format for import</small></span>
            </a>
            <form method="POST" action="{{ route('admin.data-tools.import', $dataset) }}" enctype="multipart/form-data" class="border-t border-slate-100 p-3">
                @csrf
                <label class="mb-2 block text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Import Excel / CSV</label>
                <input type="file" name="file" accept=".xlsx,.csv,text/csv" required class="mb-2 w-full rounded-lg text-[11px]">
                <button type="submit" class="admin-btn admin-btn-primary w-full"><i class="fas fa-upload"></i>Import</button>
            </form>
        @endif
    </div>
</div>
