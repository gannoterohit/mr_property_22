@extends('layouts.admin')

@section('title', 'Property Categories')

@section('admin-content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Property Categories</h2>
            <p class="text-sm text-slate-500">Manage dynamic category values such as 1BHK, 2BHK, Furnished Office, Road Facing.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="property-categories" importable />
            <a href="{{ route('admin.property-categories.create') }}" class="px-4 py-2 rounded-xl admin-theme-bg text-xs font-bold text-white">Add Category</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">Property Type</th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Slug</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-bold text-slate-700">{{ $category->propertyType?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-bold text-slate-800">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->slug }}</td>
                        <td class="px-4 py-3">
                            <x-admin.status-toggle :active="$category->status" :action="route('admin.property-categories.toggle-status', $category)" :data-label="$category->name" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <x-admin.action-icon variant="edit" :href="route('admin.property-categories.edit', $category)" />
                                <form method="POST" action="{{ route('admin.property-categories.destroy', $category) }}" class="admin-confirm" data-confirm-title="Delete {{ $category->name }}?" data-confirm-text="Delete only if this category is not used by any listing. Used records should be deactivated." data-confirm-button="Yes, delete">
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
