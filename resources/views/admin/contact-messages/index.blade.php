@extends('layouts.admin')

@section('title', 'Contact Messages - Admin')

@section('admin-content')
<div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8">
        <div class="container mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-envelope admin-theme-text mr-2"></i>Contact Messages
                    </h1>
                    <p class="text-gray-600 mt-1">Total: {{ $messages->total() }} enquiries</p>
                </div>
                <x-admin.data-actions dataset="contact-messages" />
            </div>

            <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Sender</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Subject</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Message</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($messages as $msg)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ !$msg->is_read ? 'bg-blue-50/30' : '' }}">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 admin-theme-soft rounded-xl flex items-center justify-center font-bold uppercase">
                                                {{ substr($msg->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ $msg->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $msg->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-bold text-gray-700">{{ $msg->subject ?? 'No Subject' }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm text-gray-600 line-clamp-2 max-w-xs">{{ $msg->message }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-xs font-bold text-gray-500 uppercase">{{ $msg->created_at->format('d M Y') }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $msg->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if(!$msg->is_read)
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase rounded-full">New</span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-500 text-[10px] font-black uppercase rounded-full">Read</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center gap-2">
                                            @if(!$msg->is_read)
                                                <form action="{{ route('admin.contact-messages.read', $msg->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-all flex items-center justify-center" title="Mark as Read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <x-admin.action-icon variant="view" type="button" onclick="showMessage('{{ addslashes($msg->name) }}', '{{ addslashes($msg->message) }}')" title="View Full Message" />
                                            <form action="{{ route('admin.contact-messages.destroy', $msg->id) }}" method="POST" class="admin-confirm" data-confirm-title="Delete contact message?" data-confirm-text="This message will be permanently removed." data-confirm-button="Yes, delete message">
                                                @csrf @method('DELETE')
                                                <x-admin.action-icon variant="delete" type="submit" />
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-gray-300 mb-4">
                                                <i class="fas fa-envelope-open text-4xl"></i>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-400">No Messages Yet</h3>
                                            <p class="text-gray-400 text-sm">When users contact you, their messages will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($messages->hasPages())
                    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Message Modal --}}
<div id="msgModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[3000] hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-2xl font-black text-gray-900" id="modalSender">Message</h3>
            <button onclick="closeModal()" class="w-10 h-10 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-8 max-h-[60vh] overflow-y-auto">
            <p class="text-gray-600 leading-relaxed whitespace-pre-wrap" id="modalBody"></p>
        </div>
        <div class="p-8 bg-gray-50 flex justify-end">
            <button onclick="closeModal()" class="bg-slate-900 text-white font-bold px-8 py-3 rounded-xl">Close</button>
        </div>
    </div>
</div>

<script>
    function showMessage(sender, body) {
        document.getElementById('modalSender').innerText = 'From: ' + sender;
        document.getElementById('modalBody').innerText = body;
        document.getElementById('msgModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('msgModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>
@endsection
