<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'roles' => AdminRole::withCount('staff')->orderBy('name')->get(),
            'catalog' => config('admin_permissions.catalog')
        ]);
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name'], '_');
        $data['is_system'] = false;
        AdminRole::create($data);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$data['name']}' created successfully.");
    }

    public function update(Request $request, AdminRole $role)
    {
        if ($role->slug === 'super_admin') {
            return back()->withErrors(['role' => 'Super Admin permissions cannot be reduced.']);
        }

        $data = $this->validated($request, $role);
        $role->update($data);

        return back()->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(AdminRole $role)
    {
        if ($role->is_system || $role->slug === 'super_admin') {
            return back()->withErrors(['role' => 'System roles cannot be deleted.']);
        }

        if ($role->staff()->count() > 0) {
            return back()->withErrors(['role' => "Cannot delete '{$role->name}' because {$role->staff()->count()} staff member(s) are assigned to it. Please reassign them in Staff Management first."]);
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$name}' deleted successfully.");
    }

    private function validated(Request $request, ?AdminRole $role = null): array
    {
        $nameRule = $role
            ? ['required', 'string', 'max:80', \Illuminate\Validation\Rule::unique('admin_roles', 'name')->ignore($role->id)]
            : ['required', 'string', 'max:80', 'unique:admin_roles,name'];

        $data = $request->validate([
            'name' => $nameRule,
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['in:' . implode(',', array_keys(config('admin_permissions.catalog')))],
        ]);

        $permissions = $data['permissions'] ?? [];
        foreach (['listings', 'people', 'support', 'finance', 'content', 'reports', 'brokers'] as $module) {
            if (in_array($module . '.manage', $permissions, true) && !in_array($module . '.view', $permissions, true)) {
                $permissions[] = $module . '.view';
            }
        }
        if ($permissions && !in_array('dashboard.view', $permissions, true)) {
            $permissions[] = 'dashboard.view';
        }

        $data['permissions'] = array_values(array_unique($permissions));
        return $data;
    }
}
