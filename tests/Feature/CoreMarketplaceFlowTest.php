<?php

use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;

function marketplaceRoom(User $owner, array $overrides = []): Room
{
    return Room::create(array_merge([
        'user_id' => $owner->id,
        'title' => 'Launch Test Room',
        'description' => 'A verified test listing.',
        'rent' => 5000,
        'city' => 'Indore',
        'address' => 'Test area',
        'status' => 'active',
        'listing_status' => 'approved',
        'listing_fee_paid' => true,
        'listing_type' => 'owner',
    ], $overrides));
}

it('unlocks contact for free only once when the fee toggle is off', function () {
    Setting::set('unlock_fee_enabled', '0');
    $owner = User::factory()->create(['role' => 'owner', 'phone' => '9999999999']);
    $user = User::factory()->create(['role' => 'user', 'wallet_balance' => 500, 'free_unlocks' => 3]);
    $room = marketplaceRoom($owner);

    $this->actingAs($user)->postJson(route('unlock.contact', $room))
        ->assertOk()
        ->assertJsonPath('free_unlock', true)
        ->assertJsonPath('contact', '9999999999');

    $this->actingAs($user)->postJson(route('unlock.contact', $room))
        ->assertOk()
        ->assertJsonPath('already_unlocked', true);

    expect(Enquiry::whereBelongsTo($user)->where('room_id', $room->id)->count())->toBe(1)
        ->and(Payment::whereBelongsTo($user)->where('type', 'unlock')->count())->toBe(1)
        ->and((float) $user->fresh()->wallet_balance)->toBe(500.0)
        ->and($user->fresh()->free_unlocks)->toBe(3);
});

it('creates a pending Razorpay payment when paid unlock is enabled', function () {
    Setting::set('unlock_fee_enabled', '1');
    Setting::set('unlock_fee', '49');
    $owner = User::factory()->create(['role' => 'owner', 'phone' => '8888888888']);
    $user = User::factory()->create(['role' => 'user', 'wallet_balance' => 0, 'free_unlocks' => 0]);
    $room = marketplaceRoom($owner);

    $this->actingAs($user)->postJson(route('unlock.contact', $room))
        ->assertOk()
        ->assertJsonPath('amount', '49')
        ->assertJsonStructure(['enquiry_id', 'payment_id']);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'type' => 'unlock',
        'reference_id' => (string) $room->id,
        'amount' => 49,
        'gateway' => 'razorpay',
        'status' => 'pending',
    ]);
    $this->assertDatabaseHas('enquiries', [
        'user_id' => $user->id,
        'room_id' => $room->id,
        'unlocked' => false,
    ]);
});

it('allows only the owner to mark a room rented and available', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $otherOwner = User::factory()->create(['role' => 'owner']);
    $room = marketplaceRoom($owner);

    $this->actingAs($otherOwner)->postJson(route('rooms.markBooked', $room))->assertForbidden();
    $this->actingAs($owner)->postJson(route('rooms.markBooked', $room))->assertOk();
    expect($room->fresh()->status)->toBe('booked');

    Setting::set('listing_fee_enabled', '0');
    $this->actingAs($owner)->postJson(route('rooms.markAvailable', $room), ['payment_method' => 'free'])->assertOk();
    expect($room->fresh()->status)->toBe('active')
        ->and($room->fresh()->listing_fee_paid)->toBeTrue();
});

it('shows an owner only their own unlocked enquiries', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $otherOwner = User::factory()->create(['role' => 'owner']);
    $user = User::factory()->create(['role' => 'user']);
    $ownRoom = marketplaceRoom($owner, ['title' => 'Own Enquiry Room']);
    $otherRoom = marketplaceRoom($otherOwner, ['title' => 'Other Enquiry Room']);

    Enquiry::create(['user_id' => $user->id, 'room_id' => $ownRoom->id, 'unlocked' => true, 'unlocked_at' => now()]);
    Enquiry::create(['user_id' => $user->id, 'room_id' => $otherRoom->id, 'unlocked' => true, 'unlocked_at' => now()]);

    $this->actingAs($owner)->get(route('owner.enquiries'))
        ->assertOk()
        ->assertSee('Own Enquiry Room')
        ->assertDontSee('Other Enquiry Room');
});
