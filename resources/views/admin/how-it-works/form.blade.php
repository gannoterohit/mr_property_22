@extends('layouts.admin')

@section('title', $item->exists ? 'Edit How It Works Item' : 'Create How It Works Item')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header>
        <a href="{{ route('admin.how-it-works.index') }}" class="text-xs font-bold admin-theme-text"><i class="fas fa-arrow-left mr-1"></i>How It Works</a>
        <h1 class="mt-3 text-2xl font-extrabold">{{ $item->exists ? 'Edit Item' : 'Create Item' }}</h1>
        <p class="text-sm text-slate-500">Use Font Awesome icon names like fa-search, fa-phone or fa-credit-card.</p>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $item->exists ? route('admin.how-it-works.items.update', $item) : route('admin.how-it-works.items.store') }}" method="POST" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @if($item->exists) @method('PUT') @endif
        <main class="rounded-2xl border bg-white p-5">
            <div><label class="text-xs font-bold">Title *</label><input name="title" value="{{ old('title', $item->title) }}" required maxlength="160" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-base font-bold"></div>
            <div class="mt-5"><label class="text-xs font-bold">Description</label><textarea name="description" rows="6" maxlength="700" class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old('description', $item->description) }}</textarea></div>
        </main>
        <aside class="space-y-4">
            <section class="rounded-2xl border bg-white p-5">
                <h2 class="text-sm font-extrabold">Display</h2>
                <div class="mt-4 space-y-4">
                    <div><label class="text-xs font-bold">Section</label><select name="group" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">@foreach($groups as $key => $label)<option value="{{ $key }}" @selected(old('group', $item->group)===$key)>{{ $label }}</option>@endforeach</select></div>
                    <div><label class="text-xs font-bold">Icon class</label><input name="icon" value="{{ old('icon', $item->icon ?: 'fa-circle-check') }}" maxlength="80" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm font-semibold"></div>
                    <div><label class="text-xs font-bold">Badge</label><input name="badge" value="{{ old('badge', $item->badge) }}" maxlength="40" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm" placeholder="Step 01"></div>
                    <div><label class="text-xs font-bold">Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
                    <label class="flex cursor-pointer items-center justify-between rounded-xl bg-slate-50 p-4">
                        <span><strong class="block text-xs">Show on page</strong><small class="text-[10px] text-slate-500">Turn off to hide temporarily</small></span>
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true)) class="h-5 w-5 rounded admin-theme-text">
                    </label>
                </div>
                <button class="mt-5 w-full rounded-xl admin-theme-bg py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>{{ $item->exists ? 'Save Item' : 'Create Item' }}</button>
                <a href="{{ route('admin.how-it-works.index') }}" class="mt-2 flex h-11 items-center justify-center rounded-xl border text-sm font-bold">Cancel</a>
            </section>
        </aside>
    </form>
</div>
@endsection
