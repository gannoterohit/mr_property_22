@extends('layouts.admin')

@section('title', $testimonial->exists ? 'Edit Testimonial' : 'Create Testimonial')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header>
        <a href="{{ route('admin.testimonials.index') }}" class="text-xs font-bold admin-theme-text"><i class="fas fa-arrow-left mr-1"></i>All testimonials</a>
        <h1 class="mt-3 text-2xl font-extrabold">{{ $testimonial->exists ? 'Edit Testimonial' : 'Create Testimonial' }}</h1>
        <p class="text-sm text-slate-500">Only active testimonials appear on the landing page.</p>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        @csrf
        @if($testimonial->exists) @method('PUT') @endif
        <main class="space-y-5">
            <section class="rounded-2xl border bg-white p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label class="text-xs font-bold">Name *</label><input name="name" value="{{ old('name', $testimonial->name) }}" required maxlength="120" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-base font-bold"></div>
                    <div><label class="text-xs font-bold">Role</label><input name="role" value="{{ old('role', $testimonial->role) }}" maxlength="80" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm" placeholder="Tenant, Owner, Broker"></div>
                    <div><label class="text-xs font-bold">City</label><input name="city" value="{{ old('city', $testimonial->city) }}" maxlength="120" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="text-xs font-bold">Rating *</label><select name="rating" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm">@for($i=5;$i>=1;$i--)<option value="{{ $i }}" @selected((int) old('rating', $testimonial->rating ?: 5) === $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>@endfor</select></div>
                </div>
                <div class="mt-5"><label class="text-xs font-bold">Review message *</label><textarea name="message" rows="7" required maxlength="800" class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old('message', $testimonial->message) }}</textarea></div>
            </section>
        </main>
        <aside class="space-y-4">
            <section class="rounded-2xl border bg-white p-5">
                <h2 class="text-sm font-extrabold">Publishing</h2>
                <div class="mt-4 space-y-4">
                    <div><label class="text-xs font-bold">Status</label><select name="status" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"><option value="pending" @selected(old('status',$testimonial->status)==='pending')>Pending</option><option value="active" @selected(old('status',$testimonial->status ?: 'active')==='active')>Active</option><option value="inactive" @selected(old('status',$testimonial->status)==='inactive')>Inactive</option></select></div>
                    <div><label class="text-xs font-bold">Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
                    <div>
                        <label class="text-xs font-bold">Avatar</label>
                        @if($testimonial->avatar_url)<img src="{{ $testimonial->avatar_url }}" alt="{{ $testimonial->name }}" class="mt-2 h-20 w-20 rounded-2xl object-cover">@endif
                        <input type="file" name="avatar" accept="image/*" class="mt-3 block w-full rounded-xl border border-slate-200 p-2 text-xs">
                        <p class="mt-1 text-[10px] text-slate-400">Optional JPG or PNG, max 2 MB.</p>
                    </div>
                </div>
                <button class="mt-5 w-full rounded-xl admin-theme-bg py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>{{ $testimonial->exists ? 'Save Review' : 'Create Review' }}</button>
                <a href="{{ route('admin.testimonials.index') }}" class="mt-2 flex h-11 items-center justify-center rounded-xl border text-sm font-bold">Cancel</a>
            </section>
        </aside>
    </form>
</div>
@endsection
