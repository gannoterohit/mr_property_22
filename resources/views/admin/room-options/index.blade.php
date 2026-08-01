@extends('layouts.admin')

@section('title', 'Room Options')

@push('styles')
<style>
    .room-options-page .compact-table th { padding-top:.65rem !important; padding-bottom:.65rem !important; }
    .room-options-page .compact-table td { padding-top:.5rem !important; padding-bottom:.5rem !important; }
    .room-options-page .compact-control { height:36px; padding-top:.4rem !important; padding-bottom:.4rem !important; }
</style>
@endpush

@section('admin-content')
@php
    $allOptions = $options->flatten(1)->sortBy([['group', 'asc'], ['sort_order', 'asc'], ['label', 'asc']]);
    $groupMeta = [
        'room_type' => ['label' => 'Room Type', 'icon' => 'fa-door-open', 'badge' => 'admin-theme-soft border-slate-200'],
        'furnishing_type' => ['label' => 'Furnishing', 'icon' => 'fa-couch', 'badge' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'tenant_type' => ['label' => 'Preferred Tenant', 'icon' => 'fa-user-friends', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
        'amenity' => ['label' => 'Amenities', 'icon' => 'fa-bell-concierge', 'badge' => 'bg-sky-50 text-sky-700 border-sky-100'],
    ];
@endphp

<div class="room-options-page space-y-4" x-data="{ filter: 'all' }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Room option manager</h2>
            <p class="text-sm text-slate-500 mt-1">Manage room type, furnishing, tenant and amenity options from one place.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-600">{{ $allOptions->where('is_active', true)->count() }} active</span>
            </div>
            <a href="{{ route('admin.room-options.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl admin-theme-bg text-xs font-bold shadow-sm transition"><i class="fas fa-plus"></i>Add new option</a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i>Please check the form</p>
            <ul class="list-disc ml-5 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900">Configured options</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Edit values directly inside the table.</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 rounded-xl">
                <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition">All <span class="ml-1 text-[9px] text-slate-400">{{ $allOptions->count() }}</span></button>
                @foreach($groupMeta as $key => $meta)
                    <button type="button" @click="filter = '{{ $key }}'" :class="filter === '{{ $key }}' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition">{{ $meta['label'] }} <span class="ml-1 text-[9px] text-slate-400">{{ $allOptions->where('group', $key)->count() }}</span></button>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="compact-table w-full min-w-[980px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-5 text-left w-[190px]">Group</th>
                        <th class="px-4 text-left">Display label</th>
                        <th class="px-4 text-left">System key</th>
                        <th class="px-4 text-center w-[100px]">Order</th>
                        <th class="px-4 text-center w-[110px]">Status</th>
                        <th class="px-5 text-right w-[190px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($allOptions as $option)
                        @php $meta = $groupMeta[$option->group] ?? ['label' => $option->group, 'icon' => 'fa-list', 'badge' => 'bg-slate-100 text-slate-600 border-slate-200']; @endphp
                        <tr x-show="filter === 'all' || filter === '{{ $option->group }}'" class="hover:bg-slate-50/70 {{ !$option->is_active ? 'bg-slate-50 opacity-70' : '' }} transition">
                            <td class="px-5">
                                <span class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg border text-[11px] font-bold {{ $meta['badge'] }}"><i class="fas {{ $meta['icon'] }} w-3 text-center"></i>{{ $meta['label'] }}</span>
                            </td>
                            <td class="px-4"><span class="text-sm font-bold text-slate-800">{{ $option->label }}</span></td>
                            <td class="px-4"><code class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs">{{ $option->key }}</code></td>
                            <td class="px-4 text-center"><span class="inline-flex min-w-7 justify-center px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-bold">{{ $option->sort_order }}</span></td>
                            <td class="px-4 text-center">
                                <form action="{{ route('admin.room-options.toggle-status', $option) }}" method="POST" class="toggle-room-option inline-flex" data-label="{{ $option->label }}" data-active="{{ $option->is_active ? '1' : '0' }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex h-8 w-[104px] items-center justify-start gap-2 rounded-full border px-2 text-[10px] font-bold transition-colors {{ $option->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}" title="Click to {{ $option->is_active ? 'deactivate' : 'activate' }}">
                                        <span class="relative inline-flex h-4 w-7 shrink-0 rounded-full transition-colors {{ $option->is_active ? 'bg-emerald-500' : 'bg-red-500' }}">
                                            <span class="absolute left-0.5 top-0.5 h-3 w-3 rounded-full bg-white shadow-sm transition-transform duration-200 {{ $option->is_active ? 'translate-x-3' : 'translate-x-0' }}"></span>
                                        </span>
                                        <span class="inline-block w-12 text-left">{{ $option->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-5">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.room-options.edit', $option) }}" class="h-8 px-2.5 inline-flex items-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-800 hover:text-white text-[10px] font-bold transition"><i class="fas fa-pen mr-1"></i>Edit</a>
                                    <form action="{{ route('admin.room-options.destroy', $option) }}" method="POST" class="delete-room-option" data-label="{{ $option->label }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 items-center rounded-lg border border-red-100 bg-white px-2.5 text-[10px] font-bold text-red-600 transition hover:bg-red-600 hover:text-white">
                                            <i class="fas fa-trash-can mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-400"><i class="fas fa-inbox block text-3xl mb-3 text-slate-300"></i>No options have been added.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-room-option').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const isActive = form.dataset.active === '1';
        const action = isActive ? 'Deactivate' : 'Activate';
        const result = await Swal.fire({
            title: `${action} ${form.dataset.label}?`,
            text: isActive
                ? 'This option will be hidden from new room forms, filters and API.'
                : 'This option will become available in room forms, filters and API.',
            icon: isActive ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action.toLowerCase()}`,
            cancelButtonText: 'Cancel',
            confirmButtonColor: isActive ? '#dc2626' : '#059669',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});

document.querySelectorAll('.delete-room-option').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = await Swal.fire({
            title: `Delete ${form.dataset.label}?`,
            text: 'This permanently removes the option. Options used by existing rooms cannot be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete permanently',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endpush
