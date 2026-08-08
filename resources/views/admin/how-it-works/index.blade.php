@extends('layouts.admin')

@section('title', 'How It Works')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Landing content</p>
            <h1 class="mt-1 text-2xl font-extrabold">How It Works</h1>
            <p class="text-sm text-slate-500">Manage this page with structured fields instead of a free-form editor.</p>
        </div>
        <a href="{{ route('pages.how-it-works') }}" target="_blank" class="rounded-xl border bg-white px-4 py-3 text-xs font-bold text-slate-700"><i class="fas fa-up-right-from-square mr-2"></i>Preview</a>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.how-it-works.settings') }}" class="rounded-2xl border bg-white p-5">
        @csrf @method('PUT')
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach([
                'hiw_hero_eyebrow' => 'Hero eyebrow',
                'hiw_hero_title' => 'Hero title',
                'hiw_hero_highlight' => 'Hero highlighted line',
                'hiw_primary_button_label' => 'Primary button',
                'hiw_secondary_button_label' => 'Secondary button',
                'hiw_seeker_eyebrow' => 'Seeker eyebrow',
                'hiw_seeker_title' => 'Seeker title',
                'hiw_owner_eyebrow' => 'Owner eyebrow',
                'hiw_owner_title' => 'Owner title',
                'hiw_owner_button_label' => 'Owner button',
                'hiw_safety_title' => 'Safety title',
                'hiw_safety_button_label' => 'Safety button',
            ] as $key => $label)
                <div><label class="text-xs font-bold">{{ $label }}</label><input name="{{ $key }}" value="{{ old($key, \App\Models\Setting::get($key)) }}" required class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
            @endforeach
            @foreach([
                'hiw_hero_description' => 'Hero description',
                'hiw_seeker_description' => 'Seeker description',
                'hiw_owner_description' => 'Owner description',
                'hiw_safety_description' => 'Safety description',
            ] as $key => $label)
                <div class="lg:col-span-2"><label class="text-xs font-bold">{{ $label }}</label><textarea name="{{ $key }}" rows="3" required class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old($key, \App\Models\Setting::get($key)) }}</textarea></div>
            @endforeach
        </div>
        <button class="mt-5 rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>Save Page Text</button>
    </form>

    @foreach($groups as $groupKey => $groupLabel)
        <section class="overflow-hidden rounded-2xl border bg-white">
            <div class="flex items-center justify-between border-b bg-slate-50 px-5 py-4">
                <div><h2 class="text-sm font-extrabold text-slate-950">{{ $groupLabel }}</h2><p class="text-xs text-slate-500">Add, edit, reorder and hide items for this section.</p></div>
                <a href="{{ route('admin.how-it-works.items.create', ['group' => $groupKey]) }}" class="rounded-xl admin-theme-bg px-4 py-2.5 text-xs font-bold text-white"><i class="fas fa-plus mr-2"></i>Add</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left">
                    <thead class="bg-white text-[10px] font-extrabold uppercase tracking-wide text-slate-400"><tr><th class="p-4">Item</th><th class="p-4">Badge</th><th class="p-4">Order</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y">
                        @forelse(($items[$groupKey] ?? collect()) as $item)
                            <tr>
                                <td class="p-4"><div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl admin-theme-soft admin-theme-text"><i class="fas {{ $item->icon }}"></i></span><div><p class="text-sm font-extrabold">{{ $item->title }}</p><p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $item->description }}</p></div></div></td>
                                <td class="p-4 text-xs font-bold text-slate-600">{{ $item->badge ?: '-' }}</td>
                                <td class="p-4 text-xs font-bold text-slate-600">{{ $item->sort_order }}</td>
                                <td class="p-4"><x-admin.status-toggle :active="$item->is_active" :action="route('admin.how-it-works.items.toggle-status', $item)" :data-label="$item->title" /></td>
                                <td class="p-4"><div class="flex justify-end gap-2"><x-admin.action-icon variant="edit" :href="route('admin.how-it-works.items.edit', $item)" /><form method="POST" action="{{ route('admin.how-it-works.items.destroy', $item) }}" class="admin-confirm" data-confirm-title="Delete {{ $item->title }}?" data-confirm-text="This item will be permanently removed." data-confirm-button="Yes, delete item">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-sm text-slate-500">No items yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>
@endsection
