<?php

use App\Http\Middleware\AdminPermission;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function employeeWithPermissions(array $permissions): User
{
    $role = AdminRole::create([
        'name' => 'Test employee role',
        'slug' => 'test_employee_'.str()->random(8),
        'permissions' => $permissions,
        'is_system' => false,
    ]);

    return User::factory()->create([
        'role' => 'admin',
        'admin_role_id' => $role->id,
        'is_staff_active' => true,
    ]);
}

test('employee cannot open an admin module outside the assigned role', function () {
    $employee = employeeWithPermissions(['dashboard.view', 'support.view']);

    $this->actingAs($employee)
        ->get(route('admin.settings'))
        ->assertForbidden();
});

test('reports view permission cannot delete analytics history', function () {
    $employee = employeeWithPermissions(['reports.view']);

    $this->actingAs($employee)
        ->delete(route('admin.analytics.logs.all'))
        ->assertForbidden();
});

test('reports manage permission allows analytics history management', function () {
    $employee = employeeWithPermissions(['reports.view', 'reports.manage']);

    $this->actingAs($employee)
        ->delete(route('admin.analytics.logs.all'))
        ->assertRedirect();
});

test('inactive employee is denied even when the role has permission', function () {
    $employee = employeeWithPermissions(['dashboard.view']);
    $employee->update(['is_staff_active' => false]);

    $this->actingAs($employee)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin api enforces the same reports manage permission', function () {
    $employee = employeeWithPermissions(['reports.view']);
    Sanctum::actingAs($employee, ['admin']);

    $this->deleteJson('/api/v1/admin/search-logs')
        ->assertForbidden();
});

test('admin api protects search analytics with reports permission', function () {
    $employee = employeeWithPermissions(['dashboard.view']);
    Sanctum::actingAs($employee, ['admin']);

    $this->getJson('/api/v1/admin/search-analytics')
        ->assertForbidden();
});

test('admin api protects complaint options with support permission', function () {
    $employee = employeeWithPermissions(['dashboard.view']);
    Sanctum::actingAs($employee, ['admin']);

    $this->getJson('/api/v1/admin/complaint-options')
        ->assertForbidden();
});

test('api role creation adds reports view when reports manage is selected', function () {
    $employee = employeeWithPermissions(['staff.manage']);
    Sanctum::actingAs($employee, ['admin']);

    $this->postJson('/api/v1/admin/roles', [
        'name' => 'Analytics cleanup role',
        'permissions' => ['reports.manage'],
    ])->assertCreated();

    expect(AdminRole::where('name', 'Analytics cleanup role')->firstOrFail()->permissions)
        ->toContain('dashboard.view', 'reports.view', 'reports.manage');
});

test('web dashboard only renders modules assigned to the employee role', function () {
    $employee = employeeWithPermissions(['dashboard.view', 'support.view']);

    $this->actingAs($employee)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Support desk')
        ->assertSee('Support tickets')
        ->assertSee('Recent support tickets')
        ->assertDontSee('Add owner')
        ->assertDontSee('Platform revenue')
        ->assertDontSee('Active listings');
});

test('admin api dashboard does not return unauthorized module data', function () {
    $employee = employeeWithPermissions(['dashboard.view', 'support.view']);
    Sanctum::actingAs($employee, ['admin']);

    $this->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.access.support', true)
        ->assertJsonPath('data.access.finance', false)
        ->assertJsonMissingPath('data.modules.finance')
        ->assertJsonMissingPath('data.modules.listings')
        ->assertJsonPath('data.available_actions.0', 'support.open')
        ->assertJsonStructure(['data' => ['modules' => ['support' => ['open_complaints', 'unread_contacts']]]]);
});

test('full access dashboard renders every module summary', function () {
    $employee = employeeWithPermissions(array_keys(config('admin_permissions.catalog')));

    $this->actingAs($employee)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Active listings')
        ->assertSee('property owners')
        ->assertSee('Support desk')
        ->assertSee('Platform revenue')
        ->assertSee('Website content')
        ->assertSee('Activity today')
        ->assertSee('Recent payments')
        ->assertSee('Your work tools');
});

test('every protected admin action has an explicit permission mapping', function () {
    $middleware = new AdminPermission;
    $permissionFor = new ReflectionMethod($middleware, 'permissionFor');
    $unmapped = [];

    foreach (app('router')->getRoutes() as $route) {
        $uri = $route->uri();
        if (!str_starts_with($uri, 'admin/') && !str_starts_with($uri, 'api/v1/admin/')) {
            continue;
        }
        if (!collect($route->gatherMiddleware())->contains(
            fn ($item) => $item === 'admin.permission' || $item === AdminPermission::class
        )) {
            continue;
        }

        foreach (array_diff($route->methods(), ['HEAD']) as $httpMethod) {
            $request = Request::create('/'.$uri, $httpMethod);
            $request->setRouteResolver(fn () => $route);
            if (!$permissionFor->invoke($middleware, $request)) {
                $unmapped[] = $httpMethod.' '.$uri;
            }
        }
    }

    expect($unmapped)->toBe([
        'GET api/v1/admin/auth/me',
        'POST api/v1/admin/auth/logout',
    ]);
});
