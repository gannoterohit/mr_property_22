@extends('layouts.admin')

@section('title', $page->exists ? 'Edit CMS Page' : 'Create CMS Page')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-form.css') }}">

@endpush

@section('admin-content')
@php
    $currentTemplate = old('template', $page->template ?: 'default');
@endphp
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.cms-pages.index') }}" class="text-xs font-bold admin-theme-text"><i class="fas fa-arrow-left mr-1"></i>All CMS pages</a>
            <h1 class="mt-3 text-2xl font-extrabold">{{ $page->exists ? 'Edit CMS Page' : 'Create CMS Page' }}</h1>
            <p class="text-sm text-slate-500">Create reusable static pages with SEO and publishing controls.</p>
        </div>
        @if($page->exists && $page->isPublished())
            <a href="{{ $page->public_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border admin-theme-soft px-4 py-3 text-xs font-bold"><i class="fas fa-eye"></i>Preview</a>
        @elseif($page->exists)
            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold text-slate-400"><i class="fas fa-eye-slash"></i>Draft hidden</span>
        @endif
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $page->exists ? route('admin.cms-pages.update', $page) : route('admin.cms-pages.store') }}" method="POST" class="cms-editor-grid">
        @csrf
        @if($page->exists) @method('PUT') @endif
        <main class="min-w-0 space-y-5">
            <section class="rounded-2xl border bg-white p-5">
                <div><label class="text-xs font-bold">Page title *</label><input name="title" value="{{ old('title', $page->title) }}" required maxlength="255" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-base font-bold"></div>
                <div class="mt-5"><label class="text-xs font-bold">Slug *</label><input name="slug" value="{{ old('slug', $page->slug) }}" required maxlength="255" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm font-semibold" placeholder="refund-policy"></div>
                <div class="mt-5"><label class="text-xs font-bold">Content</label><textarea id="cmsContent" name="content" rows="18" class="mt-2 w-full rounded-xl border-slate-200">{{ old('content', $page->content) }}</textarea></div>
            </section>
            <section class="rounded-2xl border bg-white p-5">
                <h2 class="text-sm font-extrabold">SEO</h2>
                <div class="mt-5 space-y-4">
                    <div><label class="text-xs font-bold">SEO title</label><input name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" maxlength="255" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="text-xs font-bold">Meta description</label><textarea name="meta_description" rows="3" maxlength="500" class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                </div>
            </section>
        </main>
        <aside class="cms-publish space-y-4">
            <section class="rounded-2xl border bg-white p-5">
                <h2 class="text-sm font-extrabold">Publishing</h2>
                <div class="mt-4 space-y-4">
                    <div><label class="text-xs font-bold">Status</label><select name="status" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"><option value="published" @selected(old('status',$page->status)==='published')>Published</option><option value="draft" @selected(old('status',$page->status)==='draft')>Draft</option></select></div>
                    <div><label class="text-xs font-bold">Template</label><select name="template" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"><option value="default" @selected($currentTemplate==='default')>Default</option><option value="contact" @selected($currentTemplate==='contact')>Contact</option></select><p class="mt-1 text-[10px] text-slate-400">Changing template reloads after save.</p></div>
                    <div><label class="text-xs font-bold">Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order',$page->sort_order ?? 0) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
                </div>
                <button class="mt-5 w-full rounded-xl admin-theme-bg py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>{{ $page->exists ? 'Save Page' : 'Create Page' }}</button>
                <a href="{{ route('admin.cms-pages.index') }}" class="mt-2 flex h-11 items-center justify-center rounded-xl border text-sm font-bold">Cancel</a>
            </section>
        </aside>
    </form>
</div>
@endsection

@include('admin.pages.partials.rich-editor')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const content = document.querySelector('#cmsContent');
    if (content) createRichEditor(content).catch(() => {});
    document.querySelector('form.cms-editor-grid')?.addEventListener('submit', () => window.syncRichEditors?.());
});
</script>
@endpush
