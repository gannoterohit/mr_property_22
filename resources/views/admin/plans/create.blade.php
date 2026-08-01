@extends('layouts.admin')

@section('title', 'Create Subscription Plan')

@section('admin-content')
<div class="admin-plan-page space-y-5">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.plans.index') }}" class="admin-theme-text text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>Subscription plans</a>
            <p class="admin-theme-text mt-3 text-[10px] font-extrabold uppercase tracking-[.2em]">Finance & Plans</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Create Subscription Plan</h1>
            <p class="text-sm text-slate-500">Define credits, pricing and plan benefits for users or owners.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-plan-grid">
        @csrf

        <main class="admin-plan-card p-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="admin-theme-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas fa-tags"></i></span>
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
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="admin-plan-field pl-9" placeholder="e.g. Gold Monthly Pack">
                    </div>
                </div>

                <div>
                    <label for="price" class="block text-xs font-bold text-slate-700">Price (&#8377;)</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">&#8377;</span>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="0.01" class="admin-plan-field pl-9" placeholder="499">
                    </div>
                </div>

                <div>
                    <label for="duration_days" class="block text-xs font-bold text-slate-700">Duration (Days)</label>
                    <div class="relative mt-2">
                        <i class="fas fa-calendar-alt pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days') }}" required min="1" class="admin-plan-field pl-9" placeholder="30">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Validity period in days.</p>
                </div>

                <div>
                    <label for="type" class="block text-xs font-bold text-slate-700">Plan Type</label>
                    <select name="type" id="type" onchange="toggleLimitFields()" class="admin-plan-field mt-2">
                        <option value="user" @selected(old('type', 'user') === 'user')>User (Contact Unlocks)</option>
                        <option value="owner" @selected(old('type') === 'owner')>Owner (Room Listings)</option>
                    </select>
                </div>

                <div>
                    <div id="contacts_limit_group">
                        <label for="contacts_limit" class="block text-xs font-bold text-slate-700">Contact Unlocks Limit <span class="font-medium text-slate-400">(-1 for unlimited)</span></label>
                        <input type="number" name="contacts_limit" id="contacts_limit" value="{{ old('contacts_limit', 5) }}" min="-1" class="admin-plan-field mt-2" placeholder="-1 for Unlimited">
                    </div>
                    <div id="listing_limit_group" class="hidden">
                        <label for="listing_limit" class="block text-xs font-bold text-slate-700">Listing Limit <span class="font-medium text-slate-400">(-1 for unlimited)</span></label>
                        <input type="number" name="listing_limit" id="listing_limit" value="{{ old('listing_limit') }}" min="-1" class="admin-plan-field mt-2" placeholder="-1 for Unlimited">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700">Plan Benefits</label>
                    <div id="benefits-container" class="mt-2 space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="text" name="benefits[]" class="admin-plan-field h-10" placeholder="e.g. 24/7 Support">
                            <button type="button" onclick="addBenefitField()" class="admin-theme-soft flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" title="Add benefit"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Add key benefits to display on the plan card.</p>
                </div>
            </div>
        </main>

        <aside class="space-y-4">
            <section class="admin-plan-card p-5">
                <h2 class="text-sm font-extrabold text-slate-950">Publishing</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">Plan will be available after creation when active status is enabled from the plans list.</p>
                <button type="submit" class="admin-theme-bg mt-5 flex h-11 w-full items-center justify-center rounded-xl text-sm font-bold">
                    <i class="fas fa-plus mr-2"></i>Create Plan
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
