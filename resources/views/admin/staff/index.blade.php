@extends('layouts.admin')
@section('title', 'Admin Staff')

@section('admin-content')
<div class="space-y-6 p-5 lg:p-7" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }}, edit: null }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest admin-theme-text">Access control</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Admin Staff</h1>
            <p class="mt-1 text-sm text-slate-500">Create staff accounts, assign roles, pause access and restore deleted staff.</p>
        </div>
        <x-admin.button type="button" variant="primary" icon="fa-user-plus" @click="open = true; edit = null">Add staff member</x-admin.button>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-3 sm:grid-cols-4">
        @foreach([
            ['Total staff', $staffStats['total'], 'fa-users-gear', 'admin-theme-soft'],
            ['Active', $staffStats['active'], 'fa-user-check', 'bg-emerald-50 text-emerald-600'],
            ['Disabled', $staffStats['disabled'], 'fa-user-lock', 'bg-amber-50 text-amber-600'],
            ['Deleted', $staffStats['deleted'], 'fa-trash-arrow-up', 'bg-red-50 text-red-600'],
        ] as [$label, $value, $icon, $tone])
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                <div><p class="text-[10px] font-bold uppercase text-slate-400">{{ $label }}</p><p class="text-xl font-extrabold text-slate-900">{{ $value }}</p></div>
            </div>
        @endforeach
    </div>

    <form method="GET" class="staff-filters rounded-2xl border bg-white p-4 shadow-sm">
        <input name="search" value="{{ request('search') }}" placeholder="Search name, email or phone..." class="h-10 rounded-xl text-xs">
        <select name="admin_role_id" class="h-10 rounded-xl text-xs">
            <option value="">All roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(request('admin_role_id')==$role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        <select name="status" class="h-10 rounded-xl text-xs">
            <option value="">All status</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="disabled" @selected(request('status')==='disabled')>Disabled</option>
            <option value="deleted" @selected(request('status')==='deleted')>Deleted</option>
        </select>
        <x-admin.button type="submit" variant="primary">Apply</x-admin.button>
        <x-admin.button :href="route('admin.staff.index')">Reset</x-admin.button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">Staff directory</h2>
                <p class="text-xs text-slate-500">{{ $staff->total() }} staff accounts match the current filters</p>
            </div>
            <span class="rounded-full admin-theme-soft px-3 py-1.5 text-[10px] font-extrabold">Page {{ $staff->currentPage() }} / {{ max(1, $staff->lastPage()) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead><tr><th>Staff member</th><th>Role</th><th>Status</th><th>Last admin login</th><th>Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($staff as $member)
                    @php
                        $editPayload = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'admin_role_id' => $member->admin_role_id,
                        ];
                    @endphp
                    <tr class="{{ $member->trashed() ? 'bg-slate-50 opacity-75' : '' }}">
                        <td><div class="font-bold text-slate-900">{{ $member->name }} @if(auth()->id() === $member->id)<span class="text-xs admin-theme-text">(You)</span>@endif</div><div class="text-xs text-slate-500">{{ $member->email }} - {{ $member->phone ?: 'No phone' }}</div></td>
                        <td><span class="rounded-full admin-theme-soft px-2.5 py-1 text-xs font-bold admin-theme-text">{{ $member->adminRole?->name ?? 'Legacy Super Admin' }}</span></td>
                        <td>
                            @if($member->trashed())
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Deleted</span>
                            @elseif(auth()->id() === $member->id)
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $member->is_staff_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $member->is_staff_active ? 'Active' : 'Disabled' }}</span>
                            @else
                                <x-admin.status-toggle :active="$member->is_staff_active" active-label="Active" inactive-label="Disabled" :action="route('admin.staff.toggle', $member)" :data-label="$member->name" method="POST" />
                            @endif
                        </td>
                        <td class="text-xs text-slate-500">{{ $member->last_admin_login_at?->format('d M Y, h:i A') ?? 'Not recorded' }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @if($member->trashed())
                                    <form method="POST" action="{{ route('admin.staff.restore', $member->id) }}" class="admin-confirm" data-confirm-title="Restore {{ $member->name }}?" data-confirm-text="This staff account will regain access if it is active." data-confirm-button="Yes, restore">
                                        @csrf
                                        <x-admin.action-icon variant="view" icon="fa-rotate-left" type="submit" title="Restore" />
                                    </form>
                                @else
                                    <x-admin.action-icon variant="edit" type="button" @click='edit = {{ \Illuminate\Support\Js::from($editPayload) }}; open = true' />
                                    @if(auth()->id() !== $member->id)
                                        <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="admin-confirm" data-confirm-title="Delete {{ $member->name }}?" data-confirm-text="This staff account will be soft deleted and can be restored later." data-confirm-button="Yes, delete staff">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.action-icon variant="delete" type="submit" />
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-12 text-center text-sm text-slate-500">No staff accounts found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())<div class="border-t p-4">{{ $staff->links() }}</div>@endif
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div @click.outside="open = false" class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas" :class="edit ? 'fa-user-pen' : 'fa-user-plus'"></i></span><div><h2 class="text-lg font-extrabold" x-text="edit ? 'Edit staff member' : 'Create staff member'"></h2><p class="text-xs text-slate-500" x-text="edit ? 'Update profile, role or password.' : 'Create secure access for a team member.'"></p></div></div><button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-lg text-slate-500">&times;</button></div>
            <form method="POST" :action="edit ? '{{ url('/admin/staff') }}/' + edit.id : '{{ route('admin.staff.store') }}'" class="max-h-[75vh] overflow-y-auto">
                @csrf
                <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-5 p-5">
                    <section><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Basic information</h3><p class="text-[11px] text-slate-500">Use the employee's real contact details.</p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Full name *</label><input name="name" required :value="edit?.name || ''" placeholder="Employee name" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Phone number</label><input name="phone" :value="edit?.phone || ''" placeholder="+91 98765 43210" class="mt-1 w-full rounded-xl"></div></div><div class="mt-4"><label class="text-xs font-bold">Official email *</label><input type="email" name="email" required :value="edit?.email || ''" placeholder="staff@apnanest.com" class="mt-1 w-full rounded-xl"></div></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Access role</h3><p class="text-[11px] text-slate-500">The role decides which admin modules this person can open.</p></div><select name="admin_role_id" required class="w-full rounded-xl bg-slate-50">@foreach($roles as $role)<option value="{{ $role->id }}" :selected="edit?.admin_role_id == {{ $role->id }}">{{ $role->name }} - {{ $role->description }}</option>@endforeach</select><a href="{{ route('admin.roles.index') }}" class="mt-2 inline-flex text-[11px] font-bold admin-theme-text"><i class="fas fa-arrow-up-right-from-square mr-1"></i>Review role permissions</a></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900" x-text="edit ? 'Change password' : 'Set initial password'"></h3><p class="text-[11px] text-slate-500" x-text="edit ? 'Leave both fields blank to keep the current password.' : 'Use at least 8 characters and share it securely.'"></p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Password <span x-show="edit" class="text-slate-400">(optional)</span></label><input type="password" name="password" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Confirm password</label><input type="password" name="password_confirmation" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div></div></section>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4"><x-admin.button type="button" @click="open = false">Cancel</x-admin.button><x-admin.button type="submit" variant="primary" icon="fa-floppy-disk"><span x-text="edit ? 'Save changes' : 'Create staff account'"></span></x-admin.button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .staff-filters{display:grid;grid-template-columns:minmax(240px,1fr) 220px 160px auto auto;gap:10px;align-items:center}
    .staff-table{width:100%;min-width:1040px;table-layout:fixed}
    .staff-table th,.staff-table td{padding:16px 20px;text-align:left!important;vertical-align:middle!important}
    .staff-table th{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#64748b;white-space:nowrap}
    .staff-table td{height:72px}
    .staff-table th:nth-child(1),.staff-table td:nth-child(1){width:34%}
    .staff-table th:nth-child(2),.staff-table td:nth-child(2){width:18%}
    .staff-table th:nth-child(3),.staff-table td:nth-child(3){width:16%}
    .staff-table th:nth-child(4),.staff-table td:nth-child(4){width:17%}
    .staff-table th:nth-child(5),.staff-table td:nth-child(5){width:15%;text-align:right!important}
    .staff-table td:nth-child(5)>div{justify-content:flex-end}
    @media(max-width:900px){.staff-filters{grid-template-columns:1fr 1fr}.staff-filters button,.staff-filters a{width:100%}}
    @media(max-width:640px){.staff-filters{grid-template-columns:1fr}}
</style>
@endpush
