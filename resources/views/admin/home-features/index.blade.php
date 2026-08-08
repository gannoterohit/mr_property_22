@extends('layouts.admin')

@section('title', 'Why Choose Us')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Landing content</p>
            <h1 class="mt-1 text-2xl font-extrabold">Why Choose Us</h1>
            <p class="text-sm text-slate-500">Manage the benefit cards shown on the landing page.</p>
        </div>
        <a href="{{ route('admin.home-features.create') }}" class="inline-flex items-center gap-2 rounded-xl admin-theme-bg px-4 py-3 text-xs font-bold text-white">
            <i class="fas fa-plus"></i>Add Item
        </a>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="flex flex-wrap items-end gap-2 rounded-2xl border bg-white p-4">
        <div>
            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Search</label>
            <input name="search" value="{{ request('search') }}" placeholder="Title" class="h-10 rounded-lg text-xs">
        </div>
        <button class="h-10 rounded-lg bg-slate-900 px-4 text-xs font-bold text-white">Apply</button>
        <a href="{{ route('admin.home-features.index') }}" class="flex h-10 items-center rounded-lg border px-3 text-xs font-bold">Reset</a>
    </form>

    <div class="overflow-hidden rounded-2xl border bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-left">
                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                    <tr><th class="p-4">Item</th><th class="p-4">Icon</th><th class="p-4">Order</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($features as $feature)
                        <tr>
                            <td class="p-4">
                                <p class="text-sm font-extrabold text-slate-900">{{ $feature->title }}</p>
                                <p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $feature->description }}</p>
                            </td>
                            <td class="p-4"><span class="inline-flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft admin-theme-text"><i class="fas {{ $feature->icon }}"></i></span></td>
                            <td class="p-4 text-xs font-bold text-slate-600">{{ $feature->sort_order }}</td>
                            <td class="p-4">
                                <x-admin.status-toggle :active="$feature->is_active" :action="route('admin.home-features.toggle-status', $feature)" :data-label="$feature->title" />
                            </td>
                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    <x-admin.action-icon variant="edit" :href="route('admin.home-features.edit', $feature)" />
                                    <form method="POST" action="{{ route('admin.home-features.destroy', $feature) }}" class="admin-confirm" data-confirm-title="Delete {{ $feature->title }}?" data-confirm-text="This landing page item will be permanently removed." data-confirm-button="Yes, delete item">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-12 text-center text-sm text-slate-500">No Why Choose Us items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($features->hasPages())
            <div class="border-t p-4">{{ $features->links() }}</div>
        @endif
    </div>
</div>
@endsection
