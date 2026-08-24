@extends('layouts.broker')

@section('title', 'My Properties')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent panel</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">My Properties</h1>
                <p class="mt-2 text-sm text-slate-500">Manage your property listings.</p>
            </div>
            <a href="{{ route('agent.rooms.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus"></i> Add New Property
            </a>
        </div>
    </header>

        <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <a href="{{ route('agent.properties') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold {{ !request('status') && !request('listing_status') ? 'text-indigo-600 border-indigo-200' : 'text-slate-700 hover:bg-slate-50' }}">All</a>
                <a href="{{ route('agent.properties', ['status' => 'active']) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold {{ request('status') === 'active' ? 'text-indigo-600 border-indigo-200' : 'text-slate-700 hover:bg-slate-50' }}">Active</a>
                <a href="{{ route('agent.properties', ['listing_status' => 'pending']) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold {{ request('listing_status') === 'pending' ? 'text-indigo-600 border-indigo-200' : 'text-slate-700 hover:bg-slate-50' }}">Pending</a>
            </div>

            <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mt-6">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead><tr><th class="px-5 text-left">Property</th><th class="px-5 text-left">City</th><th class="px-5 text-left">Rent</th><th class="px-5 text-left">Status</th><th class="px-5 text-left">Listed</th><th class="px-5 text-right">Actions</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($properties as $property)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-house"></i></div><div class="font-bold text-slate-900">{{ $property->title }}</div></div></td>
                                    <td class="px-5 text-slate-600">{{ $property->city }}</td>
                                    <td class="px-5 text-slate-600">&#8377;{{ number_format($property->rent) }}</td>
                                    <td class="px-5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $property->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($property->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $property->status === 'booked' ? 'Rented' : ucfirst($property->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 text-slate-500">{{ $property->created_at->format('M d, Y') }}</td>
                                    <td class="px-5"><div class="flex justify-end gap-2">
                                        <a href="{{ route('agent.rooms.show', $property) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700 hover:bg-slate-200" target="_blank"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('agent.rooms.edit', $property) }}" class="rounded-lg bg-indigo-100 p-2 text-xs font-bold text-indigo-700 hover:bg-indigo-200"><i class="fas fa-edit"></i></a>
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-16 text-center text-slate-500">No properties found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="lg:hidden divide-y divide-slate-100">
                    @forelse($properties as $property)
                        <article class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ $property->title }}</h3>
                                    <p class="text-xs text-slate-500">{{ $property->city }} · &#8377;{{ number_format($property->rent) }}/month</p>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold mt-2 {{ $property->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($property->status) }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('agent.rooms.show', $property) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700" target="_blank"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('agent.rooms.edit', $property) }}" class="rounded-lg bg-indigo-100 p-2 text-xs font-bold text-indigo-700"><i class="fas fa-edit"></i></a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">No properties found.</div>
                    @endforelse
                </div>
                <div class="p-4">{{ $properties->links() }}</div>
            </div>
        </div>
    </div>
@endsection