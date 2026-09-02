<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes — RoomRental (Modular Monolith Style)
|--------------------------------------------------------------------------
|
| This file imports modular route definitions from routes/api/v1/*.php
| structure making it easy to add new modules (e.g., cars, hostels).
|
*/

Route::prefix('v1')->group(function () {

    // 1. PUBLIC Endpoints (Settings, Auth, Global Browse)
    require __DIR__ . '/api/v1/public.php';

    // 2. USER Endpoints (Auth, Profile, Wallet, Wishlist, Unlocks)
    require __DIR__ . '/api/v1/user.php';

    // 3. OWNER Endpoints (Dashboard, Room Management, Leads)
    require __DIR__ . '/api/v1/owner.php';

    // 4. BROKER Endpoints (Dashboard, Properties, Payments)
    require __DIR__ . '/api/v1/broker.php';

    // 5. ADMIN Endpoints (Global Control, Analytics, User Management)
    require __DIR__ . '/api/v1/admin.php';

    // 5. WEBHOOKS (External Callbacks)
    Route::post('/webhook/razorpay', [\App\Http\Controllers\RazorpayController::class, 'webhook'])
         ->name('api.webhook.razorpay');

});
