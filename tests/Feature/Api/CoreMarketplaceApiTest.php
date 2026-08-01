<?php

use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function apiMarketplaceRoom(User $owner, array $overrides = []): Room
{
    return Room::create(array_merge([
        'user_id' => $owner->id,
        'title' => 'API Launch Test Room',
        'description' => 'A verified API test listing.',
        'rent' => 6000,
        'city' => 'Indore',
        'address' => 'API test area',
        'status' => 'active',
        'listing_status' => 'approved',
        'listing_fee_paid' => true,
        'listing_type' => 'owner',
    ], $overrides));
}

it('keeps API free unlock duplicate-safe without consuming credits or wallet', function () {
    Setting::set('unlock_fee_enabled', '0');
    $owner = User::factory()->create(['role' => 'owner', 'phone' => '7777777777']);
    $user = User::factory()->create(['role' => 'user', 'wallet_balance' => 250, 'free_unlocks' => 2]);
    $room = apiMarketplaceRoom($owner);
    Sanctum::actingAs($user);

    $this->postJson("/api/v1/unlock/{$room->id}")
        ->assertOk()
        ->assertJsonPath('data.free_unlock', true)
        ->assertJsonPath('data.contact', '7777777777');
    $this->postJson("/api/v1/unlock/{$room->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Already unlocked');

    expect(Enquiry::where('user_id', $user->id)->where('room_id', $room->id)->count())->toBe(1)
        ->and(Payment::where('user_id', $user->id)->where('type', 'unlock')->count())->toBe(1)
        ->and((float) $user->fresh()->wallet_balance)->toBe(250.0)
        ->and($user->fresh()->free_unlocks)->toBe(2);
});

it('uses an API referral unlock credit before creating a paid order', function () {
    Setting::set('unlock_fee_enabled', '1');
    Setting::set('unlock_fee', '49');
    $owner = User::factory()->create(['role' => 'owner', 'phone' => '6666666666']);
    $user = User::factory()->create(['role' => 'user', 'wallet_balance' => 0, 'free_unlocks' => 1]);
    $room = apiMarketplaceRoom($owner);
    Sanctum::actingAs($user);

    $this->postJson("/api/v1/unlock/{$room->id}")
        ->assertOk()
        ->assertJsonPath('data.free_credit_used', true)
        ->assertJsonPath('data.remaining_credits', 0);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'type' => 'unlock',
        'gateway' => 'free_credit',
        'amount' => 0,
        'status' => 'completed',
    ]);
});

it('returns only real unlocked enquiries in the owner API without seeker contact details', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $seeker = User::factory()->create(['role' => 'user', 'phone' => '5555555555']);
    $room = apiMarketplaceRoom($owner, ['title' => 'Visible API Enquiry']);
    Enquiry::create(['user_id' => $seeker->id, 'room_id' => $room->id, 'unlocked' => true, 'unlocked_at' => now()]);
    Enquiry::create([
        'user_id' => User::factory()->create(['role' => 'user'])->id,
        'room_id' => apiMarketplaceRoom($owner, ['title' => 'Pending API Enquiry'])->id,
        'unlocked' => false,
    ]);
    Sanctum::actingAs($owner);

    $this->getJson('/api/v1/owner/enquiries')
        ->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.room.title', 'Visible API Enquiry')
        ->assertJsonMissing(['phone' => '5555555555'])
        ->assertJsonMissingPath('data.items.0.user.email');
});
