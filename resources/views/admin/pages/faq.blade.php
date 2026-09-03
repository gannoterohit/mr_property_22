@extends('layouts.admin')

@section('admin-content')
<div class="flex min-h-0">
    <div class="flex-1 min-w-0 flex flex-col">
        <div class="container-fluid px-4 py-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-slate-800">{{ $pageTitle }}</h1>
                @if(!empty($previewUrl))
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border admin-theme-soft px-4 py-2 text-xs font-bold transition admin-theme-hover-bg">
                        <i class="fas fa-eye"></i>
                        Preview
                    </a>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 overflow-y-auto max-h-[calc(100vh-150px)]">
                <form id="faqEditorForm" action="{{ url()->current() }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div id="faq-container" class="space-y-4">
                        @php
                            $faqs = json_decode($setting->value ?? '[]', true);
                            if (!is_array($faqs)) $faqs = [];
                        @endphp

                        @forelse($faqs as $index => $faq)
                            <div class="faq-item border border-slate-200 rounded-lg p-4 bg-slate-50 relative">
                                <button type="button" class="absolute top-2 right-2 text-red-500 hover:text-red-700 remove-faq">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Question</label>
                                    <input type="text" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" 
                                           class="w-full border-slate-300 rounded-md shadow-sm ">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Answer</label>
                                    <textarea name="faqs[{{ $index }}][answer]" rows="2" 
                                              class="w-full border-slate-300 rounded-md shadow-sm ">{{ $faq['answer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @empty
                            <div class="faq-item border border-slate-200 rounded-lg p-4 bg-slate-50 relative">
                                <button type="button" class="absolute top-2 right-2 text-red-500 hover:text-red-700 remove-faq">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Question</label>
                                    <input type="text" name="faqs[0][question]" class="w-full border-slate-300 rounded-md shadow-sm ">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Answer</label>
                                    <textarea name="faqs[0][answer]" rows="2" class="w-full border-slate-300 rounded-md shadow-sm "></textarea>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 flex justify-between">
                        <button type="button" id="add-faq-btn" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md shadow-sm">
                            <i class="fas fa-plus mr-2"></i>Add New Question
                        </button>
                        <button type="submit" class="px-4 py-2 admin-theme-bg rounded-md shadow-sm">
                            Save FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.pages.partials.rich-editor')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('faq-container');
        const addBtn = document.getElementById('add-faq-btn');
        let faqCount = {{ count($faqs) > 0 ? count($faqs) : 1 }};

        function initAnswerEditor(element) {
            if (!element) return;
            if (typeof createRichEditor !== 'function') return;
            createRichEditor(element).catch(function(error) {
                console.error('FAQ editor init failed:', error);
                element.classList.remove('hidden');
            });
        }

        document.querySelectorAll('textarea[name^="faqs"]').forEach(textarea => {
            initAnswerEditor(textarea);
        });

        addBtn.addEventListener('click', function() {
            const template = `
                <div class="faq-item border border-slate-200 rounded-lg p-4 bg-slate-50 relative animate-fade-in-down mt-4">
                    <button type="button" class="absolute top-2 right-2 text-red-500 hover:text-red-700 remove-faq">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Question</label>
                        <input type="text" name="faqs[${faqCount}][question]" class="w-full border-slate-300 rounded-md shadow-sm ">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Answer</label>
                        <textarea name="faqs[${faqCount}][answer]" rows="2" class="w-full border-slate-300 rounded-md shadow-sm "></textarea>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            const newTextarea = container.lastElementChild.querySelector('textarea');
            initAnswerEditor(newTextarea);

            faqCount++;
        });

        document.getElementById('faqEditorForm')?.addEventListener('submit', function() {
            window.syncRichEditors?.();
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-faq')) {
                const item = e.target.closest('.faq-item');
                // Check if last one
                if (document.querySelectorAll('.faq-item').length > 1) {
                    item.remove();
                } else {
                    alert('You must have at least one FAQ item.');
                }
            }
        });
    });
</script>
@endsection
