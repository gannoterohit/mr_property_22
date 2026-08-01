@extends('layouts.public')

@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomNest');
    $phone = \App\Models\Setting::get('contact_phone', '+91 9340058914');
    $email = \App\Models\Setting::get('contact_email', 'support@roomrental.com');
    $address = \App\Models\Setting::get('company_address', 'Indore, Madhya Pradesh, India');
@endphp

@section('title', 'Contact Us | ' . $siteName)
@section('description', 'Contact RoomNest support for room listing help, owner contact unlocks, complaints and general assistance.')

@section('content')
<div class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mb-7 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-indigo-600">Support desk</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 md:text-4xl">Contact {{ $siteName }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Need help with a room, owner contact, payment, listing, or complaint? Send the details and our team will review it.</p>
            </div>
            <div class="rounded-xl border border-indigo-100 bg-white p-4 text-sm text-slate-600 shadow-sm">
                <strong class="block text-slate-950"><i class="fas fa-clock mr-2 text-indigo-600"></i>Typical response</strong>
                <span class="mt-1 block text-xs leading-5">Most support messages are reviewed during business hours. For fraud or safety concerns, include screenshots and room details.</span>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Contact information</h2>
                    <div class="mt-5 space-y-4">
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-indigo-200 hover:bg-indigo-50">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><i class="fas fa-phone"></i></span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Phone</span>
                                <span class="block break-words text-sm font-bold text-slate-800">{{ $phone }}</span>
                            </span>
                        </a>
                        <a href="mailto:{{ $email }}" class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-indigo-200 hover:bg-indigo-50">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700"><i class="fas fa-envelope"></i></span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Email</span>
                                <span class="block break-words text-sm font-bold text-slate-800">{{ $email }}</span>
                            </span>
                        </a>
                        <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"><i class="fas fa-location-dot"></i></span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Location</span>
                                <span class="block break-words text-sm font-bold text-slate-800">{{ $address }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="text-sm font-extrabold text-amber-900"><i class="fas fa-shield-halved mr-2"></i>Before paying anyone</h3>
                    <p class="mt-2 text-xs leading-5 text-amber-800">Visit the property, verify owner identity, confirm rent/deposit, and take a receipt for any payment.</p>
                </div>
            </aside>

            <section class="rounded-2xl border bg-white p-5 shadow-sm md:p-6">
                <div class="mb-5">
                    <h2 class="text-xl font-extrabold text-slate-950">Send a message</h2>
                    <p class="mt-1 text-xs text-slate-500">Add enough detail so support can understand the issue quickly.</p>
                </div>

                <form action="{{ route('pages.contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                            <p class="font-extrabold">Please correct the following information:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wide text-slate-400" for="name">Name</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Your full name" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-indigo-100">
                            @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wide text-slate-400" for="email">Email</label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="you@example.com" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-indigo-100">
                            @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wide text-slate-400" for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" placeholder="Payment, listing, complaint, or general help" class="h-11 w-full rounded-xl border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-indigo-100">
                        @error('subject')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-extrabold uppercase tracking-wide text-slate-400" for="message">Message</label>
                        <textarea name="message" id="message" rows="5" required placeholder="Tell us what happened. Include room title, city, payment/reference details or screenshots context if relevant." class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-indigo-100">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-extrabold text-white shadow-sm shadow-indigo-100 transition hover:bg-indigo-700 md:w-auto">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
