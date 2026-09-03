@extends('layouts.admin')
@section('title', 'Data Management')

@section('admin-content')
<div class="data-transfer-page space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest admin-theme-text">Backup, migration and reports</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Data Management</h1>
            <p class="mt-1 text-sm text-slate-500">Export business data, print PDF-ready reports and safely import setup/master data.</p>
        </div>
        <x-admin.button :href="route('admin.data-maintenance.index')" icon="fa-database">Data Maintenance</x-admin.button>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif
    @if(session('import_errors'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
            <p class="mb-2 font-extrabold">Skipped row details</p>
            @foreach(session('import_errors') as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-3 md:grid-cols-5">
        @foreach([
            ['Properties', $stats['rooms'], 'fa-building'],
            ['Users', $stats['users'], 'fa-users'],
            ['Owners', $stats['owners'], 'fa-user-tie'],
            ['Payments', $stats['payments'], 'fa-credit-card'],
            ['Master records', $stats['masters'], 'fa-sliders'],
        ] as [$label, $value, $icon])
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <span class="admin-theme-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas {{ $icon }}"></i></span>
                <p class="mt-3 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-extrabold text-slate-950">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="text-sm font-extrabold text-slate-900">Export and PDF Reports</h2>
            <p class="text-xs text-slate-500">CSV files are best for backup/import checks. PDF report opens a print-ready page; choose Save as PDF in the browser.</p>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($exports as $key => $item)
                <article class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">{{ $item['label'] }}</h3>
                            <p class="mt-1 text-[11px] text-slate-500">{{ $item['importable'] ? 'Export, report and import supported.' : 'Export and report only for data safety.' }}</p>
                        </div>
                        <span class="admin-theme-soft rounded-lg px-2.5 py-1 text-[10px] font-extrabold">{{ $item['importable'] ? 'Master' : 'Report' }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-admin.button :href="route('admin.data-tools.export', $key)" icon="fa-file-csv">Export CSV</x-admin.button>
                        @if(isset($reports[$key]))
                            <x-admin.button :href="route('admin.data-tools.report', $key)" icon="fa-file-pdf" target="_blank">PDF / Print</x-admin.button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="text-sm font-extrabold text-slate-900">Safe Master Data Import</h2>
            <p class="text-xs text-slate-500">Import updates existing rows by slug/key/name and creates missing rows. Rooms, users and payments are intentionally export-only.</p>
        </div>
        <div class="grid gap-3 p-4 lg:grid-cols-2">
            @foreach($imports as $key => $item)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">{{ $item['label'] }}</h3>
                            <p class="mt-1 text-[11px] text-slate-500">Columns: {{ implode(', ', $item['columns']) }}</p>
                        </div>
                        <x-admin.button :href="route('admin.data-tools.template', $key)" icon="fa-download">Template</x-admin.button>
                    </div>
                    <form method="POST" action="{{ route('admin.data-tools.import', $key) }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                        @csrf
                        <input type="file" name="file" accept=".csv,text/csv" required class="min-h-10 flex-1 rounded-xl text-xs">
                        <x-admin.button type="submit" variant="primary" icon="fa-upload">Import</x-admin.button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .data-transfer-page input[type=file]{padding:.55rem .75rem!important;background:#fff}
    .data-transfer-page article{transition:border-color .15s ease,background-color .15s ease}
    .data-transfer-page article:hover{border-color:rgba(var(--admin-primary-rgb),.28);background:rgba(var(--admin-primary-rgb),.03)}
</style>
@endpush
