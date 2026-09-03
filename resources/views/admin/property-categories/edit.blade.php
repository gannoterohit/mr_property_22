@extends('layouts.admin')

@section('title', 'Edit Property Category')

@section('admin-content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200 p-5">
    <h2 class="text-xl font-bold text-slate-900">Edit Property Category</h2>
    <form action="{{ route('admin.property-categories.update', $propertyCategory) }}" method="POST" class="mt-4 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Property Type</label>
            <select name="property_type_id" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
                @foreach($propertyTypes as $type)
                    <option value="{{ $type->id }}" @selected($propertyCategory->property_type_id == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $propertyCategory->name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $propertyCategory->slug) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Status</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                <option value="1" @selected((bool) old('status', $propertyCategory->status))>Active</option>
                <option value="0" @selected(!(bool) old('status', $propertyCategory->status))>Inactive</option>
            </select>
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.property-categories.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-xs font-bold">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-xl admin-theme-bg text-xs font-bold text-white">Update</button>
        </div>
    </form>
</div>
@endsection
