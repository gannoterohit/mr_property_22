@extends('layouts.admin')

@section('title', $feature->exists ? 'Edit Why Choose Us' : 'Create Why Choose Us')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header>
        <a href="{{ route('admin.home-features.index') }}" class="text-xs font-bold admin-theme-text"><i class="fas fa-arrow-left mr-1"></i>All Why Choose Us items</a>
        <h1 class="mt-3 text-2xl font-extrabold">{{ $feature->exists ? 'Edit Why Choose Us Item' : 'Create Why Choose Us Item' }}</h1>
        <p class="text-sm text-slate-500">Use Font Awesome icon names like fa-shield-halved, fa-location-dot or fa-phone-volume.</p>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $feature->exists ? route('admin.home-features.update', $feature) : route('admin.home-features.store') }}" method="POST" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @if($feature->exists) @method('PUT') @endif
        <main class="rounded-2xl border bg-white p-5">
            <div><label class="text-xs font-bold">Title *</label><input name="title" value="{{ old('title', $feature->title) }}" required maxlength="120" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-base font-bold"></div>
            <div class="mt-5"><label class="text-xs font-bold">Description</label><textarea name="description" rows="5" maxlength="500" class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old('description', $feature->description) }}</textarea></div>
        </main>
        <aside class="space-y-4">
            <section class="rounded-2xl border bg-white p-5">
                <h2 class="text-sm font-extrabold">Display</h2>
                <div class="mt-4 space-y-4">
                    <div><label class="text-xs font-bold">Icon class</label><input name="icon" value="{{ old('icon', $feature->icon ?: 'fa-circle-check') }}" maxlength="80" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm font-semibold"></div>
                    <div><label class="text-xs font-bold">Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $feature->sort_order ?? 0) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
                    <label class="flex cursor-pointer items-center justify-between rounded-xl bg-slate-50 p-4">
                        <span><strong class="block text-xs">Show on landing</strong><small class="text-[10px] text-slate-500">Turn off to hide temporarily</small></span>
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $feature->is_active ?? true)) class="h-5 w-5 rounded admin-theme-text">
                    </label>
                </div>
                <button class="mt-5 w-full rounded-xl admin-theme-bg py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>{{ $feature->exists ? 'Save Item' : 'Create Item' }}</button>
                <a href="{{ route('admin.home-features.index') }}" class="mt-2 flex h-11 items-center justify-center rounded-xl border text-sm font-bold">Cancel</a>
            </section>
        </aside>
    </form>
</div>
@endsection
