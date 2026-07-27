<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();
    Cache::put('delete_otp_' . $user->id, '123456', now()->addMinutes(10));

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'otp' => '123456',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertSoftDeleted($user);
});

test('correct otp must be provided to delete account', function () {
    $user = User::factory()->create();
    Cache::put('delete_otp_' . $user->id, '123456', now()->addMinutes(10));

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'otp' => '000000',
        ]);

    $response
        ->assertSessionHas('error', 'Invalid or expired verification code.')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
