<?php $__env->startPush('styles'); ?>
<link rel="preload" href="<?php echo e(asset('css/admin.css')); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>"></noscript>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('sweetalert'); ?>
<script src="<?php echo e(asset('assets/js/sweetalert2.min.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form.admin-confirm');
    if (!form || form.dataset.confirmed === '1') return;

    event.preventDefault();
    const result = await Swal.fire({
        title: form.dataset.confirmTitle || 'Confirm this action?',
        text: form.dataset.confirmText || 'Please confirm before continuing.',
        icon: form.dataset.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmButton || 'Yes, continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: form.dataset.confirmColor || '#dc2626',
        reverseButtons: true,
        focusCancel: true,
    });

    if (result.isConfirmed) {
        form.dataset.confirmed = '1';
        form.submit();
    }
});

// Admin Notification Bell & Mark Read Interactivity
document.addEventListener('DOMContentLoaded', () => {
    const bellBtn = document.getElementById('adminNotificationBell');
    const dropdown = document.getElementById('adminNotificationDropdown');
    const badge = document.getElementById('adminNotificationBadge');
    const dropdownUnreadBadge = document.getElementById('dropdownUnreadBadge');
    const markAllBtn = document.getElementById('markAllNotificationsReadBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';

    if (bellBtn && dropdown) {
        bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    const updateUnreadBadge = (count) => {
        if (badge) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.toggle('hidden', count <= 0);
        }
        if (dropdownUnreadBadge) {
            dropdownUnreadBadge.textContent = count + ' new';
            dropdownUnreadBadge.classList.toggle('hidden', count <= 0);
        }
        if (markAllBtn && count <= 0) {
            markAllBtn.style.display = 'none';
        }
        document.querySelectorAll('.sidebar-unread-badge').forEach(b => {
            if (count <= 0) b.classList.add('hidden');
        });
    };

    // Mark single notification read
    document.querySelectorAll('.mark-single-read-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.dataset.id;
            try {
                const res = await fetch(`/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const item = btn.closest('.admin-notification-item');
                    if (item) {
                        item.classList.remove('bg-indigo-50/30');
                        item.classList.add('opacity-75');
                    }
                    btn.remove();
                    updateUnreadBadge(data.unread_count);
                }
            } catch (err) {
                console.error('Notification error:', err);
            }
        });
    });

    // Mark all notifications read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('<?php echo e(route("admin.notifications.markAllRead")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    document.querySelectorAll('.admin-notification-item').forEach(item => {
                        item.classList.remove('bg-indigo-50/30');
                        item.classList.add('opacity-75');
                    });
                    document.querySelectorAll('.mark-single-read-btn').forEach(b => b.remove());
                    updateUnreadBadge(0);
                }
            } catch (err) {
                console.error('Mark all notifications error:', err);
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-shell">
    <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[.16em] font-bold text-slate-400">Administration</p>
                <h1 class="text-base font-bold text-slate-800 truncate"><?php echo $__env->yieldContent('title', 'Admin Panel'); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('home')); ?>" target="_blank" class="admin-theme-link hidden sm:flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 rounded-lg transition">
                    <i class="fas fa-external-link-alt"></i> View website
                </a>

                <?php
                    $unreadNotificationsCount = \App\Models\AdminNotification::where('is_read', false)->count();
                    $recentNotifications = \App\Models\AdminNotification::where('is_read', false)->latest()->take(10)->get();
                ?>

                <!-- Notification Bell Dropdown -->
                <div class="relative" id="adminNotificationDropdownRoot">
                    <button type="button" id="adminNotificationBell" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 shadow-sm" aria-label="View notifications">
                        <i class="fas fa-bell text-sm"></i>
                        <span id="adminNotificationBadge" class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-extrabold text-white shadow <?php echo e($unreadNotificationsCount > 0 ? '' : 'hidden'); ?>">
                            <?php echo e($unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount); ?>

                        </span>
                    </button>

                    <div id="adminNotificationDropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white shadow-2xl z-50 overflow-hidden">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-bell text-slate-500 text-xs"></i>
                                <span class="text-xs font-bold text-slate-800">Notifications</span>
                                <span id="dropdownUnreadBadge" class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-extrabold text-red-600 <?php echo e($unreadNotificationsCount > 0 ? '' : 'hidden'); ?>">
                                    <?php echo e($unreadNotificationsCount); ?> new
                                </span>
                            </div>
                            <?php if($unreadNotificationsCount > 0): ?>
                                <button type="button" id="markAllNotificationsReadBtn" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    Mark all read
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100" id="notificationItemsList">
                            <?php $__empty_1 = true; $__currentLoopData = $recentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="admin-notification-item flex items-start gap-3 p-3 transition hover:bg-slate-50 <?php echo e($notif->is_read ? 'opacity-75' : 'bg-indigo-50/30'); ?>" data-id="<?php echo e($notif->id); ?>">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg <?php echo e($notif->is_read ? 'bg-slate-100 text-slate-400' : 'admin-theme-soft'); ?>">
                                        <i class="fas <?php echo e($notif->icon ?: 'fa-bell'); ?> text-xs"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <?php if($notif->link): ?>
                                                <a href="<?php echo e(route('admin.notifications.markRead', $notif->id)); ?>" class="text-xs font-bold text-slate-800 hover:text-indigo-600 truncate block">
                                                    <?php echo e($notif->title); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-xs font-bold text-slate-800 truncate block"><?php echo e($notif->title); ?></span>
                                            <?php endif; ?>
                                            <span class="text-[9px] font-semibold text-slate-400 whitespace-nowrap"><?php echo e($notif->created_at->diffForHumans(null, true)); ?></span>
                                        </div>
                                        <?php if($notif->message): ?>
                                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5 leading-snug"><?php echo e($notif->message); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!$notif->is_read): ?>
                                        <button type="button" class="mark-single-read-btn text-slate-300 hover:text-indigo-600 p-1 transition shrink-0" data-id="<?php echo e($notif->id); ?>" title="Mark as read">
                                            <i class="fas fa-check-circle text-xs"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="p-6 text-center text-slate-400">
                                    <i class="fas fa-bell-slash text-2xl mb-2 block text-slate-300"></i>
                                    <p class="text-xs font-medium">No unread notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-2 border-t border-slate-100 bg-slate-50/50 text-center">
                            <a href="<?php echo e(route('admin.notifications.index')); ?>" class="text-xs font-bold text-slate-600 hover:text-indigo-600 transition block py-1">
                                View all notifications <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="h-10 pl-1.5 pr-3 rounded-xl border border-slate-200 bg-white flex items-center gap-2.5 shadow-sm">
                    <?php if(Auth::user()->avatar): ?>
                        <img src="<?php echo e(asset('storage/' . Auth::user()->avatar)); ?>" alt="<?php echo e(Auth::user()->name); ?>" class="w-7 h-7 rounded-lg object-cover ring-1 ring-slate-200">
                    <?php else: ?>
                        <div class="admin-theme-avatar w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm"><?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?></div>
                    <?php endif; ?>
                    <div class="hidden sm:block leading-tight max-w-[140px]">
                        <p class="text-xs font-bold text-slate-800 truncate"><?php echo e(Auth::user()->name); ?></p>
                        <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Administrator</p>
                    </div>
                </div>
            </div>
        </header>
        <?php
            $maintenanceActive = filter_var(\App\Models\Setting::get('maintenance_mode', '0'), FILTER_VALIDATE_BOOLEAN);
            $pausedModules = collect([
                'Registration' => 'registration_enabled',
                'New listings' => 'new_listings_enabled',
                'Payments & unlocks' => 'payments_enabled',
                'Owner panel' => 'owner_panel_enabled',
                'User panel' => 'user_panel_enabled',
            ])->filter(fn ($key) => !filter_var(\App\Models\Setting::get($key, '1'), FILTER_VALIDATE_BOOLEAN))->keys();
        ?>
        <?php if($maintenanceActive || $pausedModules->isNotEmpty()): ?>
            <div class="border-b <?php echo e($maintenanceActive ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-800'); ?> px-5 py-2.5 text-xs font-semibold">
                <div class="mx-auto flex max-w-[1680px] flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span><i class="fas fa-triangle-exclamation mr-2"></i><?php echo e($maintenanceActive ? 'Global maintenance mode is ON.' : 'Temporarily paused: '.$pausedModules->join(', ').'.'); ?></span>
                    <a href="<?php echo e(route('admin.maintenance')); ?>" class="font-extrabold underline underline-offset-2">Manage availability</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="admin-content">
            <?php echo $__env->yieldContent('admin-content'); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\apnanest\resources\views/layouts/admin.blade.php ENDPATH**/ ?>