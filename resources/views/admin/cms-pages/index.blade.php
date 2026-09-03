@extends('layouts.admin')

@section('title', 'CMS Pages')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Content management</p>
            <h1 class="mt-1 text-2xl font-extrabold">CMS Pages</h1>
            <p class="text-sm text-slate-500">Manage static website pages without adding sidebar clutter.</p>
        </div>
        <a href="{{ route('admin.cms-pages.create') }}" class="inline-flex items-center gap-2 rounded-xl admin-theme-bg px-4 py-3 text-xs font-bold text-white">
            <i class="fas fa-plus"></i>Add Page
        </a>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="flex flex-wrap items-end gap-2 rounded-2xl border bg-white p-4">
        <div>
            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Search</label>
            <input name="search" value="{{ request('search') }}" placeholder="Title or slug" class="h-10 rounded-lg text-xs">
        </div>
        <div>
            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Status</label>
            <select name="status" class="h-10 rounded-lg text-xs">
                <option value="">All</option>
                <option value="published" @selected(request('status')==='published')>Published</option>
                <option value="draft" @selected(request('status')==='draft')>Draft</option>
            </select>
        </div>
        <button class="h-10 rounded-lg bg-slate-900 px-4 text-xs font-bold text-white">Apply</button>
        <a href="{{ route('admin.cms-pages.index') }}" class="flex h-10 items-center rounded-lg border px-3 text-xs font-bold">Reset</a>
    </form>

    <div class="overflow-hidden rounded-2xl border bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[1040px] w-full text-left">
                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                    <tr><th class="p-4">Page</th><th class="p-4">Template</th><th class="p-4">Status</th><th class="p-4 text-center">Publish</th><th class="p-4">Updated</th><th class="p-4 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($pages as $page)
                        <tr>
                            <td class="p-4">
                                <p class="text-sm font-extrabold text-slate-900">{{ $page->title }}</p>
                                <p class="text-xs text-slate-400">/{{ $page->slug }}</p>
                            </td>
                            <td class="p-4"><span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">{{ ucfirst($page->template) }}</span></td>
                            <td class="p-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-lg px-2.5 py-1 text-[10px] font-extrabold {{ $page->isPublished() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $page->isPublished() ? 'Published' : 'Draft' }}</span>
                                    @if($page->is_system)
                                        <span class="rounded-lg admin-theme-soft px-2.5 py-1 text-[10px] font-bold admin-theme-text">System</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <x-admin.status-toggle
                                    :active="$page->isPublished()"
                                    active-label="Published"
                                    inactive-label="Draft"
                                    :action="route('admin.cms-pages.toggle-status', $page)"
                                    :data-label="$page->title"
                                />
                            </td>
                            <td class="p-4 text-xs text-slate-500">{{ $page->updated_at?->format('d M Y, h:i A') }}</td>
                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    @if($page->isPublished())
                                        <x-admin.action-icon variant="view" :href="$page->public_url" target="_blank" title="View public page" />
                                    @else
                                        <span class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-bold text-slate-300" title="Draft pages are hidden from the public website"><i class="fas fa-eye-slash"></i></span>
                                    @endif
                                    <x-admin.action-icon variant="edit" :href="route('admin.cms-pages.edit', $page)" />
                                    @unless($page->is_system)
                                        <form method="POST" action="{{ route('admin.cms-pages.destroy', $page) }}" class="admin-confirm" data-confirm-title="Delete {{ $page->title }}?" data-confirm-text="This CMS page will be permanently removed." data-confirm-button="Yes, delete page">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-14 text-center"><i class="fas fa-file-circle-plus text-3xl admin-theme-text"></i><p class="mt-3 text-sm font-extrabold text-slate-800">No CMS pages yet</p><p class="mt-1 text-xs text-slate-500">Create the first page to publish content on your website.</p><a href="{{ route('admin.cms-pages.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl admin-theme-bg px-4 py-2.5 text-xs font-bold text-white"><i class="fas fa-plus"></i>Create page</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pages->hasPages())
            <div class="border-t p-4">{{ $pages->links() }}</div>
        @endif
    </div>
</div>
@endsection
