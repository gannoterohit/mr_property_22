@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : 'layouts.public')
@section('title', $complaint->ticket_number)
@section('content')
@php $isOwner = Auth::user()->role === 'owner'; @endphp

<div class="{{ $isOwner ? 'owner-workspace' : '' }} min-h-screen bg-slate-50">
    @if($isOwner)
        @include('owner.partials.sidebar', ['active' => 'complaints'])
    @endif

    <main class="{{ $isOwner ? 'complaint-page-main' : '' }} flex-1">

        {{-- Page Header --}}
        @if($isOwner)
            <header class="owner-page-header">
                <a href="{{ route('complaints.index') }}" class="text-xs font-bold text-indigo-600">← My Complaints</a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $complaint->ticket_number }}</h1>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ \App\Models\Complaint::STATUSES[$complaint->status] }}</span>
                </div>
            </header>
        @else
            <header class="bg-white border-b border-slate-200">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                    <a href="{{ route('complaints.index') }}" class="text-xs font-bold text-indigo-600 inline-flex items-center gap-1 mb-3">
                        <i class="fas fa-arrow-left text-[10px]"></i> My Complaints
                    </a>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-extrabold text-slate-900">{{ $complaint->ticket_number }}</h1>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">
                            {{ \App\Models\Complaint::STATUSES[$complaint->status] }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">{{ $complaint->subject }}</p>
                </div>
            </header>
        @endif

        {{-- Content --}}
        <div class="{{ $isOwner ? 'complaint-page-content complaint-detail-grid' : 'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-1 lg:grid-cols-3 gap-5' }}">

            {{-- Left: Main Content --}}
            <section class="min-w-0 space-y-5 {{ $isOwner ? '' : 'lg:col-span-2' }}">

                {{-- Complaint Details --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase text-slate-400">{{ \App\Models\Complaint::CATEGORIES[$complaint->category] }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $complaint->subject }}</h2>
                    <p class="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-600">{{ $complaint->description }}</p>
                    @if($complaint->evidence_path)
                        <a href="{{ route('complaints.evidence', $complaint) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:underline">
                            <i class="fas fa-paperclip"></i> Download evidence
                        </a>
                    @endif
                </div>

                {{-- Conversation --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-900">Conversation</h3>
                    <div class="mt-4 space-y-4">
                        @forelse($complaint->replies as $reply)
                            <div class="rounded-xl {{ $reply->user->role === 'admin' ? 'bg-indigo-50 border border-indigo-100' : 'bg-slate-50' }} p-4">
                                <div class="flex justify-between text-xs">
                                    <strong>{{ $reply->user->role === 'admin' ? 'ApnaNest Support' : $reply->user->name }}</strong>
                                    <span class="text-slate-400">{{ $reply->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $reply->message }}</p>
                                @if($reply->attachment_path)
                                    <a href="{{ route('complaints.attachment', [$complaint, $reply]) }}" class="mt-2 inline-block text-xs font-bold text-indigo-600 hover:underline">
                                        Download attachment
                                    </a>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 italic">Support responses will appear here.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Reply Form --}}
                @unless(in_array($complaint->status, ['closed', 'rejected']))
                    <form action="{{ route('complaints.reply', $complaint) }}" method="POST" enctype="multipart/form-data"
                          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        @csrf
                        <h3 class="font-bold text-slate-900">Add information</h3>
                        @error('message')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        @error('attachment')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        <textarea name="message" rows="4" required
                                  class="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none resize-none"
                                  placeholder="Write a reply...">{{ old('message') }}</textarea>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"
                               class="mt-3 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-indigo-700">
                        <button type="submit" class="mt-4 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                            Send Reply
                        </button>
                    </form>
                @endunless
            </section>

            {{-- Right: Sidebar Info --}}
            <aside class="h-fit space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-900">Ticket details</h3>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div><dt class="text-xs text-slate-400 mb-0.5">Created</dt><dd class="font-semibold">{{ $complaint->created_at->format('d M Y, h:i A') }}</dd></div>
                        <div><dt class="text-xs text-slate-400 mb-0.5">Property</dt><dd class="font-semibold">{{ $complaint->room?->title ?? 'General complaint' }}</dd></div>
                        <div><dt class="text-xs text-slate-400 mb-0.5">Priority</dt><dd class="font-semibold capitalize">{{ $complaint->priority }}</dd></div>
                        @if($complaint->resolution)
                            <div><dt class="text-xs text-slate-400 mb-0.5">Resolution</dt><dd class="mt-1 whitespace-pre-wrap text-slate-700 text-xs">{{ $complaint->resolution }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-900">Activity</h3>
                    <ol class="mt-4 space-y-4">
                        @foreach($complaint->activities->sortByDesc('created_at') as $activity)
                            <li class="border-l-2 border-indigo-200 pl-3">
                                <p class="text-xs font-semibold text-slate-700">{{ $activity->description }}</p>
                                @if($activity->status_to)
                                    <p class="mt-0.5 text-xs text-indigo-600">{{ \App\Models\Complaint::STATUSES[$activity->status_to] ?? $activity->status_to }}</p>
                                @endif
                                <p class="mt-0.5 text-[10px] text-slate-400">{{ $activity->created_at->format('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </aside>

        </div>
    </main>
</div>
@endsection
