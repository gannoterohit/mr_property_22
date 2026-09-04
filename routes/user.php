<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UnlockController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/otp-delete', [ProfileController::class, 'sendDeleteOtp'])
        ->name('profile.send-delete-otp');

    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])
        ->middleware('throttle:public_form')
        ->name('complaints.store');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/reply', [ComplaintController::class, 'reply'])
        ->middleware('throttle:public_form')
        ->name('complaints.reply');
    Route::get('/complaints/{complaint}/evidence', [ComplaintController::class, 'evidence'])
        ->name('complaints.evidence');
    Route::get('/complaints/{complaint}/attachments/{reply}', [ComplaintController::class, 'attachment'])
        ->name('complaints.attachment');

    Route::get('/unlocked-contacts', [UnlockController::class, 'index'])->name('unlocks.index');
    Route::post('/payment/razorpay/order', [RazorpayController::class, 'createOrder'])->middleware('throttle:10,1')->name('razorpay.createOrder');
    Route::post('/payment/razorpay/verify', [RazorpayController::class, 'verifyPayment'])->middleware('throttle:10,1')->name('razorpay.verify');
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');
    Route::post('/subscription/purchase', [SubscriptionController::class, 'store'])->middleware('throttle:5,1')->name('subscription.purchase');
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->middleware('throttle:5,1')->name('subscribe');
    Route::get('/refer-and-earn', [\App\Http\Controllers\ReferralController::class, 'index'])->name('referral.index');
    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{roomId}', [\App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/city-alerts', [\App\Http\Controllers\CityAlertController::class, 'store'])->name('city-alerts.store');
    Route::delete('/city-alerts/{alert}', [\App\Http\Controllers\CityAlertController::class, 'destroy'])->name('city-alerts.destroy');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/convert', [WalletController::class, 'convertPoints'])->name('wallet.convert');
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('user.notifications.index');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('user.notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('user.notifications.readAll');
    Route::get('/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('user.notifications.unreadCount');
    Route::post('/push-token', [\App\Http\Controllers\WebPushTokenController::class, 'store'])->name('web.push.store');
    Route::delete('/push-token', [\App\Http\Controllers\WebPushTokenController::class, 'destroy'])->name('web.push.destroy');
});
