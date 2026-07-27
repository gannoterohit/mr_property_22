<?php

use App\Models\User;
use App\Models\Otp;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $otp = Otp::generate($user->email);

    $response = $this->postJson('/verify-login-otp', [
        'email' => $user->email,
        'otp' => $otp,
    ]);

    $this->assertAuthenticated();
    $response->assertOk()->assertJsonPath('success', true);
});

test('users can not authenticate with invalid otp', function () {
    $user = User::factory()->create();

    $this->postJson('/verify-login-otp', [
        'email' => $user->email,
        'otp' => '000000',
    ])->assertUnauthorized();

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
