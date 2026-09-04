@extends('layouts.public')

@section('title', $title)
@section('description', $metaDescription ?? 'Answers to common RoomNest questions about owner contact, brokerage, payments and safety.')

@section('content')
<div class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 text-center">
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-indigo-600">Help center</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950 md:text-3xl">{{ $pageTitle ?? $title }}</h1>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">Quick answers about contact unlocks, brokerage, visits and listing safety.</p>
            @if(!empty($updatedAt))
                <p class="mt-2 text-[11px] font-semibold text-slate-400">Last updated {{ $updatedAt->format('d M Y') }}</p>
            @endif
        </div>

        <div class="space-y-2">
            @forelse($faqs as $index => $faq)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <button class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left transition-colors duration-200 hover:bg-slate-50 focus:outline-none active:bg-slate-100"
                            onclick="toggleFaq('faq-{{ $index }}')"
                            aria-expanded="false"
                            aria-controls="faq-{{ $index }}">
                        <span class="text-sm font-extrabold text-slate-900">{{ $faq['question'] }}</span>
                        <i id="icon-faq-{{ $index }}" class="fas fa-chevron-down shrink-0 text-xs text-slate-400 transition-transform duration-200"></i>
                    </button>
                    <div id="faq-{{ $index }}" class="hidden px-4 pb-4 text-sm leading-6 text-slate-600 transition-all duration-300">
                        <div class="border-t border-slate-100 pt-3">
                            {!! \App\Services\HtmlSanitizer::clean($faq['answer']) !!}
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border bg-white p-8 text-center shadow-sm">
                    <i class="fas fa-question-circle text-4xl text-slate-300"></i>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">No FAQs yet</h3>
                    <p class="mt-1 text-sm text-slate-500">Check back later for updates.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 rounded-xl border border-indigo-100 bg-white px-4 py-3 text-center text-sm text-slate-600">
            Still need help?
            @php $contactPageLive = \App\Models\CmsPage::published()->where('slug', 'contact-us')->exists(); @endphp
            @if($contactPageLive)
                <a href="{{ url('/contact-us') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Contact support</a>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleFaq(id) {
        const element = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        const button = element.previousElementSibling;
        const isHidden = element.classList.contains('hidden');
        
        if (isHidden) {
            element.classList.remove('hidden');
            icon.classList.add('rotate-180');
            button.setAttribute('aria-expanded', 'true');
        } else {
            element.classList.add('hidden');
            icon.classList.remove('rotate-180');
            button.setAttribute('aria-expanded', 'false');
        }
    }
</script>
@endsection
