@extends('layouts.admin')
@section('title', 'Admin Staff')

@section('admin-content')
<div class="space-y-6 p-5 lg:p-7" x-data="{ open: false, edit: null }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest admin-theme-text">Access control</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Admin Staff</h1>
            <p class="mt-1 text-sm text-slate-500">Create staff accounts, assign roles and pause access immediately.</p>
        </div>
        <button type="button" @click="open = true; edit = null" class="rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white">
            <i class="fas fa-user-plus mr-2"></i>Add staff member
        </button>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-users-gear"></i></span><div><p class="text-[10px] font-bold uppercase text-slate-400">Total staff</p><p class="text-xl font-extrabold text-slate-900">{{ $staff->count() }}</p></div></div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-user-check"></i></span><div><p class="text-[10px] font-bold uppercase text-slate-400">Active</p><p class="text-xl font-extrabold text-slate-900">{{ $staff->where('is_staff_active', true)->count() }}</p></div></div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-key"></i></span><div><p class="text-[10px] font-bold uppercase text-slate-400">Available roles</p><p class="text-xl font-extrabold text-slate-900">{{ $roles->count() }}</p></div></div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr><th>Staff member</th><th>Role</th><th>Status</th><th>Last admin login</th><th>Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($staff as $member)
                    @php
                        $editPayload = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'admin_role_id' => $member->admin_role_id,
                        ];
                    @endphp
                    <tr>
                        <td class="px-5"><div class="font-bold text-slate-900">{{ $member->name }} @if(auth()->id() === $member->id)<span class="text-xs admin-theme-text">(You)</span>@endif</div><div class="text-xs text-slate-500">{{ $member->email }} · {{ $member->phone ?: 'No phone' }}</div></td>
                        <td class="px-5"><span class="rounded-full admin-theme-soft px-2.5 py-1 text-xs font-bold admin-theme-text">{{ $member->adminRole?->name ?? 'Legacy Super Admin' }}</span></td>
                        <td class="px-5"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $member->is_staff_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $member->is_staff_active ? 'Active' : 'Disabled' }}</span></td>
                        <td class="px-5 text-xs text-slate-500">{{ $member->last_admin_login_at?->format('d M Y, h:i A') ?? 'Not recorded' }}</td>
                        <td class="px-5"><div class="flex gap-2">
                            <x-admin.action-icon variant="edit" type="button" @click='edit = {{ \Illuminate\Support\Js::from($editPayload) }}; open = true' />
                            @if(auth()->id() !== $member->id)<form method="POST" action="{{ route('admin.staff.toggle', $member) }}">@csrf<button class="h-9 rounded-lg px-3 text-xs font-bold {{ $member->is_staff_active ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">{{ $member->is_staff_active ? 'Disable' : 'Enable' }}</button></form>@endif
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div @click.outside="open = false" class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-bg"><i class="fas" :class="edit ? 'fa-user-pen' : 'fa-user-plus'"></i></span><div><h2 class="text-lg font-extrabold" x-text="edit ? 'Edit staff member' : 'Create staff member'"></h2><p class="text-xs text-slate-500" x-text="edit ? 'Update profile, role or password.' : 'Create secure access for a team member.'"></p></div></div><button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-lg text-slate-500">&times;</button></div>
            <form method="POST" :action="edit ? '{{ url('/admin/staff') }}/' + edit.id : '{{ route('admin.staff.store') }}'" class="max-h-[75vh] overflow-y-auto">
                @csrf
                <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-5 p-5">
                    <section><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Basic information</h3><p class="text-[11px] text-slate-500">Use the employee's real contact details.</p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Full name *</label><input name="name" required :value="edit?.name || ''" placeholder="Employee name" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Phone number</label><input name="phone" :value="edit?.phone || ''" placeholder="+91 98765 43210" class="mt-1 w-full rounded-xl"></div></div><div class="mt-4"><label class="text-xs font-bold">Official email *</label><input type="email" name="email" required :value="edit?.email || ''" placeholder="staff@apnanest.com" class="mt-1 w-full rounded-xl"></div></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Access role</h3><p class="text-[11px] text-slate-500">The role decides which admin modules this person can open.</p></div><select name="admin_role_id" required class="w-full rounded-xl bg-slate-50">@foreach($roles as $role)<option value="{{ $role->id }}" :selected="edit?.admin_role_id == {{ $role->id }}">{{ $role->name }} — {{ $role->description }}</option>@endforeach</select><a href="{{ route('admin.roles.index') }}" class="mt-2 inline-flex text-[11px] font-bold admin-theme-text"><i class="fas fa-arrow-up-right-from-square mr-1"></i>Review role permissions</a></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900" x-text="edit ? 'Change password' : 'Set initial password'"></h3><p class="text-[11px] text-slate-500" x-text="edit ? 'Leave both fields blank to keep the current password.' : 'Use at least 8 characters and share it securely.'"></p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Password <span x-show="edit" class="text-slate-400">(optional)</span></label><input type="password" name="password" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Confirm password</label><input type="password" name="password_confirmation" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div></div></section>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4"><button type="button" @click="open = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600">Cancel</button><button class="rounded-xl admin-theme-bg px-5 py-2.5 text-xs font-bold text-white"><i class="fas fa-floppy-disk mr-2"></i><span x-text="edit ? 'Save changes' : 'Create staff account'"></span></button></div>
            </form>
        </div>
    </div>
</div>
@endsection
