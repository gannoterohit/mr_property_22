<?php

use App\Models\Otp;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $otp = Otp::generate('test@example.com');
    $response = $this->postJson('/verify-registration-otp', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'user',
        'otp' => $otp,
    ]);

    $response->assertOk()->assertJsonPath('success', true);
    $this->assertAuthenticated();
});
