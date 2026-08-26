@extends('layouts.agent')

@section('title', 'Agent Profile & Agency Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-sidebar.css') }}">
@endpush

@section('broker-content')
@php $broker = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-section-heading mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('agent.dashboard') }}"
               class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition shadow-sm flex-shrink-0">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h2 class="text-lg font-black text-slate-900">Profile & Agency Settings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage your personal info, credentials, and contact details.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3.5 shadow-sm">
            <i class="fas fa-circle-check text-emerald-600 text-lg flex-shrink-0"></i>
            <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif

    <div class="agent-profile-grid">

        {{-- Main Form --}}
        <div class="agent-profile-card">
            <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fas fa-user-gear"></i>
                </span>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Personal & Agency Information</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update your agent details and agency credentials</p>
                </div>
            </div>

            <form action="{{ route('agent.profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="agent-form-row">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $broker->name) }}" required placeholder="Enter full name">
                        @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $broker->phone) }}" placeholder="e.g. +91 9876543210">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Agency Name</label>
                    <input type="text" name="agency_name" value="{{ old('agency_name', $broker->agency_name) }}" placeholder="e.g. Apex Realty Solutions">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Agency Address</label>
                    <textarea name="agency_address" rows="3" placeholder="Enter complete office/agency address...">{{ old('agency_address', $broker->agency_address) }}</textarea>
                </div>

                <div class="agent-form-row pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">GST Number</label>
                        <input type="text" name="agency_gst" value="{{ old('agency_gst', $broker->agency_gst) }}" placeholder="e.g. 22AAAAA0000A1Z5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Broker License No.</label>
                        <input type="text" name="broker_license" value="{{ old('broker_license', $broker->broker_license) }}" placeholder="e.g. BRK-884920">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-extrabold py-3 px-7 rounded-xl shadow-md shadow-indigo-100 transition text-sm">
                        <i class="fas fa-save text-xs"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Info Card --}}
        <div class="space-y-4">
            <div class="agent-profile-card">
                {{-- Avatar + Name --}}
                <div class="flex items-center gap-3.5 pb-5 mb-5 border-b border-slate-100">
                    @if($broker->avatar)
                        <img src="{{ asset('storage/'.$broker->avatar) }}" alt="{{ $broker->name }}"
                             class="w-14 h-14 rounded-2xl object-cover ring-2 ring-indigo-100 shadow-sm flex-shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-black text-xl flex items-center justify-center shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($broker->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h2 class="font-extrabold text-slate-900 text-base truncate">{{ $broker->name }}</h2>
                        <p class="text-xs text-slate-500 truncate">{{ $broker->email }}</p>
                        <span class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                            <i class="fas fa-briefcase text-[10px]"></i> Agent
                        </span>
                    </div>
                </div>

                {{-- Info rows --}}
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Verification</span>
                        @if(in_array(strtolower($broker->broker_verification_status ?? ''), ['approved','verified']))
                            <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                                <i class="fas fa-circle-check text-emerald-500 text-[10px]"></i> Approved
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                                <i class="fas fa-clock text-amber-500 text-[10px]"></i>
                                {{ ucfirst($broker->broker_verification_status ?? 'Pending') }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-slate-500 font-medium">Account Status</span>
                        <span class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $broker->is_broker_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $broker->is_broker_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-slate-500 font-medium">Total Listings</span>
                        <span class="font-black text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                            {{ $broker->broker_total_listings ?? 0 }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-slate-500 font-medium">Member Since</span>
                        <span class="font-bold text-slate-800">
                            {{ $broker->created_at ? $broker->created_at->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="agent-profile-card">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Quick Links</p>
                <div class="space-y-2">
                    <a href="{{ route('agent.properties') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                        <i class="fas fa-building w-4 text-center text-slate-400"></i> My Properties
                    </a>
                    <a href="{{ route('agent.plans') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                        <i class="fas fa-layer-group w-4 text-center text-slate-400"></i> Listing Plans
                    </a>
                    <a href="{{ route('agent.payments') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                        <i class="fas fa-credit-card w-4 text-center text-slate-400"></i> Payments
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection