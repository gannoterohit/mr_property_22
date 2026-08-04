<?php

use App\Models\Setting;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function apiMember(string $role = 'user'): User
{
    return User::factory()->create([
        'role' => $role,
        'is_staff_active' => true,
        'is_blocked' => false,
    ]);
}

it('exposes every admin controlled module flag through public settings', function () {
    foreach ([
        'maintenance_mode' => '0',
        'registration_enabled' => '1',
        'new_listings_enabled' => '1',
        'payments_enabled' => '1',
        'owner_panel_enabled' => '1',
        'user_panel_enabled' => '1',
        'wallet_enabled' => '0',
        'referral_enabled' => '0',
        'promo_enabled' => '0',
    ] as $key => $value) {
        Setting::set($key, $value);
    }

    $this->getJson('http://localhost/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.module_availability.wallet_enabled', false)
        ->assertJsonPath('data.module_availability.referral_enabled', false)
        ->assertJsonPath('data.module_availability.promo_enabled', false)
        ->assertJsonPath('data.module_availability.subscriptions_enabled', true);
});

it('treats boolean and string feature flags consistently', function () {
    Setting::set('wallet_enabled', true);
    Setting::set('referral_enabled', false);

    expect(Setting::isEnabled('wallet_enabled', true))->toBeTrue()
        ->and(Setting::isEnabled('referral_enabled', true))->toBeFalse();
});

it('blocks disabled wallet and referral API modules', function () {
    Setting::set('wallet_enabled', '0');
    Setting::set('referral_enabled', '0');
    Sanctum::actingAs(apiMember());

    $this->getJson('http://localhost/api/v1/wallet')
        ->assertStatus(503)
        ->assertJsonPath('reason', 'wallet');

    $this->getJson('http://localhost/api/v1/referral-stats')
        ->assertStatus(503)
        ->assertJsonPath('reason', 'referral');
});

it('uses password login for admins and issues an admin scoped token', function () {
    Setting::set('maintenance_mode', '1');
    $admin = apiMember('admin');

    $response = $this->postJson('http://localhost/api/v1/admin/auth/login', [
        'email' => $admin->email,
        'password' => 'password',
        'device_name' => 'test-admin',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.admin.role', 'admin')
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($admin->fresh()->tokens()->latest()->first()->can('admin'))->toBeTrue();
});

it('prevents regular users from accessing owner APIs', function () {
    Setting::set('maintenance_mode', '0');
    Setting::set('user_panel_enabled', '1');
    Setting::set('owner_panel_enabled', '1');
    Sanctum::actingAs(apiMember('user'));

    $this->getJson('http://localhost/api/v1/owner/dashboard')->assertForbidden();
});

it('blocks registration listings and purchases when admin disables them', function () {
    Setting::set('registration_enabled', '0');
    Setting::set('new_listings_enabled', '0');
    Setting::set('payments_enabled', '0');

    $this->postJson('http://localhost/api/v1/auth/register', [])->assertStatus(503)->assertJsonPath('reason', 'registration');
    $this->postJson('http://localhost/api/v1/owner/rooms', [])->assertStatus(503)->assertJsonPath('reason', 'listings');
    $this->postJson('http://localhost/api/v1/subscriptions/purchase', [])->assertStatus(503)->assertJsonPath('reason', 'payments');
});
