@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : 'layouts.public')
@section('title', 'My Complaints')
@section('content')
@php $isOwner = Auth::user()->role === 'owner'; @endphp

<div class="{{ $isOwner ? 'owner-workspace' : '' }} min-h-screen bg-slate-50">
    @if($isOwner)
        @include('owner.partials.sidebar', ['active' => 'complaints'])
    @endif

    <main class="{{ $isOwner ? 'complaint-page-main' : '' }} flex-1">

        {{-- Page Header --}}
        @if($isOwner)
            <header class="owner-page-header flex items-center justify-between gap-4">
                <div><h1 class="text-2xl font-bold text-slate-900">My Complaints</h1><p class="text-sm text-slate-500 mt-1">Track reports and support responses.</p></div>
                <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white"><i class="fas fa-plus"></i> New Complaint</a>
            </header>
        @else
            <header class="bg-white border-b border-slate-200">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-1">Support</p>
                        <h1 class="text-2xl font-extrabold text-slate-900">My Complaints</h1>
                        <p class="text-sm text-slate-500 mt-1">Track reports and support responses.</p>
                    </div>
                    <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                        <i class="fas fa-plus text-xs"></i> New Complaint
                    </a>
                </div>
            </header>
        @endif

        {{-- Content --}}
        <div class="{{ $isOwner ? 'complaint-page-content' : 'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6' }}">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto"><table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-4">Ticket</th><th class="p-4">Complaint</th><th class="p-4">Property</th><th class="p-4">Status</th><th class="p-4">Updated</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($complaints as $complaint)
                        <tr class="hover:bg-slate-50"><td class="p-4"><a class="font-bold text-indigo-600" href="{{ route('complaints.show', $complaint) }}">{{ $complaint->ticket_number }}</a></td><td class="p-4"><p class="font-semibold text-slate-900">{{ $complaint->subject }}</p><p class="text-xs text-slate-500">{{ \App\Models\Complaint::CATEGORIES[$complaint->category] ?? $complaint->category }}</p></td><td class="p-4 text-slate-600">{{ $complaint->room?->title ?? 'General' }}</td><td class="p-4"><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ \App\Models\Complaint::STATUSES[$complaint->status] ?? $complaint->status }}</span></td><td class="p-4 text-slate-500">{{ $complaint->updated_at->diffForHumans() }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-12 text-center"><i class="fas fa-shield-halved text-3xl text-slate-300"></i><p class="mt-3 font-semibold text-slate-600">No complaints submitted.</p></td></tr>
                    @endforelse
                    </tbody>
                </table></div>
                @if($complaints->hasPages())<div class="border-t border-slate-100 p-4">{{ $complaints->links() }}</div>@endif
            </div>
        </div>
    </main>
</div>
@endsection
