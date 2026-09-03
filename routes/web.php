<?php

use App\Http\Controllers\Admin\AdminBroadcastController;
use App\Http\Controllers\Admin\AdminBrokerController;
use App\Http\Controllers\Admin\AdminBrokerPlanController;
use App\Http\Controllers\Admin\AdminBrokerSettingsController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\DataTransferController;
use App\Http\Controllers\Admin\HomeFeatureController;
use App\Http\Controllers\Admin\HowItWorksController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\PropertyCategoryController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\RejectionReasonController;
use App\Http\Controllers\Admin\RoomOptionController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\BrokerDashboardController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\BrokerRoomController;
use App\Http\Controllers\BrokerRoomDraftController;
use App\Http\Controllers\OwnerRoomDraftController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UnlockController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/admin-login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'adminAccess'])
    ->name('admin.login-access');
Route::post('/admin-login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'adminAuthenticate'])
    ->middleware('throttle:strict_login')
    ->name('admin.login.submit');

Route::get('auth/{provider}/redirect', [\App\Http\Controllers\SocialAuthController::class, 'redirect'])
    ->where('provider', 'google|facebook')
    ->name('social.redirect');

Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialAuthController::class, 'callback'])
    ->where('provider', 'google|facebook')
    ->name('social.callback');

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/set-city', [RoomController::class, 'setCity'])->name('set-city');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::post('/analytics/events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('analytics.events.store');

