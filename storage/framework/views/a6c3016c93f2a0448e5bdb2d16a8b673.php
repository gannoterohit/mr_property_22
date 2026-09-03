<?php $__env->startSection('title', 'Admin Staff'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="space-y-6 p-5 lg:p-7" x-data="{ open: <?php echo e($errors->any() ? 'true' : 'false'); ?>, edit: null }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest admin-theme-text">Access control</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Admin Staff</h1>
            <p class="mt-1 text-sm text-slate-500">Create staff accounts, assign roles, pause access and restore deleted staff.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (isset($component)) { $__componentOriginalc04607b3e34baec54ff19a10efaf8c10 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04607b3e34baec54ff19a10efaf8c10 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-actions','data' => ['dataset' => 'staff']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['dataset' => 'staff']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04607b3e34baec54ff19a10efaf8c10)): ?>
<?php $attributes = $__attributesOriginalc04607b3e34baec54ff19a10efaf8c10; ?>
<?php unset($__attributesOriginalc04607b3e34baec54ff19a10efaf8c10); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04607b3e34baec54ff19a10efaf8c10)): ?>
<?php $component = $__componentOriginalc04607b3e34baec54ff19a10efaf8c10; ?>
<?php unset($__componentOriginalc04607b3e34baec54ff19a10efaf8c10); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['type' => 'button','variant' => 'primary','icon' => 'fa-user-plus','@click' => 'open = true; edit = null']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'primary','icon' => 'fa-user-plus','@click' => 'open = true; edit = null']); ?>Add staff member <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="grid gap-3 sm:grid-cols-4">
        <?php $__currentLoopData = [
            ['Total staff', $staffStats['total'], 'fa-users-gear', 'admin-theme-soft'],
            ['Active', $staffStats['active'], 'fa-user-check', 'bg-emerald-50 text-emerald-600'],
            ['Disabled', $staffStats['disabled'], 'fa-user-lock', 'bg-amber-50 text-amber-600'],
            ['Deleted', $staffStats['deleted'], 'fa-trash-arrow-up', 'bg-red-50 text-red-600'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $icon, $tone]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl <?php echo e($tone); ?>"><i class="fas <?php echo e($icon); ?>"></i></span>
                <div><p class="text-[10px] font-bold uppercase text-slate-400"><?php echo e($label); ?></p><p class="text-xl font-extrabold text-slate-900"><?php echo e($value); ?></p></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <form method="GET" class="staff-filters rounded-2xl border bg-white p-4 shadow-sm">
        <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Search name, email or phone..." class="h-10 rounded-xl text-xs">
        <select name="admin_role_id" class="h-10 rounded-xl text-xs">
            <option value="">All roles</option>
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($role->id); ?>" <?php if(request('admin_role_id')==$role->id): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="status" class="h-10 rounded-xl text-xs">
            <option value="">All status</option>
            <option value="active" <?php if(request('status')==='active'): echo 'selected'; endif; ?>>Active</option>
            <option value="disabled" <?php if(request('status')==='disabled'): echo 'selected'; endif; ?>>Disabled</option>
            <option value="deleted" <?php if(request('status')==='deleted'): echo 'selected'; endif; ?>>Deleted</option>
        </select>
        <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['type' => 'submit','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary']); ?>Apply <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['href' => route('admin.staff.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.staff.index'))]); ?>Reset <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">Staff directory</h2>
                <p class="text-xs text-slate-500"><?php echo e($staff->total()); ?> staff accounts match the current filters</p>
            </div>
            <span class="rounded-full admin-theme-soft px-3 py-1.5 text-[10px] font-extrabold">Page <?php echo e($staff->currentPage()); ?> / <?php echo e(max(1, $staff->lastPage())); ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="staff-table admin-table-base">
                <thead><tr><th>Staff member</th><th>Role</th><th>Status</th><th>Last admin login</th><th>Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $editPayload = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'admin_role_id' => $member->admin_role_id,
                        ];
                    ?>
                    <tr class="<?php echo e($member->trashed() ? 'bg-slate-50 opacity-75' : ''); ?>">
                        <td><div class="font-bold text-slate-900"><?php echo e($member->name); ?> <?php if(auth()->id() === $member->id): ?><span class="text-xs admin-theme-text">(You)</span><?php endif; ?></div><div class="text-xs text-slate-500"><?php echo e($member->email); ?> - <?php echo e($member->phone ?: 'No phone'); ?></div></td>
                        <td><span class="rounded-full admin-theme-soft px-2.5 py-1 text-xs font-bold admin-theme-text"><?php echo e($member->adminRole?->name ?? 'Legacy Super Admin'); ?></span></td>
                        <td>
                            <?php if($member->trashed()): ?>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Deleted</span>
                            <?php elseif(auth()->id() === $member->id): ?>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?php echo e($member->is_staff_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'); ?>"><?php echo e($member->is_staff_active ? 'Active' : 'Disabled'); ?></span>
                            <?php else: ?>
                                <?php if (isset($component)) { $__componentOriginal34999d704fb4480704a28cb78ec57cce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal34999d704fb4480704a28cb78ec57cce = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-toggle','data' => ['active' => $member->is_staff_active,'activeLabel' => 'Active','inactiveLabel' => 'Disabled','action' => route('admin.staff.toggle', $member),'dataLabel' => $member->name,'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->is_staff_active),'active-label' => 'Active','inactive-label' => 'Disabled','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.staff.toggle', $member)),'data-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->name),'method' => 'POST']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal34999d704fb4480704a28cb78ec57cce)): ?>
