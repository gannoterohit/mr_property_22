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

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => $roles,
            'staffStats' => [
                'total' => User::where('role', 'admin')->count(),
                'active' => User::where('role', 'admin')->where('is_staff_active', true)->count(),
                'disabled' => User::where('role', 'admin')->where('is_staff_active', false)->count(),
                'deleted' => User::onlyTrashed()->where('role', 'admin')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data += ['role' => 'admin', 'is_verified' => true, 'is_staff_active' => true, 'email_verified_at' => now()];
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return back()->with('success', 'Staff account created.');
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->role === 'admin', 404);

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
}