// Broker Registration redirect to unified auth registration
Route::get('/become-agent', fn () => redirect()->route('register', ['role' => 'broker']))->name('broker.register');

    // Referral Tracking
    Route::get('/ref/{code}', [\App\Http\Controllers\ReferralController::class, 'track'])->name('referral.track');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'broker') {
        return redirect()->route('agent.dashboard');
    } elseif ($user->role === 'owner') {
        return redirect()->route('owner.dashboard');
    } else {
        return redirect()->route('home');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Public room browsing
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');

// Map-based search
Route::get('/map-search', [\App\Http\Controllers\MapSearchController::class, 'index'])->name('rooms.map');
Route::get('/api/map-rooms', [\App\Http\Controllers\MapSearchController::class, 'index'])->name('rooms.map.api');

Route::middleware(['auth', 'role:broker', 'broker.active'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [BrokerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending', [BrokerDashboardController::class, 'pending'])->name('pending');
    Route::get('/properties', [BrokerDashboardController::class, 'properties'])->name('properties');
    Route::get('/enquiries', [BrokerDashboardController::class, 'enquiries'])->name('enquiries');

    Route::get('/rooms/drafts', [BrokerRoomDraftController::class, 'index'])->name('rooms.drafts');
    Route::post('/rooms/drafts/save', [BrokerRoomDraftController::class, 'save'])->name('rooms.drafts.save');
    Route::get('/rooms/drafts/latest', [BrokerRoomDraftController::class, 'latest'])->name('rooms.drafts.latest');
    Route::get('/rooms/drafts/{id}', [BrokerRoomDraftController::class, 'load'])->name('rooms.drafts.load');
    Route::delete('/rooms/drafts/{id}', [BrokerRoomDraftController::class, 'destroy'])->name('rooms.drafts.destroy');

    Route::get('/rooms/create', [BrokerRoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{room}/featured', [RoomController::class, 'makeFeatured'])->name('rooms.featured');
    Route::post('/rooms/{room}/booked', [RoomController::class, 'markBooked'])->name('rooms.markBooked');
    Route::post('/rooms/{room}/available', [RoomController::class, 'markAvailable'])->name('rooms.markAvailable');
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');
    Route::get('/payments', [BrokerDashboardController::class, 'payments'])->name('payments');
    Route::get('/transactions', [BrokerDashboardController::class, 'transactions'])->name('transactions');
    Route::get('/profile', [BrokerDashboardController::class, 'profile'])->name('profile');
    Route::patch('/profile', [BrokerDashboardController::class, 'updateProfile'])->name('profile.update');
});

// Public room browsing (moved to top - lines 76-77)
// Removed duplicate declarations that were here

Route::post('/unlock/{room}', [UnlockController::class, 'unlock'])->name('unlock.contact');

// Blog Routes
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blogs.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blogs.show');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\SubscriberController::class, 'store'])->middleware('throttle:public_form')->name('newsletter.subscribe');

// Static Pages
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
Route::post('/contact-us', [\App\Http\Controllers\ContactController::class, 'store'])->middleware('throttle:public_form')->name('pages.contact.store');

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

    // Payment routes
    Route::post('/payment/razorpay/order', [RazorpayController::class, 'createOrder'])->middleware('throttle:10,1')->name('razorpay.createOrder');
    Route::post('/payment/razorpay/verify', [RazorpayController::class, 'verifyPayment'])->middleware('throttle:10,1')->name('razorpay.verify');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans');
    Route::post('/subscription/purchase', [\App\Http\Controllers\SubscriptionController::class, 'store'])->name('subscription.purchase');
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscribe');

    // Referral Dashboard
    Route::get('/refer-and-earn', [\App\Http\Controllers\ReferralController::class, 'index'])->name('referral.index');

    // Wishlist Routes
    Route::get('/wishlist', [App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{roomId}', [App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // City Alerts
    Route::post('/city-alerts', [App\Http\Controllers\CityAlertController::class, 'store'])->name('city-alerts.store');
    Route::delete('/city-alerts/{alert}', [App\Http\Controllers\CityAlertController::class, 'destroy'])->name('city-alerts.destroy');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/convert', [WalletController::class, 'convertPoints'])->name('wallet.convert');

    // User / Owner Bell Icon Notifications
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('user.notifications.index');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('user.notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('user.notifications.readAll');
    Route::get('/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('user.notifications.unreadCount');

    // Web Push Notification Token
    Route::post('/push-token', [\App\Http\Controllers\WebPushTokenController::class, 'store'])->name('web.push.store');
    Route::delete('/push-token', [\App\Http\Controllers\WebPushTokenController::class, 'destroy'])->name('web.push.destroy');
});

Route::post('/webhook/razorpay', [RazorpayController::class, 'webhook'])->name('razorpay.webhook');

// Coupon / Promo Code Validation (auth required)
Route::middleware(['auth'])->group(function () {
    Route::post('/coupon/apply', [\App\Http\Controllers\CouponController::class, 'apply'])->name('coupon.apply');
});

Route::middleware(['auth', 'role:admin', 'admin.permission', 'admin.activity'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/staff', [\App\Http\Controllers\Admin\AdminStaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [\App\Http\Controllers\Admin\AdminStaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{staff}', [\App\Http\Controllers\Admin\AdminStaffController::class, 'update'])->name('staff.update');
    Route::post('/staff/{staff}/toggle', [\App\Http\Controllers\Admin\AdminStaffController::class, 'toggle'])->name('staff.toggle');
    Route::delete('/staff/{staff}', [\App\Http\Controllers\Admin\AdminStaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{id}/restore', [\App\Http\Controllers\Admin\AdminStaffController::class, 'restore'])->name('staff.restore');
    Route::get('/roles', [\App\Http\Controllers\Admin\AdminRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [\App\Http\Controllers\Admin\AdminRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [\App\Http\Controllers\Admin\AdminRoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'update'])->name('roles.update');
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\AdminActivityController::class, 'index'])->name('activity.index');
    Route::delete('/activity-logs/bulk', [\App\Http\Controllers\Admin\AdminActivityController::class, 'bulkDestroy'])->name('activity.bulk-destroy');
    Route::delete('/activity-logs/filtered', [\App\Http\Controllers\Admin\AdminActivityController::class, 'destroyFiltered'])->name('activity.destroy-filtered');
    Route::delete('/activity-logs/{activityLog}', [\App\Http\Controllers\Admin\AdminActivityController::class, 'destroy'])->name('activity.destroy');

    // Admin Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('notifications.unreadCount');

    // Broadcast Announcement Center
    Route::get('/broadcast', [\App\Http\Controllers\Admin\AdminBroadcastController::class, 'index'])->name('broadcast.index');
    Route::post('/broadcast/send', [\App\Http\Controllers\Admin\AdminBroadcastController::class, 'send'])->name('broadcast.send');

    // Blog Management
    Route::patch('blogs/{blog}/toggle-status', [\App\Http\Controllers\Admin\BlogController::class, 'toggleStatus'])->name('blogs.toggle-status');
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class)->except(['show']);

    Route::get('/settings', [BusinessSettingsController::class, 'index'])->name('settings');
    Route::get('/maintenance', [BusinessSettingsController::class, 'maintenance'])->name('maintenance');
    Route::post('/maintenance', [BusinessSettingsController::class, 'updateMaintenance'])->name('maintenance.update');
    Route::get('/data-maintenance', [\App\Http\Controllers\Admin\DataMaintenanceController::class, 'index'])->name('data-maintenance.index');
    Route::put('/data-maintenance/retention', [\App\Http\Controllers\Admin\DataMaintenanceController::class, 'update'])->name('data-maintenance.update');
    Route::post('/data-maintenance/cleanup', [\App\Http\Controllers\Admin\DataMaintenanceController::class, 'cleanup'])->name('data-maintenance.cleanup');
    Route::get('/data-tools', [DataTransferController::class, 'index'])->name('data-tools.index');
    Route::get('/data-tools/export/{dataset}', [DataTransferController::class, 'export'])->name('data-tools.export');
    Route::get('/data-tools/template/{dataset}', [DataTransferController::class, 'template'])->name('data-tools.template');
    Route::post('/data-tools/import/{dataset}', [DataTransferController::class, 'import'])->name('data-tools.import');
    Route::get('/data-tools/report/{dataset}', [DataTransferController::class, 'report'])->name('data-tools.report');
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
    Route::get('/cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');
    Route::put('/cities/{city}', [CityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');
    Route::post('/settings', [BusinessSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/store', [BusinessSettingsController::class, 'store'])->name('settings.store');
    Route::post('/settings/ping', [BusinessSettingsController::class, 'pingSearchEngines'])->name('settings.ping');
    Route::patch('cms-pages/{cmsPage}/toggle-status', [CmsPageController::class, 'toggleStatus'])->name('cms-pages.toggle-status');
    Route::resource('cms-pages', CmsPageController::class)->except(['show']);
    Route::get('pages/faq', [\App\Http\Controllers\Admin\PagesController::class, 'faq'])->name('pages.faq');
    Route::put('pages/faq', [\App\Http\Controllers\Admin\PagesController::class, 'updateFaq'])->name('pages.faq.update');
    Route::patch('home-features/{homeFeature}/toggle-status', [HomeFeatureController::class, 'toggleStatus'])->name('home-features.toggle-status');
    Route::resource('home-features', HomeFeatureController::class)->except(['show']);
    Route::get('how-it-works', [HowItWorksController::class, 'index'])->name('how-it-works.index');
    Route::put('how-it-works/settings', [HowItWorksController::class, 'updateSettings'])->name('how-it-works.settings');
    Route::get('how-it-works/items/create', [HowItWorksController::class, 'create'])->name('how-it-works.items.create');
    Route::post('how-it-works/items', [HowItWorksController::class, 'store'])->name('how-it-works.items.store');
    Route::get('how-it-works/items/{item}/edit', [HowItWorksController::class, 'edit'])->name('how-it-works.items.edit');
    Route::put('how-it-works/items/{item}', [HowItWorksController::class, 'update'])->name('how-it-works.items.update');
    Route::delete('how-it-works/items/{item}', [HowItWorksController::class, 'destroy'])->name('how-it-works.items.destroy');
    Route::patch('how-it-works/items/{item}/toggle-status', [HowItWorksController::class, 'toggleStatus'])->name('how-it-works.items.toggle-status');
    Route::patch('testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
    Route::resource('testimonials', TestimonialController::class)->except(['show']);
    Route::resource('room-options', RoomOptionController::class)->except(['show']);
    Route::patch('room-options/{roomOption}/toggle-status', [RoomOptionController::class, 'toggleStatus'])->name('room-options.toggle-status');
    Route::resource('property-types', PropertyTypeController::class)->except(['show']);
    Route::patch('property-types/{propertyType}/toggle-status', [PropertyTypeController::class, 'toggleStatus'])->name('property-types.toggle-status');
    Route::resource('property-categories', PropertyCategoryController::class)->except(['show']);
    Route::patch('property-categories/{propertyCategory}/toggle-status', [PropertyCategoryController::class, 'toggleStatus'])->name('property-categories.toggle-status');
    Route::patch('rejection-reasons/{rejectionReason}/toggle-status', [\App\Http\Controllers\Admin\RejectionReasonController::class, 'toggleStatus'])->name('rejection-reasons.toggle-status');
    Route::patch('cities/{city}/toggle-status', [\App\Http\Controllers\Admin\CityController::class, 'toggleStatus'])->name('cities.toggle-status');
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::post('/plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');

    // Coupon / Offer Management
    Route::resource('offers', \App\Http\Controllers\Admin\OfferController::class)->except(['show']);
    Route::post('/offers/{offer}/toggle-active', [\App\Http\Controllers\Admin\OfferController::class, 'toggleActive'])->name('offers.toggleActive');

    // Users Management
    Route::get('/members', [AdminController::class, 'member360'])->name('members.index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}', [AdminController::class, 'userDetail'])->name('users.detail');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/toggle-block', [AdminController::class, 'toggleBlock'])->name('users.toggleBlock');
    Route::put('/members/{user}/notes', [AdminController::class, 'updateMemberNotes'])->name('members.notes');
    Route::post('/members/{user}/send-direct-message', [AdminController::class, 'sendDirectMessage'])->name('members.sendDirectMessage');
    Route::post('/members/{user}/restore', [AdminController::class, 'restoreMember'])->name('members.restore');


    // Owners Management
    Route::get('/owners', [AdminController::class, 'owners'])->name('owners');
    Route::get('/owners/create', [AdminController::class, 'createOwner'])->name('owners.create');
    Route::post('/owners', [AdminController::class, 'storeOwner'])->name('owners.store');
    Route::get('/owners/{owner}', [AdminController::class, 'ownerDetail'])->name('owners.detail');
    Route::get('/owners/{owner}/edit', [AdminController::class, 'editOwner'])->name('owners.edit');
    Route::put('/owners/{owner}', [AdminController::class, 'updateOwner'])->name('owners.update');
    Route::delete('/owners/{owner}', [AdminController::class, 'destroyOwner'])->name('owners.destroy');
    Route::post('/owners/{user}/toggle-block', [AdminController::class, 'toggleBlock'])->name('owners.toggleBlock');

    // Brokers Management
    Route::get('/brokers', [AdminBrokerController::class, 'index'])->name('brokers.index');
    Route::get('/brokers/{broker}', [AdminBrokerController::class, 'show'])->name('brokers.show');
    Route::post('/brokers/{broker}/approve', [AdminBrokerController::class, 'approve'])->name('brokers.approve');
    Route::post('/brokers/{broker}/reject', [AdminBrokerController::class, 'reject'])->name('brokers.reject');
    Route::post('/brokers/{broker}/suspend', [AdminBrokerController::class, 'suspend'])->name('brokers.suspend');
    Route::post('/brokers/{broker}/activate', [AdminBrokerController::class, 'activate'])->name('brokers.activate');
    Route::delete('/brokers/{broker}', [AdminBrokerController::class, 'destroy'])->name('brokers.destroy');

    // Broker Settings
    Route::get('/broker-settings', [AdminBrokerSettingsController::class, 'index'])->name('broker-settings.index');
    Route::post('/broker-settings', [AdminBrokerSettingsController::class, 'update'])->name('broker-settings.update');

    // Broker Plans
    Route::resource('broker-plans', AdminBrokerPlanController::class)->except(['show']);
    Route::post('/broker-plans/{brokerPlan}/toggle-active', [AdminBrokerPlanController::class, 'toggleActive'])->name('broker-plans.toggleActive');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/all-rooms', [AdminController::class, 'rooms'])->name('all-rooms');
    Route::get('/payments-index', [AdminController::class, 'paymentsIndex'])->name('payments.index');



    // City Alerts Management
    Route::get('/city-alerts', [AdminController::class, 'cityAlerts'])->name('city-alerts.index');
    Route::delete('/city-alerts/{alert}', [AdminController::class, 'deleteCityAlert'])->name('city-alerts.destroy');

    // Newsletter Subscribers
    Route::get('/subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('subscribers.index');
    Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

    // Contact Messages
    Route::get('/contact-messages', [AdminController::class, 'contactMessages'])->name('contact-messages.index');
    Route::post('/contact-messages/{id}/read', [AdminController::class, 'markMessageAsRead'])->name('contact-messages.read');
    Route::delete('/contact-messages/{id}', [AdminController::class, 'deleteContactMessage'])->name('contact-messages.destroy');

    Route::get('/complaints', [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->name('complaints.show');
    Route::put('/complaints/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'update'])->name('complaints.update');
    Route::post('/complaints/{complaint}/reply', [\App\Http\Controllers\Admin\ComplaintController::class, 'reply'])->name('complaints.reply');
    Route::post('/complaints/{complaint}/reopen', [\App\Http\Controllers\Admin\ComplaintController::class, 'reopen'])->name('complaints.reopen');

    Route::get('rejection-reasons', [RejectionReasonController::class, 'index'])->name('rejection-reasons.index');
    Route::post('rejection-reasons', [RejectionReasonController::class, 'store'])->name('rejection-reasons.store');
    Route::put('rejection-reasons/{rejectionReason}', [RejectionReasonController::class, 'update'])->name('rejection-reasons.update');
    Route::delete('rejection-reasons/{rejectionReason}', [RejectionReasonController::class, 'destroy'])->name('rejection-reasons.destroy');

    Route::get('rooms', [AdminController::class, 'rooms'])->name('rooms.list');
    Route::get('rooms/create', [AdminController::class, 'createRoom'])->name('rooms.create');
    Route::post('rooms/store', [AdminController::class, 'storeRoom'])->name('rooms.store');
    Route::get('rooms/{room}', [AdminController::class, 'showRoom'])->name('rooms.show');
    Route::get('rooms/{room}/edit', [AdminController::class, 'editRoom'])->name('rooms.edit');
    Route::put('rooms/{room}/update', [AdminController::class, 'updateRoom'])->name('rooms.update');
    Route::post('/rooms/{room}/approve', [AdminController::class, 'approveRoom'])->name('rooms.approve');
    Route::post('/rooms/{room}/reject', [AdminController::class, 'rejectRoom'])->name('rooms.reject');
    Route::patch('/rooms/{room}/toggle-status', [AdminController::class, 'toggleRoomStatus'])->name('rooms.toggle-status');
    Route::post('rooms/bulk-action', [AdminController::class, 'bulkRooms'])->name('rooms.bulk');
    Route::delete('rooms/{room}', [AdminController::class, 'deleteRoom'])->name('rooms.destroy');

    // Search Analytics
    Route::get('/analytics', [\App\Http\Controllers\Admin\SearchAnalyticsController::class, 'index'])->name('analytics');
    Route::delete('/analytics/logs/all', [\App\Http\Controllers\Admin\SearchAnalyticsController::class, 'destroyAll'])->name('analytics.logs.all');
    Route::delete('/analytics/logs/range', [\App\Http\Controllers\Admin\SearchAnalyticsController::class, 'destroyRange'])->name('analytics.logs.range');
    Route::delete('/analytics/logs/{searchLog}', [\App\Http\Controllers\Admin\SearchAnalyticsController::class, 'destroy'])->name('analytics.logs.destroy');

    Route::controller(PagesController::class)->group(function () {
        Route::post('/pages/upload-image', 'uploadImage')->name('pages.upload-image');
    });
    Route::get('/pages/{key}', [CmsPageController::class, 'legacy'])
        ->where('key', 'about|careers|how-it-works|safety-tips|owner-guidelines|user-guidelines|terms|condition|privacy|contact|faq')
        ->name('pages.legacy');
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/rooms', [OwnerController::class, 'rooms'])->name('rooms');

    Route::get('/rooms/drafts', [OwnerRoomDraftController::class, 'index'])->name('rooms.drafts');
    Route::post('/rooms/drafts/save', [OwnerRoomDraftController::class, 'save'])->name('rooms.drafts.save');
    Route::get('/rooms/drafts/latest', [OwnerRoomDraftController::class, 'latest'])->name('rooms.drafts.latest');
    Route::get('/rooms/drafts/{id}', [OwnerRoomDraftController::class, 'load'])->name('rooms.drafts.load');
    Route::delete('/rooms/drafts/{id}', [OwnerRoomDraftController::class, 'destroy'])->name('rooms.drafts.destroy');

    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{room}/featured', [RoomController::class, 'makeFeatured'])->name('rooms.featured');
    Route::post('/rooms/{room}/booked', [RoomController::class, 'markBooked'])->name('rooms.markBooked');
    Route::post('/rooms/{room}/available', [RoomController::class, 'markAvailable'])->name('rooms.markAvailable');
    Route::get('/enquiries', [OwnerController::class, 'enquiries'])->name('enquiries');
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');

});

require __DIR__.'/auth.php';

// YouTube Proxy Route
Route::get('/youtube-proxy/{videoId}', function ($videoId) {
    $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";

    // Fetch the thumbnail from YouTube
    $response = Http::get($thumbnailUrl);

    if ($response->successful()) {
        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Cache-Control', 'public, max-age=31536000');
    } else {
        abort(404, 'YouTube thumbnail not found');
    }
})->name('youtube.proxy');

Route::get('/{cmsPageSlug}', [\App\Http\Controllers\PageController::class, 'show'])
    ->where('cmsPageSlug', '^(?!(bhopal|indore|pune|mumbai|delhi|bangalore|hyderabad|admin|owner|api|rooms|blog|login|register|dashboard|profile|complaints|plans|wallet|wishlist|sitemap\.xml|robots\.txt|youtube-proxy)$)[A-Za-z0-9-]+$')
    ->name('cms-pages.show');

Route::get('/{citySlug}', [LandingPageController::class, 'city'])
    ->where('citySlug', 'bhopal|indore|pune|mumbai|delhi|bangalore|hyderabad')
    ->name('cities.show');
