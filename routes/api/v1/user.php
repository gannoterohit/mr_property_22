<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiProfileController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiPaymentController;
use App\Http\Controllers\Api\ApiBookingController;
use App\Http\Controllers\Api\ApiUnlockController;
use App\Http\Controllers\Api\ApiWalletController;
use App\Http\Controllers\Api\ApiWishlistController;
use App\Http\Controllers\Api\ApiGeneralController;
use App\Http\Controllers\Api\ApiSubscriptionController;
use App\Http\Controllers\Api\ApiComplaintController;
use App\Http\Controllers\Api\ApiAccountController;

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth & Profile ────────────────────────
    Route::get('/auth/me',      [ApiAuthController::class, 'user']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/profile',             [ApiProfileController::class, 'show']);
    Route::post('/profile/update',      [ApiProfileController::class, 'update']);
    Route::post('/profile/delete-otp',  [ApiProfileController::class, 'sendDeleteOtp']);
    Route::delete('/profile',           [ApiProfileController::class, 'destroy']);

    // ── Dashboard ─────────────────────────────
    Route::get('/dashboard',        [ApiDashboardController::class, 'index']);
    Route::get('/referral-stats',   [ApiDashboardController::class, 'referralStats']);

    // ── Payments ──────────────────────────────
    Route::post('/payments/create-order', [ApiPaymentController::class, 'createOrder']);
    Route::post('/payments/verify',       [ApiPaymentController::class, 'verifyPayment']);

    // ── Transactions ──────────────────────────
    // Phase 1 does not collect rent or create bookings/payouts.
    Route::post('/unlock/{room}', [ApiUnlockController::class, 'unlock']);

    // ── Wallet & Wishlist ─────────────────────
    Route::get('/wallet',          [ApiWalletController::class, 'index']);
    Route::post('/wallet/convert', [ApiWalletController::class, 'convertPoints']);
    Route::get('/wishlist',                     [ApiWishlistController::class, 'index']);
    Route::post('/wishlist/toggle/{roomId}',    [ApiWishlistController::class, 'toggle']);

    // ── City Alerts ───────────────────────────
    Route::get('/city-alerts',          [ApiGeneralController::class, 'getCityAlerts']);
    Route::post('/city-alerts',         [ApiGeneralController::class, 'addCityAlert']);
    Route::delete('/city-alerts/{id}',  [ApiGeneralController::class, 'removeCityAlert']);

    // ── Subscriptions ─────────────────────────
    Route::get('/plans',                    [ApiSubscriptionController::class, 'plans']);
    Route::post('/subscriptions/purchase',  [ApiSubscriptionController::class, 'purchase']);
    Route::get('/subscriptions',             [ApiAccountController::class, 'subscriptions']);
    Route::get('/payments',                  [ApiAccountController::class, 'payments']);
    Route::get('/unlocks',                   [ApiAccountController::class, 'unlocks']);

    // Unified support for both renters and owners.
    Route::get('/complaint-options',                          [ApiComplaintController::class, 'options']);
    Route::get('/complaints',                                [ApiComplaintController::class, 'index']);
    Route::post('/complaints',                               [ApiComplaintController::class, 'store'])->middleware('throttle:public_form');
    Route::get('/complaints/{complaint}',                    [ApiComplaintController::class, 'show']);
    Route::post('/complaints/{complaint}/replies',           [ApiComplaintController::class, 'reply'])->middleware('throttle:public_form');
    Route::get('/complaints/{complaint}/evidence',           [ApiComplaintController::class, 'evidence']);
    Route::get('/complaints/{complaint}/replies/{reply}/attachment', [ApiComplaintController::class, 'attachment']);
});
