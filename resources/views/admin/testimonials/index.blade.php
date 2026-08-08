@extends('layouts.admin')

@section('title', 'Testimonials')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Landing content</p>
            <h1 class="mt-1 text-2xl font-extrabold">Testimonials</h1>
            <p class="text-sm text-slate-500">Add, approve, deactivate or remove reviews shown on the landing page.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="testimonials" importable />
            <a href="{{ route('admin.testimonials.create') }}" class="inline-flex items-center gap-2 rounded-xl admin-theme-bg px-4 py-3 text-xs font-bold text-white">
                <i class="fas fa-plus"></i>Add Review
            </a>
        </div>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="flex flex-wrap items-end gap-2 rounded-2xl border bg-white p-4">
        <div><label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Search</label><input name="search" value="{{ request('search') }}" placeholder="Name, city or review" class="h-10 rounded-lg text-xs"></div>
        <div><label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Status</label><select name="status" class="h-10 rounded-lg text-xs"><option value="">All</option><option value="pending" @selected(request('status')==='pending')>Pending</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select></div>
        <button class="h-10 rounded-lg bg-slate-900 px-4 text-xs font-bold text-white">Apply</button>
        <a href="{{ route('admin.testimonials.index') }}" class="flex h-10 items-center rounded-lg border px-3 text-xs font-bold">Reset</a>
    </form>

    <div class="overflow-hidden rounded-2xl border bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[1020px] w-full text-left">
                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                    <tr><th class="p-4">Review</th><th class="p-4">Person</th><th class="p-4">Rating</th><th class="p-4">Order</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td class="p-4"><p class="max-w-xl text-sm leading-6 text-slate-700">{{ $testimonial->message }}</p></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if($testimonial->avatar_url)<img src="{{ $testimonial->avatar_url }}" alt="{{ $testimonial->name }}" class="h-10 w-10 rounded-xl object-cover">@else<span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft text-xs font-extrabold admin-theme-text">{{ strtoupper(substr($testimonial->name,0,1)) }}</span>@endif
                                    <div><p class="text-sm font-extrabold text-slate-900">{{ $testimonial->name }}</p><p class="text-xs text-slate-500">{{ collect([$testimonial->role, $testimonial->city])->filter()->join(' - ') }}</p></div>
                                </div>
                            </td>
                            <td class="p-4 text-amber-500 text-xs">@for($i=1;$i<=5;$i++)<i class="fas fa-star {{ $i <= $testimonial->rating ? '' : 'text-slate-200' }}"></i>@endfor</td>
                            <td class="p-4 text-xs font-bold text-slate-600">{{ $testimonial->sort_order }}</td>
                            <td class="p-4">
                                <x-admin.status-toggle
                                    :active="$testimonial->status === 'active'"
                                    :inactive-label="ucfirst($testimonial->status)"
                                    :action="route('admin.testimonials.toggle-status', $testimonial)"
                                    :data-label="$testimonial->name"
                                />
                            </td>
                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    <x-admin.action-icon variant="edit" :href="route('admin.testimonials.edit', $testimonial)" />
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="admin-confirm" data-confirm-title="Delete review by {{ $testimonial->name }}?" data-confirm-text="This testimonial will be permanently removed." data-confirm-button="Yes, delete review">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-12 text-center text-sm text-slate-500">No testimonials found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($testimonials->hasPages())
            <div class="border-t p-4">{{ $testimonials->links() }}</div>
        @endif
    </div>
</div>
@endsection
