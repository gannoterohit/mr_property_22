@extends('layouts.agent')

@section('title', 'Agent Profile & Agency Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account-profile-edit.css') }}">
@endpush

@section('broker-content')
@php $broker = Auth::user(); @endphp
<div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('agent.dashboard') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Agent Profile & Agency Details</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Manage your personal information, agency credentials, and contact details.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3.5 text-sm font-bold text-emerald-800 shadow-sm">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="agent-profile-grid">
        <!-- Main Form Column -->
        <div class="agent-profile-card space-y-6">
            <form action="{{ route('agent.profile.update') }}" method="POST" class="space-y-6">
                @csrf 
                @method('PATCH')

                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold shrink-0">
                        <i class="fas fa-user-gear"></i>
                    </span>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Personal & Agency Information</h3>
                        <p class="text-xs text-slate-500">Update your agent details and agency credentials</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="agent-form-row">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $broker->name) }}" required placeholder="Enter full name">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $broker->phone) }}" placeholder="e.g. +91 9876543210">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Agency Name</label>
                        <input type="text" name="agency_name" value="{{ old('agency_name', $broker->agency_name) }}" placeholder="e.g. Apex Realty Solutions">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Agency Address</label>
                        <textarea name="agency_address" rows="3" placeholder="Enter complete office/agency address...">{{ old('agency_address', $broker->agency_address) }}</textarea>
                    </div>

                    <div class="agent-form-row pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">GST Number</label>
                            <input type="text" name="agency_gst" value="{{ old('agency_gst', $broker->agency_gst) }}" placeholder="e.g. 22AAAAA0000A1Z5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Broker License Number</label>
                            <input type="text" name="broker_license" value="{{ old('broker_license', $broker->broker_license) }}" placeholder="e.g. BRK-884920">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-extrabold py-3.5 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all duration-200 transform active:scale-[0.99] text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-save text-xs"></i> Save Profile Details
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Column -->
        <div class="agent-profile-card">
            <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                @if($broker->avatar)
                    <img src="{{ asset('storage/'.$broker->avatar) }}" alt="{{ $broker->name }}" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-indigo-100 shadow-sm shrink-0">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-black text-xl flex items-center justify-center shadow-sm shrink-0">
                        {{ strtoupper(substr($broker->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="font-extrabold text-slate-900 text-base truncate">{{ $broker->name }}</h2>
                    <p class="text-xs text-slate-500 truncate">{{ $broker->email }}</p>
                    <span class="mt-1 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                        <i class="fas fa-briefcase text-[10px]"></i> Verified Agent
                    </span>
                </div>
            </div>

            <div class="mt-5 space-y-3.5 text-xs sm:text-sm">
                <div class="flex items-center justify-between py-1">
                    <span class="text-slate-500 font-medium">Verification Status</span>
                    @if(strtolower($broker->broker_verification_status ?? '') === 'approved' || strtolower($broker->broker_verification_status ?? '') === 'verified')
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60 flex items-center gap-1">
                            <i class="fas fa-circle-check text-emerald-500 text-xs"></i> Approved
                        </span>
                    @else
                        <span class="font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/60 flex items-center gap-1">
                            <i class="fas fa-clock text-amber-500 text-xs"></i> {{ ucfirst($broker->broker_verification_status ?? 'Pending') }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between py-1 border-t border-slate-100">
                    <span class="text-slate-500 font-medium">Account Status</span>
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $broker->is_broker_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $broker->is_broker_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-1 border-t border-slate-100">
                    <span class="text-slate-500 font-medium">Total Properties Listed</span>
                    <span class="font-black text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-lg text-xs">{{ $broker->broker_total_listings ?? 0 }}</span>
                </div>

                <div class="flex items-center justify-between py-1 border-t border-slate-100">
                    <span class="text-slate-500 font-medium">Member Since</span>
                    <span class="font-bold text-slate-800">{{ $broker->created_at ? $broker->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection