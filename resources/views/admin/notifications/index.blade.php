@extends('layouts.admin')

@section('title', 'Admin Notifications')

@section('admin-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Notifications</h1>
            <p class="text-xs text-slate-500 mt-1">Manage system alerts for new room listings, customer inquiries, and complaints</p>
        </div>
        <div class="flex items-center gap-3">
            @if(\App\Models\AdminNotification::where('is_read', false)->exists())
                <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white text-xs font-bold rounded-xl transition">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $notification)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 gap-4 transition hover:bg-slate-50 {{ $notification->is_read ? 'opacity-70 bg-white' : 'bg-indigo-50/20' }}">
                <div class="flex items-start gap-4 min-w-0 flex-1">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification->is_read ? 'bg-slate-100 text-slate-400' : 'admin-theme-soft' }}">
                        <i class="fas {{ $notification->icon ?: 'fa-bell' }} text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-800 truncate">
                                @if($notification->link)
                                    <a href="{{ route('admin.notifications.markRead', $notification->id) }}" class="hover:text-indigo-600">
                                        {{ $notification->title }}
                                    </a>
                                @else
                                    {{ $notification->title }}
                                @endif
                            </h3>
                            @if(!$notification->is_read)
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-[10px] font-extrabold">NEW</span>
                            @endif
                        </div>
                        @if($notification->message)
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notification->message }}</p>
                        @endif
                        <span class="text-[11px] font-medium text-slate-400 mt-1 block">
                            <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->format('d M Y, h:i A') }})
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    @if($notification->link)
                        <a href="{{ route('admin.notifications.markRead', $notification->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 text-xs font-bold transition">
                            <span>Open Link</span> <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif

                    @if(!$notification->is_read)
                        <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-xs font-bold transition">
                                <i class="fas fa-check"></i> Mark Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400">
                <i class="fas fa-bell-slash text-4xl mb-3 text-slate-300 block"></i>
                <h3 class="text-base font-bold text-slate-700">No Notifications</h3>
                <p class="text-xs text-slate-500 mt-1">You're all caught up! New notifications will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
