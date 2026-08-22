@extends('layouts.broker')

@section('title', 'Broker Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent panel</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Profile Settings</h1>
            </div>
        </header>
    </header>
    <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="owner-dashboard-content max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 mt-6">
                    <i class="fas fa-circle-check"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('agent.profile.update') }}" method="POST" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
                @csrf @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $broker->name) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $broker->phone) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Agency Name</label>
                    <input type="text" name="agency_name" value="{{ old('agency_name', $broker->agency_name) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Agency Address</label>
                    <textarea name="agency_address" rows="2" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">{{ old('agency_address', $broker->agency_address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">GST Number</label>
                        <input type="text" name="agency_gst" value="{{ old('agency_gst', $broker->agency_gst) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Broker License</label>
                        <input type="text" name="broker_license" value="{{ old('broker_license', $broker->broker_license) }}" class="w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="admin-theme-bg inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold shadow-sm"><i class="fas fa-save text-xs"></i> Update Profile</button>
                </div>
            </form>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Account Status</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Verification Status</dt><dd class="font-bold text-slate-900">{{ ucfirst($broker->broker_verification_status) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Account Active</dt><dd class="font-bold text-slate-900">{{ $broker->is_broker_active ? 'Yes' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Total Listings</dt><dd class="font-bold text-slate-900">{{ $broker->broker_total_listings }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Member Since</dt><dd class="font-bold text-slate-900">{{ $broker->created_at->format('M d, Y') }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
@endsection