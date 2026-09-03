<?php

use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UnlockController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Authentication and social login
Route::get('auth/{provider}/redirect', [\App\Http\Controllers\SocialAuthController::class, 'redirect'])
    ->where('provider', 'google|facebook')->name('social.redirect');
Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialAuthController::class, 'callback'])
    ->where('provider', 'google|facebook')->name('social.callback');

// Public pages and discovery
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/set-city', [RoomController::class, 'setCity'])->name('set-city');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::post('/analytics/events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:60,1')->name('analytics.events.store');
Route::get('/become-agent', fn () => redirect()->route('register', ['role' => 'broker']))->name('broker.register');
Route::get('/ref/{code}', [\App\Http\Controllers\ReferralController::class, 'track'])->name('referral.track');

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'broker' => redirect()->route('agent.dashboard'),
        'owner' => redirect()->route('owner.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/map-search', [\App\Http\Controllers\MapSearchController::class, 'index'])->name('rooms.map');
Route::get('/api/map-rooms', [\App\Http\Controllers\MapSearchController::class, 'index'])->name('rooms.map.api');

// Role-specific route modules
require __DIR__.'/admin.php';
require __DIR__.'/owner.php';
require __DIR__.'/broker.php';

// Public content
Route::post('/unlock/{room}', [UnlockController::class, 'unlock'])->name('unlock.contact');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blogs.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blogs.show');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\SubscriberController::class, 'store'])
    ->middleware('throttle:public_form')->name('newsletter.subscribe');
Route::controller(\App\Http\Controllers\PageController::class)->group(function () {
    Route::get('/about-us', 'about')->name('pages.about');
    Route::get('/careers', 'careers')->name('pages.careers');
    Route::get('/how-it-works', 'howItWorks')->name('pages.how-it-works');
    Route::get('/safety-tips', 'safetyTips')->name('pages.safety-tips');
    Route::get('/owner-guidelines', 'ownerGuidelines')->name('pages.owner-guidelines');
    Route::get('/user-guidelines', 'userGuidelines')->name('pages.user-guidelines');
    Route::get('/terms-and-conditions', 'terms')->name('pages.terms');
    Route::get('/privacy-policy', 'privacy')->name('pages.privacy');
    Route::get('/condition-policy', 'condition')->name('pages.condition');
    Route::get('/contact-us', 'contact')->name('pages.contact');
    Route::get('/faq', 'faq')->name('pages.faq');
});
Route::post('/contact-us', [\App\Http\Controllers\ContactController::class, 'store'])
    ->middleware('throttle:public_form')->name('pages.contact.store');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    Route::get('/unlocked-contacts', [UnlockController::class, 'index'])->middleware('role:user')->name('unlocks.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/otp-delete', [ProfileController::class, 'sendDeleteOtp'])->name('profile.send-delete-otp');
    Route::get('/complaints', [\App\Http\Controllers\ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [\App\Http\Controllers\ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [\App\Http\Controllers\ComplaintController::class, 'store'])->middleware('throttle:public_form')->name('complaints.store');
    Route::get('/complaints/{complaint}', [\App\Http\Controllers\ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/reply', [\App\Http\Controllers\ComplaintController::class, 'reply'])->middleware('throttle:public_form')->name('complaints.reply');
    Route::get('/complaints/{complaint}/evidence', [\App\Http\Controllers\ComplaintController::class, 'evidence'])->name('complaints.evidence');
    Route::get('/complaints/{complaint}/attachments/{reply}', [\App\Http\Controllers\ComplaintController::class, 'attachment'])->name('complaints.attachment');
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

// External callbacks and framework auth routes
Route::post('/webhook/razorpay', [RazorpayController::class, 'webhook'])->name('razorpay.webhook');
require __DIR__.'/auth.php';

Route::get('/youtube-proxy/{videoId}', function ($videoId) {
    $response = Http::get("https://img.youtube.com/vi/{$videoId}/hqdefault.jpg");
    abort_unless($response->successful(), 404, 'YouTube thumbnail not found');
    return response($response->body())->header('Content-Type', $response->header('Content-Type'))
        ->header('Cache-Control', 'public, max-age=31536000');
})->name('youtube.proxy');

Route::get('/{cmsPageSlug}', [\App\Http\Controllers\PageController::class, 'show'])
    ->where('cmsPageSlug', '^(?!(bhopal|indore|pune|mumbai|delhi|bangalore|hyderabad|admin|owner|api|rooms|blog|login|register|dashboard|profile|complaints|plans|wallet|wishlist|sitemap\.xml|robots\.txt|youtube-proxy)$)[A-Za-z0-9-]+$')
    ->name('cms-pages.show');
Route::get('/{citySlug}', [LandingPageController::class, 'city'])
    ->where('citySlug', 'bhopal|indore|pune|mumbai|delhi|bangalore|hyderabad')->name('cities.show');
