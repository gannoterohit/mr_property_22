<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withTrashed()
            ->where('role', 'admin')
            ->with('adminRole')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%')
                ->orWhere('phone', 'like', '%'.$request->search.'%')))
            ->when($request->filled('admin_role_id'), fn ($q) => $q->where('admin_role_id', $request->integer('admin_role_id')))
            ->when($request->status === 'active', fn ($q) => $q->where('is_staff_active', true)->whereNull('deleted_at'))
            ->when($request->status === 'disabled', fn ($q) => $q->where('is_staff_active', false)->whereNull('deleted_at'))
            ->when($request->status === 'deleted', fn ($q) => $q->onlyTrashed());

        $staff = $query->latest()->paginate(15)->withQueryString();
        $roles = AdminRole::orderBy('name')->get();

        // Workload & Performance stats for each staff member
        $staffIds = $staff->pluck('id')->all();
        $openComplaintsCounts = \App\Models\Complaint::whereIn('assigned_to', $staffIds)
            ->whereNotIn('status', ['resolved', 'rejected', 'closed'])
            ->selectRaw('assigned_to, count(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $resolvedComplaintsCounts = \App\Models\Complaint::whereIn('assigned_to', $staffIds)
            ->where('status', 'resolved')
            ->selectRaw('assigned_to, count(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $weeklyActivitiesCounts = \App\Models\AdminActivityLog::whereIn('actor_id', $staffIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('actor_id, count(*) as total')
            ->groupBy('actor_id')
            ->pluck('total', 'actor_id');

        $staffWorkload = [];
        foreach ($staff as $s) {
            $staffWorkload[$s->id] = [
                'open_tickets' => (int) ($openComplaintsCounts[$s->id] ?? 0),
                'resolved_tickets' => (int) ($resolvedComplaintsCounts[$s->id] ?? 0),
                'weekly_actions' => (int) ($weeklyActivitiesCounts[$s->id] ?? 0),
            ];
        }

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => $roles,
            'staffWorkload' => $staffWorkload,
            'staffStats' => [
                'total' => User::where('role', 'admin')->count(),
                'active' => User::where('role', 'admin')->where('is_staff_active', true)->count(),
                'disabled' => User::where('role', 'admin')->where('is_staff_active', false)->count(),
                'deleted' => User::onlyTrashed()->where('role', 'admin')->count(),
                'total_assigned_open' => \App\Models\Complaint::whereNotNull('assigned_to')->whereNotIn('status', ['resolved', 'rejected', 'closed'])->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->guardSuperAdminAssignment($request);
        $data = $this->validated($request);
        $data += ['role' => 'admin', 'is_verified' => true, 'is_staff_active' => true, 'email_verified_at' => now()];
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return back()->with('success', 'Staff account created.');
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->role === 'admin', 404);
        $this->guardSuperAdminAssignment($request);

        $data = $this->validated($request, $staff);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $staff->update($data);

        return back()->with('success', 'Staff account updated.');
    }

    public function toggle(User $staff)
    {
        abort_unless($staff->role === 'admin', 404);
        abort_if(auth()->id() === $staff->id, 422, 'You cannot disable your own account.');

        $staff->update(['is_staff_active' => ! $staff->is_staff_active]);

        return back()->with('success', $staff->is_staff_active ? 'Staff access enabled.' : 'Staff access disabled.');
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->role === 'admin', 404);
        abort_if(auth()->id() === $staff->id, 422, 'You cannot delete your own account.');

        $staff->delete();

        return back()->with('success', 'Staff account deleted. It can be restored from the Deleted filter.');
    }

    public function restore(int $id)
    {
        $staff = User::onlyTrashed()->where('role', 'admin')->findOrFail($id);
        $staff->restore();

        return back()->with('success', 'Staff account restored.');
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        $passwordRule = $staff ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($staff?->id)],
            'password' => $passwordRule,
            'admin_role_id' => ['required', 'integer', 'exists:admin_roles,id'],
        ]);
    }

    private function guardSuperAdminAssignment(Request $request): void
    {
        $role = AdminRole::find($request->input('admin_role_id'));
        $actorRole = auth()->user()?->adminRole;
        $actorIsSuperAdmin = !$actorRole || $actorRole->slug === 'super_admin';

        abort_if($role?->slug === 'super_admin' && !$actorIsSuperAdmin, 403, 'Only a Super Admin can assign the Super Admin role.');
    }
}
