@extends('layouts.admin')

@section('admin-content')
<div class="flex min-h-0">
    <div class="flex-1 min-w-0 flex flex-col">
        <div class="container-fluid px-4 py-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <h1 class="text-2xl font-bold text-slate-800">{{ $pageTitle }}</h1>
                @if(!empty($previewUrl))
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border admin-theme-soft px-4 py-2 text-xs font-bold transition admin-theme-hover-bg">
                        <i class="fas fa-eye"></i>
                        Preview
                    </a>
                @endif
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
            @endif

            <div id="pageEditorCard" class="bg-white rounded-lg shadow-md p-6">
                <form id="pageEditorForm" action="{{ url()->current() }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label for="content" class="block text-sm font-medium text-slate-700">Page Content</label>
                            <button type="button" id="toggleEditorFullscreen" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-50 admin-theme-hover-text text-xs font-bold transition"><i class="fas fa-expand mr-1"></i>Full screen</button>
                        </div>
                        <textarea id="content" name="content" rows="20" 
                                  class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none  sm:text-sm">{{ $setting->value ?? '' }}</textarea>
                        <p id="editorFallbackNote" class="hidden mt-2 text-xs text-amber-600 font-semibold">Rich editor unavailable. You can edit directly in the textarea above.</p>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" 
                                class="px-4 py-2 admin-theme-bg rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 ">
                            Save Page Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.pages.partials.rich-editor')
<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('pageEditorForm');
        var contentEl = document.getElementById('content');
        var fallbackNote = document.getElementById('editorFallbackNote');

        if (contentEl && typeof createRichEditor === 'function') {
            createRichEditor(contentEl).catch(function(error) {
                console.error('Rich editor init failed:', error);
                contentEl.classList.remove('hidden');
                if (fallbackNote) fallbackNote.classList.remove('hidden');
            });
        } else if (contentEl && fallbackNote) {
            fallbackNote.classList.remove('hidden');
        }

        if (form) {
            form.addEventListener('submit', function() {
                window.syncRichEditors?.();
            });
        }

        document.getElementById('toggleEditorFullscreen')?.addEventListener('click', function() {
            var card = document.getElementById('pageEditorCard');
            card.classList.toggle('page-editor-fullscreen');
            var active = card.classList.contains('page-editor-fullscreen');
            this.innerHTML = active ? '<i class="fas fa-compress mr-1"></i>Exit full screen' : '<i class="fas fa-expand mr-1"></i>Full screen';
        });
    });
})();
</script>
@endsection
