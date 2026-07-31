<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRoleController extends Controller
{
    public function index() { return view('admin.roles.index', ['roles'=>AdminRole::withCount('staff')->orderBy('name')->get(), 'catalog'=>config('admin_permissions.catalog')]); }
    public function create() { return view('admin.roles.create'); }
    public function store(Request $request)
    {
        $request->validate(['name'=>'unique:admin_roles,name']);
        $data=$this->validated($request); $data['slug']=Str::slug($data['name'],'_'); $data['is_system']=false; AdminRole::create($data);
        return back()->with('success','Role created.');
    }
    public function update(Request $request, AdminRole $role)
    {
        if ($role->slug === 'super_admin') return back()->withErrors(['role'=>'Super Admin permissions cannot be reduced.']);
        $role->update($this->validated($request)); return back()->with('success','Role permissions updated.');
    }
    private function validated(Request $request): array
    {
        $data=$request->validate(['name'=>'required|string|max:80','description'=>'nullable|string|max:255','permissions'=>'array','permissions.*'=>'in:'.implode(',',array_keys(config('admin_permissions.catalog')))]);
        $permissions = $data['permissions'] ?? [];
        foreach (['listings','people','support','finance','content','reports'] as $module) {
            if (in_array($module.'.manage', $permissions, true) && !in_array($module.'.view', $permissions, true)) {
                $permissions[] = $module.'.view';
            }
        }
        if ($permissions && !in_array('dashboard.view', $permissions, true)) $permissions[] = 'dashboard.view';
        $data['permissions'] = array_values(array_unique($permissions));
        return $data;
    }
}
