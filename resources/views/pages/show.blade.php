@extends('layouts.public')

@section('title', $title)
@section('description', $metaDescription ?? '')

@section('content')
<div class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5 md:px-6">
                <p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-indigo-600">RoomNest policy</p>
                <h1 class="mt-2 text-2xl font-black text-slate-950 md:text-3xl">{{ $pageTitle ?? $title }}</h1>
                @if(!empty($updatedAt))
                    <p class="mt-2 text-[11px] font-semibold text-slate-400">Last updated {{ $updatedAt->format('d M Y') }}</p>
                @endif
            </div>
            <div class="px-5 py-5 md:px-6">
                <div class="prose max-w-none text-sm leading-7 text-slate-700 prose-headings:font-extrabold prose-headings:text-slate-950 prose-h2:mt-5 prose-h2:text-xl prose-p:my-3 prose-li:my-1">
                    {!! $content !!}
                </div>
            </div>
        </article>
    </div>
</div>
@endsection
