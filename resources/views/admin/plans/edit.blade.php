@extends('layouts.admin')

@section('title', 'Edit Subscription Plan')

@push('styles')
<style>
    .admin-plan-page { padding: 24px; }
    .admin-plan-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; align-items: start; }
    .admin-plan-card { border: 1px solid #e2e8f0; border-radius: 16px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .admin-plan-primary { color: var(--admin-primary); }
    .admin-plan-primary-bg { background: var(--admin-primary); color: #fff; }
    .admin-plan-primary-bg:hover { filter: brightness(.94); }
    .admin-plan-soft { background: rgba(var(--admin-primary-rgb), .08); color: var(--admin-primary); }
    .admin-plan-field { height: 44px; width: 100%; border-radius: 12px; border-color: #cbd5e1; font-size: 14px; }
    .admin-plan-field:focus { border-color: var(--admin-primary); box-shadow: 0 0 0 3px rgba(var(--admin-primary-rgb), .14); }
    @media (max-width: 1023px) { .admin-plan-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .admin-plan-page { padding: 16px; } }
</style>
@endpush

@section('admin-content')
<div class="admin-plan-page space-y-5">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.plans.index') }}" class="admin-plan-primary text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>Subscription plans</a>
            <p class="admin-plan-primary mt-3 text-[10px] font-extrabold uppercase tracking-[.2em]">Finance & Plans</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Edit Subscription Plan</h1>
            <p class="text-sm text-slate-500">Update credits, pricing and plan benefits.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="admin-plan-grid">
        @csrf
        @method('PUT')

        <main class="admin-plan-card p-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="admin-plan-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas fa-tags"></i></span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-950">Plan details</h2>
                    <p class="text-xs text-slate-500">This information appears on the pricing card.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700">Plan Name</label>
                    <div class="relative mt-2">
                        <i class="fas fa-tag pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required class="admin-plan-field pl-9">
                    </div>
                </div>

                <div>
                    <label for="price" class="block text-xs font-bold text-slate-700">Price (&#8377;)</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">&#8377;</span>
                        <input type="number" name="price" id="price" value="{{ old('price', $plan->price) }}" required min="0" step="0.01" class="admin-plan-field pl-9">
                    </div>
                </div>

                <div>
                    <label for="duration_days" class="block text-xs font-bold text-slate-700">Duration (Days)</label>
                    <div class="relative mt-2">
                        <i class="fas fa-calendar-alt pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required min="1" class="admin-plan-field pl-9">
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-xs font-bold text-slate-700">Plan Type</label>
                    <select name="type" id="type" onchange="toggleLimitFields()" class="admin-plan-field mt-2">
                        <option value="user" @selected(old('type', $plan->type) === 'user')>User (Contact Unlocks)</option>
                        <option value="owner" @selected(old('type', $plan->type) === 'owner')>Owner (Room Listings)</option>
                    </select>
                </div>

                <div>
                    <div id="contacts_limit_group" class="{{ old('type', $plan->type) === 'user' ? '' : 'hidden' }}">
                        <label for="contacts_limit" class="block text-xs font-bold text-slate-700">Contact Unlocks Limit <span class="font-medium text-slate-400">(-1 for unlimited)</span></label>
                        <input type="number" name="contacts_limit" id="contacts_limit" value="{{ old('contacts_limit', $plan->contacts_limit) }}" min="-1" class="admin-plan-field mt-2">
                    </div>
                    <div id="listing_limit_group" class="{{ old('type', $plan->type) === 'owner' ? '' : 'hidden' }}">
                        <label for="listing_limit" class="block text-xs font-bold text-slate-700">Listing Limit <span class="font-medium text-slate-400">(-1 for unlimited)</span></label>
                        <input type="number" name="listing_limit" id="listing_limit" value="{{ old('listing_limit', $plan->listing_limit) }}" min="-1" class="admin-plan-field mt-2">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700">Plan Benefits</label>
                    <div id="benefits-container" class="mt-2 space-y-2">
                        @foreach((array) old('benefits', $plan->benefits ?? []) as $benefit)
                            @if($benefit !== null && $benefit !== '')
                                <div class="flex items-center gap-2">
                                    <input type="text" name="benefits[]" value="{{ $benefit }}" class="admin-plan-field h-10">
                                    <button type="button" onclick="this.parentElement.remove()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600" title="Remove benefit"><i class="fas fa-trash"></i></button>
                                </div>
                            @endif
                        @endforeach
                        <div class="flex items-center gap-2">
                            <input type="text" name="benefits[]" class="admin-plan-field h-10" placeholder="Add new benefit">
                            <button type="button" onclick="addBenefitField()" class="admin-plan-soft flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" title="Add benefit"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside class="space-y-4">
            <section class="admin-plan-card p-5">
                <h2 class="text-sm font-extrabold text-slate-950">Publishing</h2>
                <dl class="mt-4 space-y-3 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Status</dt><dd class="font-bold text-slate-700">{{ $plan->is_active ? 'Active' : 'Inactive' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Created</dt><dd class="font-bold text-slate-700">{{ $plan->created_at?->format('d M Y') ?? 'Not available' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Updated</dt><dd class="font-bold text-slate-700">{{ $plan->updated_at?->format('d M Y') ?? 'Not available' }}</dd></div>
                </dl>
                <button type="submit" class="admin-plan-primary-bg mt-5 flex h-11 w-full items-center justify-center rounded-xl text-sm font-bold">
                    <i class="fas fa-save mr-2"></i>Update Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="mt-2 flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-600">Cancel</a>
            </section>
        </aside>
    </form>
</div>

<script>
    function toggleLimitFields() {
        const type = document.getElementById('type').value;
        document.getElementById('contacts_limit_group').classList.toggle('hidden', type !== 'user');
        document.getElementById('listing_limit_group').classList.toggle('hidden', type !== 'owner');
    }

    function addBenefitField() {
        const container = document.getElementById('benefits-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <input type="text" name="benefits[]" class="admin-plan-field h-10" placeholder="Benefit description">
            <button type="button" onclick="this.parentElement.remove()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600" title="Remove benefit"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(div);
    }

    toggleLimitFields();
</script>
@endsection
