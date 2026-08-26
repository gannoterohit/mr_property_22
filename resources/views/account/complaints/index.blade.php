@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'My Complaints')
@php $role = Auth::user()->role; $isOwner = $role === 'owner'; $isBroker = $role === 'broker'; $contentSection = $isOwner ? 'owner-content' : ($isBroker ? 'broker-content' : ($role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div><h1 class="text-2xl font-extrabold text-slate-900">My Complaints</h1><p class="text-sm text-slate-500 mt-1">Track reports and support responses.</p></div>
        <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition"><i class="fas fa-plus text-xs"></i> New Complaint</a>
    </div>
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
@endsection
