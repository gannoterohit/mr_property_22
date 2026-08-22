@extends('layouts.broker')

@section('title', 'Account Pending Approval')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-600">Agent panel</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Account Pending Approval</h1>
            </div>
        </header>

        <div class="owner-dashboard-content max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                    <i class="fas fa-clock text-2xl text-amber-600"></i>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900">Your account is under review</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Thank you for registering as an agent. Our team is reviewing your application.
                    You will be able to access your dashboard once your account is approved.
                </p>
                <p class="mt-4 text-xs text-slate-500">
                    Status: <span class="font-bold uppercase text-amber-700">{{ $user->broker_verification_status }}</span>
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Registered on {{ $user->created_at->format('M d, Y') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </header>
</div>
@endsection