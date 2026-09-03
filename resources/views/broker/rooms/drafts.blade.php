@extends('layouts.agent')

@section('title', 'My Drafts')

@section('broker-content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">My Drafts</h1>
            <p class="text-sm text-slate-500 mt-1">Pick up where you left off</p>
        </div>
        <a href="{{ route('agent.rooms.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition shadow-md">
            <i class="fas fa-plus text-xs"></i> New Property
        </a>
    </div>

    @if($drafts->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                <i class="fas fa-folder-open text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">No drafts yet</h3>
            <p class="text-sm text-slate-500 mb-5">Start a new property listing and we'll save your progress automatically.</p>
            <a href="{{ route('agent.rooms.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-plus text-xs"></i> Create Property
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($drafts as $draft)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shrink-0">
                            <i class="fas fa-home"></i>
                        </div>
                        <button type="button" class="text-slate-300 hover:text-rose-500 transition delete-draft" data-id="{{ $draft->id }}" title="Delete">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>

                    <h3 class="font-bold text-slate-900 truncate mb-1">{{ $draft->displayTitle() }}</h3>
                    <p class="text-xs text-slate-500 mb-3">
                        {{ \App\Models\RoomDraft::STEP_NAMES[$draft->step] ?? 'Step ' . $draft->step }}
                        • {{ count($draft->photos ?? []) }} photo{{ count($draft->photos ?? []) === 1 ? '' : 's' }}
                    </p>

                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-3 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all" style="width: {{ $draft->progressPercent() }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-4">
                        <i class="fas fa-clock mr-1"></i> Saved {{ $draft->last_saved_at?->diffForHumans() ?? 'recently' }}
                        @if($draft->isExpired())
                            <span class="text-rose-500 font-bold ml-1">• EXPIRED</span>
                        @endif
                    </p>

                    <a href="{{ route('agent.rooms.create') }}?draft={{ $draft->id }}" class="block w-full text-center px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition">
                        <i class="fas fa-pen mr-1"></i> Continue
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $drafts->links() }}
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.delete-draft').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this draft? This cannot be undone.')) return;
        const id = btn.dataset.id;
        try {
            const res = await fetch(`/agent/rooms/drafts/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (json.success) {
                btn.closest('.group').remove();
                toastr && toastr.success('Draft deleted');
            }
        } catch (e) {
            toastr && toastr.error('Could not delete');
        }
    });
});
</script>
@endsection
