@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'Submit a Complaint')
@php $role = Auth::user()->role; $isOwner = $role === 'owner'; $isBroker = $role === 'broker'; $isCustomer = $role === 'user'; $contentSection = $isOwner ? 'owner-content' : ($isBroker ? 'broker-content' : ($isCustomer ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900">Submit a Complaint</h1>
        <p class="text-sm text-slate-500 mt-1">Give clear details and evidence so our team can investigate.</p>
    </div>
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data"
          class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Category *</label>
            <select name="category" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                @foreach(\App\Models\Complaint::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Related property <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="number" name="room_id" value="{{ old('room_id', $room?->id) }}"
                   class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                   placeholder="Property ID">
            @if($room)<p class="mt-1.5 text-xs text-slate-500">Selected: {{ $room->title }}</p>@endif
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Subject *</label>
            <input name="subject" value="{{ old('subject') }}" maxlength="255" required
                   class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none"
                   placeholder="Short summary of the issue">
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Details *</label>
            <textarea name="description" rows="6" minlength="20" required
                      class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none resize-none"
                      placeholder="What happened, when, and what resolution do you expect?">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Evidence <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="file" name="evidence" accept=".jpg,.jpeg,.png,.webp,.pdf"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-indigo-700">
            <p class="mt-1.5 text-xs text-slate-500">JPG, PNG, WebP or PDF — max 5 MB.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                Submit Complaint
            </button>
            <a href="{{ route('complaints.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
