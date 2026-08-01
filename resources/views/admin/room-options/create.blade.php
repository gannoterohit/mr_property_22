@extends('layouts.admin')

@section('title', 'Add Room Option')

@section('admin-content')
<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.room-options.index') }}" class="w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 admin-theme-hover-text  flex items-center justify-center transition"><i class="fas fa-arrow-left text-xs"></i></a>
        <div><h2 class="text-xl font-bold text-slate-900">Add new room option</h2><p class="text-xs text-slate-500 mt-0.5">Create a value for room forms, filters and API.</p></div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc ml-5 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('admin.room-options.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        @csrf
        <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">Option details</h3><p class="text-xs text-slate-500 mt-1">All fields are required except sort order.</p></div>
        <div class="p-5 space-y-5">
            <div><label class="block text-xs font-bold text-slate-600 mb-1.5">Option group</label><select name="group" required class="w-full rounded-lg text-sm bg-white">@foreach($groups as $value => $label)<option value="{{ $value }}" {{ old('group') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-bold text-slate-600 mb-1.5">Display label</label><input type="text" name="label" value="{{ old('label') }}" required class="w-full rounded-lg text-sm" placeholder="e.g. Studio Apartment"><p class="text-[11px] text-slate-400 mt-1">This text is visible to owners and customers.</p></div>
            <div><label class="block text-xs font-bold text-slate-600 mb-1.5">System key</label><input type="text" name="key" value="{{ old('key') }}" required class="w-full rounded-lg text-sm font-mono" placeholder="studio_apartment"><p class="text-[11px] text-slate-400 mt-1">Use lowercase letters, numbers, hyphens or underscores.</p></div>
            <div><label class="block text-xs font-bold text-slate-600 mb-1.5">Sort order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full rounded-lg text-sm"></div>
            <div><label class="block text-xs font-bold text-slate-600 mb-1.5">Status</label><select name="is_active" required class="w-full rounded-lg text-sm bg-white"><option value="1" @selected(old('is_active', '1') === '1')>Active</option><option value="0" @selected(old('is_active') === '0')>Inactive</option></select><p class="text-[11px] text-slate-400 mt-1">Only active options appear in room forms and API.</p></div>
        </div>
        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3"><a href="{{ route('admin.room-options.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold hover:bg-slate-100 transition">Cancel</a><button type="submit" class="px-5 py-2.5 rounded-lg admin-theme-bg text-xs font-bold transition"><i class="fas fa-plus mr-1.5"></i>Add option</button></div>
    </form>
</div>
@endsection
