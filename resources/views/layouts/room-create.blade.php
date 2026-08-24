@extends('layouts.base')

@push('styles')
<link rel="preload" href="{{ asset('css/owner-sidebar.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/owner-sidebar.css') }}"></noscript>
<link rel="preload" href="{{ asset('css/owner-theme.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}"></noscript>
<link rel="stylesheet" href="{{ asset('css/owner-rooms-create.css') }}">
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
<div class="owner-workspace room-editor min-h-screen bg-slate-50">
    @if(Auth::user()->role === 'broker')
        @include('broker.partials.sidebar', ['active' => ''])
    @else
        @include('owner.partials.sidebar', ['active' => ''])
    @endif

    <div class="owner-form-shell">
        <main class="flex-1">
            @if(Auth::user()->role === 'broker')
            <div class="owner-page-header owner-form-page-header hidden lg:block bg-white border-b border-slate-200">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div>
                        <h1 class="font-black text-slate-950">@yield('title', 'List Your Property')</h1>
                        <p class="text-slate-500">Fill in the details to reach thousands of potential tenants.</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="room-editor-content max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 pb-12 lg:pb-16">
                @yield('owner-content')
            </div>
        </main>
    </div>
</div>
@endsection
