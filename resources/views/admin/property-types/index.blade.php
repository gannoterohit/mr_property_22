@extends('layouts.admin')

@section('title', 'Property Types')

@section('admin-content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Property Types</h2>
            <p class="text-sm text-slate-500">Manage rental property classes such as Room, Flat, Shop and Office.</p>
        </div>
        <a href="{{ route('admin.property-types.create') }}" class="px-4 py-2 rounded-xl admin-theme-bg text-xs font-bold text-white">Add Property Type</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Slug</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($types as $type)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-bold text-slate-800">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $type->slug }}</td>
                        <td class="px-4 py-3">
                            <x-admin.status-toggle :active="$type->status" :action="route('admin.property-types.toggle-status', $type)" :data-label="$type->name" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <x-admin.action-icon variant="edit" :href="route('admin.property-types.edit', $type)" />
                                <form method="POST" action="{{ route('admin.property-types.destroy', $type) }}" class="admin-confirm" data-confirm-title="Delete {{ $type->name }}?" data-confirm-text="Delete only if this property type is not used by categories or listings. Used records should be deactivated." data-confirm-button="Yes, delete">
                                    @csrf
                                    @method('DELETE')
                                    <x-admin.action-icon variant="delete" type="submit" />
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