<?php $attributes = $__attributesOriginal34999d704fb4480704a28cb78ec57cce; ?>
<?php unset($__attributesOriginal34999d704fb4480704a28cb78ec57cce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal34999d704fb4480704a28cb78ec57cce)): ?>
<?php $component = $__componentOriginal34999d704fb4480704a28cb78ec57cce; ?>
<?php unset($__componentOriginal34999d704fb4480704a28cb78ec57cce); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-xs text-slate-500"><?php echo e($member->last_admin_login_at?->format('d M Y, h:i A') ?? 'Not recorded'); ?></td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <?php if($member->trashed()): ?>
                                    <form method="POST" action="<?php echo e(route('admin.staff.restore', $member->id)); ?>" class="admin-confirm" data-confirm-title="Restore <?php echo e($member->name); ?>?" data-confirm-text="This staff account will regain access if it is active." data-confirm-button="Yes, restore">
                                        <?php echo csrf_field(); ?>
                                        <?php if (isset($component)) { $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.action-icon','data' => ['variant' => 'view','icon' => 'fa-rotate-left','type' => 'submit','title' => 'Restore']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.action-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'view','icon' => 'fa-rotate-left','type' => 'submit','title' => 'Restore']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $attributes = $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $component = $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <?php if (isset($component)) { $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.action-icon','data' => ['variant' => 'edit','type' => 'button','@click' => 'edit = '.e(\Illuminate\Support\Js::from($editPayload)).'; open = true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.action-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'edit','type' => 'button','@click' => 'edit = '.e(\Illuminate\Support\Js::from($editPayload)).'; open = true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $attributes = $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $component = $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
                                    <?php if(auth()->id() !== $member->id): ?>
                                        <form method="POST" action="<?php echo e(route('admin.staff.destroy', $member)); ?>" class="admin-confirm" data-confirm-title="Delete <?php echo e($member->name); ?>?" data-confirm-text="This staff account will be soft deleted and can be restored later." data-confirm-button="Yes, delete staff">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <?php if (isset($component)) { $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.action-icon','data' => ['variant' => 'delete','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.action-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'delete','type' => 'submit']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $attributes = $__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__attributesOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6)): ?>
<?php $component = $__componentOriginalce775163fad06cbddf8cf4fe4b8259d6; ?>
<?php unset($__componentOriginalce775163fad06cbddf8cf4fe4b8259d6); ?>
<?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="p-12 text-center text-sm text-slate-500">No staff accounts found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($staff->hasPages()): ?><div class="border-t p-4"><?php echo e($staff->links()); ?></div><?php endif; ?>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div @click.outside="open = false" class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas" :class="edit ? 'fa-user-pen' : 'fa-user-plus'"></i></span><div><h2 class="text-lg font-extrabold" x-text="edit ? 'Edit staff member' : 'Create staff member'"></h2><p class="text-xs text-slate-500" x-text="edit ? 'Update profile, role or password.' : 'Create secure access for a team member.'"></p></div></div><button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-lg text-slate-500">&times;</button></div>
            <form method="POST" :action="edit ? '<?php echo e(url('/admin/staff')); ?>/' + edit.id : '<?php echo e(route('admin.staff.store')); ?>'" class="max-h-[75vh] overflow-y-auto">
                <?php echo csrf_field(); ?>
                <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-5 p-5">
                    <section><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Basic information</h3><p class="text-[11px] text-slate-500">Use the employee's real contact details.</p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Full name *</label><input name="name" required :value="edit?.name || ''" placeholder="Employee name" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Phone number</label><input name="phone" :value="edit?.phone || ''" placeholder="+91 98765 43210" class="mt-1 w-full rounded-xl"></div></div><div class="mt-4"><label class="text-xs font-bold">Official email *</label><input type="email" name="email" required :value="edit?.email || ''" placeholder="staff@apnanest.com" class="mt-1 w-full rounded-xl"></div></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900">Access role</h3><p class="text-[11px] text-slate-500">The role decides which admin modules this person can open.</p></div><select name="admin_role_id" required class="w-full rounded-xl bg-slate-50"><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role->id); ?>" :selected="edit?.admin_role_id == <?php echo e($role->id); ?>"><?php echo e($role->name); ?> - <?php echo e($role->description); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><a href="<?php echo e(route('admin.roles.index')); ?>" class="mt-2 inline-flex text-[11px] font-bold admin-theme-text"><i class="fas fa-arrow-up-right-from-square mr-1"></i>Review role permissions</a></section>
                    <section class="border-t border-slate-100 pt-5"><div class="mb-3"><h3 class="text-sm font-extrabold text-slate-900" x-text="edit ? 'Change password' : 'Set initial password'"></h3><p class="text-[11px] text-slate-500" x-text="edit ? 'Leave both fields blank to keep the current password.' : 'Use at least 8 characters and share it securely.'"></p></div><div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold">Password <span x-show="edit" class="text-slate-400">(optional)</span></label><input type="password" name="password" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div><div><label class="text-xs font-bold">Confirm password</label><input type="password" name="password_confirmation" :required="!edit" autocomplete="new-password" class="mt-1 w-full rounded-xl"></div></div></section>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4"><?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['type' => 'button','@click' => 'open = false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'open = false']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['type' => 'submit','variant' => 'primary','icon' => 'fa-floppy-disk']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','icon' => 'fa-floppy-disk']); ?><span x-text="edit ? 'Save changes' : 'Create staff account'"></span> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?></div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin-shared.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-list.css')); ?>">

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\apnanest\resources\views/admin/staff/index.blade.php ENDPATH**/ ?>