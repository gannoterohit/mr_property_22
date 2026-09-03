@extends('layouts.base')

@section('layout-seo')
    @include('partials.seo')
@endsection

@section('layout-structured-data')
    @if(Route::currentRouteName() === 'home')
        @include('partials.home-ld')
    @endif
@endsection

@section('layout-tracking')
    @include('partials.tracking-head')
@endsection

@section('layout-top-banner')
    @unless(request()->routeIs('owner.*', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*', 'plans', 'login', 'register'))
        @include('partials.offer-banner', ['placement' => 'top_nav'])
    @endunless
@endsection

@section('layout-navigation')
    @include('partials.site-navbar')
@endsection

@section('layout-loading')
    @include('partials.mobile-loading')
@endsection

@section('layout-footer')
    @include('partials.site-footer')
@endsection

@section('layout-bottom-navigation')
    @include('partials.mobile-bottom-nav')
    @include('partials.guest-incentive-modal')
@endsection

