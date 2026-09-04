<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiProfileController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiPaymentController;
use App\Http\Controllers\Api\ApiUnlockController;
use App\Http\Controllers\Api\ApiWalletController;
use App\Http\Controllers\Api\ApiWishlistController;
use App\Http\Controllers\Api\ApiGeneralController;
use App\Http\Controllers\Api\ApiSubscriptionController;
use App\Http\Controllers\Api\ApiComplaintController;
use App\Http\Controllers\Api\ApiAccountController;
use App\Http\Controllers\Api\ApiRoomController;

use App\Http\Controllers\Api\ApiFcmTokenController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\Api\ApiCouponController;

Route::middleware(['auth:sanctum', 'role:user'])->group(function () {

    // ── Auth & Profile ────────────────────────
    Route::get('/profile',             [ApiProfileController::class, 'show']);
    Route::post('/profile/update',      [ApiProfileController::class, 'update']);
    Route::post('/profile/delete-otp',  [ApiProfileController::class, 'sendDeleteOtp']);
    Route::delete('/profile',           [ApiProfileController::class, 'destroy']);
    Route::post('/fcm-token',           [ApiFcmTokenController::class, 'store']);
    Route::delete('/fcm-token',        [ApiFcmTokenController::class, 'destroy']);

    // ── Notifications ─────────────────────────
    Route::get('/notifications',              [ApiNotificationController::class, 'index']);
    Route::post('/notifications/read-all',    [ApiNotificationController::class, 'markAllRead']);
    Route::get('/notifications/unread-count', [ApiNotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read',   [ApiNotificationController::class, 'markRead']);

    // ── Dashboard ─────────────────────────────
    Route::get('/dashboard',        [ApiDashboardController::class, 'index']);
    Route::get('/referral-stats',   [ApiDashboardController::class, 'referralStats']);
    Route::post('/rooms/set-city',  [ApiRoomController::class, 'setCity']);

    // ── Payments ──────────────────────────────
    Route::post('/payments/create-order', [ApiPaymentController::class, 'createOrder'])->middleware('throttle:10,1');
    Route::post('/payments/verify',       [ApiPaymentController::class, 'verifyPayment'])->middleware('throttle:20,1');

    // ── Transactions ──────────────────────────
    Route::post('/unlock/{room}', [ApiUnlockController::class, 'unlock'])->middleware('throttle:10,1');

    // ── Wallet & Wishlist ─────────────────────
    Route::get('/wallet',          [ApiWalletController::class, 'index']);
    Route::post('/wallet/convert', [ApiWalletController::class, 'convertPoints'])->middleware('throttle:10,1');
    Route::get('/wishlist',                     [ApiWishlistController::class, 'index']);
    Route::post('/wishlist/toggle/{roomId}',    [ApiWishlistController::class, 'toggle']);

    // ── City Alerts ───────────────────────────
    Route::get('/city-alerts',          [ApiGeneralController::class, 'getCityAlerts']);
    Route::post('/city-alerts',         [ApiGeneralController::class, 'addCityAlert']);
    Route::delete('/city-alerts/{id}',  [ApiGeneralController::class, 'removeCityAlert']);

    // ── Coupons ───────────────────────────────
    Route::post('/coupon/apply',  [ApiCouponController::class, 'apply'])->middleware('throttle:20,1');
    Route::post('/coupon/remove', [ApiCouponController::class, 'remove'])->middleware('throttle:20,1');

    // ── Subscriptions ─────────────────────────
    Route::get('/plans',                    [ApiSubscriptionController::class, 'plans']);
    Route::post('/subscriptions/purchase',  [ApiSubscriptionController::class, 'purchase'])->middleware('throttle:5,1');
    Route::get('/subscriptions',             [ApiAccountController::class, 'subscriptions']);
    Route::get('/payments',                  [ApiAccountController::class, 'payments']);
    Route::get('/unlocks',                   [ApiAccountController::class, 'unlocks']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [ApiAuthController::class, 'user']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
});

// Support is shared by renters, owners, and brokers.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/complaint-options', [ApiComplaintController::class, 'options']);
    Route::get('/complaints', [ApiComplaintController::class, 'index']);
    Route::post('/complaints', [ApiComplaintController::class, 'store'])->middleware('throttle:public_form');
    Route::get('/complaints/{complaint}', [ApiComplaintController::class, 'show']);
    Route::post('/complaints/{complaint}/replies', [ApiComplaintController::class, 'reply'])->middleware('throttle:public_form');
    Route::get('/complaints/{complaint}/evidence', [ApiComplaintController::class, 'evidence']);
    Route::get('/complaints/{complaint}/replies/{reply}/attachment', [ApiComplaintController::class, 'attachment']);
});
