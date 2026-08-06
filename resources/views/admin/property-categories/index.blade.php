@extends('layouts.admin')

@section('title', 'Property Categories')

@section('admin-content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Property Categories</h2>
            <p class="text-sm text-slate-500">Manage dynamic category values such as 1BHK, 2BHK, Furnished Office, Road Facing.</p>
        </div>
        <a href="{{ route('admin.property-categories.create') }}" class="px-4 py-2 rounded-xl admin-theme-bg text-xs font-bold text-white">Add Category</a>
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
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $category->status ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.property-categories.edit', $category) }}" class="px-3 py-2 rounded-lg bg-slate-100 text-xs font-bold">Edit</a>
                                <form action="{{ route('admin.property-categories.toggle-status', $category) }}" method="POST">@csrf @method('PATCH')<button class="px-3 py-2 rounded-lg {{ $category->status ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} text-xs font-bold">{{ $category->status ? 'Deactivate' : 'Activate' }}</button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
