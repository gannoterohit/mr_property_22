@extends('layouts.base')

@push('styles')
<link rel="preload" href="{{ asset('css/owner-sidebar.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/owner-sidebar.css') }}"></noscript>
<link rel="preload" href="{{ asset('css/owner-theme.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}"></noscript>
@endpush

@section('layout-top-banner')
@endsection

@section('layout-navigation')
@endsection

@section('layout-footer')
@endsection

@section('layout-bottom-navigation')
@endsection

@section('layout-popup')
@endsection

@section('content')
<div class="owner-workspace">
    @include('broker.partials.sidebar', ['active' => ''])

    <main class="owner-main">
        <header class="owner-topbar">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[.16em] font-bold text-slate-400">Agent Workspace</p>
                <h1 class="text-base font-bold text-slate-800 truncate">@yield('title', 'Agent Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" target="_blank" class="owner-theme-link hidden sm:flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 rounded-lg transition">
                    <i class="fas fa-external-link-alt"></i> View website
                </a>

                <div class="h-10 pl-1.5 pr-3 rounded-xl border border-slate-200 bg-white flex items-center gap-2.5 shadow-sm">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-lg object-cover ring-1 ring-slate-200">
                    @else
                        <div class="owner-theme-avatar w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    @endif
                    <div class="hidden sm:block leading-tight max-w-[140px]">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Agent</p>
                    </div>
                </div>
            </div>
        </header>
        <div class="owner-content">
            @yield('broker-content')
        </div>
    </main>
</div>
@endsection
