<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiRoomController;
use App\Http\Controllers\Api\ApiGeneralController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\Api\ApiRoomOptionController;
use App\Http\Controllers\Api\ApiPropertyCatalogController;

// ── App Settings ──────────────────────────
Route::get('/settings', [ApiSettingsController::class, 'index']);

// ── Authentication ────────────────────────
Route::post('/auth/send-otp',  [ApiAuthController::class, 'sendOtp'])->middleware('throttle:strict_otp');
Route::post('/auth/register',  [ApiAuthController::class, 'register'])->middleware('throttle:strict_login');
Route::post('/auth/login',     [ApiAuthController::class, 'login'])->middleware('throttle:strict_login');

// ── Rooms (Public Browse) ─────────────────
Route::get('/rooms',                   [ApiRoomController::class, 'index']);
Route::get('/rooms/{room}',            [ApiRoomController::class, 'show']);
Route::get('/rooms/{room}/similar',    [ApiRoomController::class, 'similar']);
Route::post('/rooms/detect-city',      [ApiRoomController::class, 'detectCity']);
Route::get('/cities',                  [ApiRoomController::class, 'getCities']);
Route::get('/room-options',            [ApiRoomOptionController::class, 'index']);
Route::get('/property-types',          [ApiPropertyCatalogController::class, 'propertyTypes']);
Route::get('/property-categories',     [ApiPropertyCatalogController::class, 'propertyCategories']);

// ── Blogs ─────────────────────────────────
Route::get('/blogs',           [ApiGeneralController::class, 'blogs']);
Route::get('/blogs/{slug}',    [ApiGeneralController::class, 'blogShow']);

// ── Static Pages ──────────────────────────
Route::get('/pages/{slug}',    [ApiGeneralController::class, 'page']);

// ── FAQ ───────────────────────────────────
Route::get('/faq',             [ApiGeneralController::class, 'faq']);

// ── Newsletter ────────────────────────────
Route::post('/newsletter/subscribe', [ApiGeneralController::class, 'subscribeNewsletter'])->middleware('throttle:public_form');

// ── Contact Us Form ───────────────────────
Route::post('/contact',        [ApiGeneralController::class, 'contactSubmit'])->middleware('throttle:public_form');

// ── Active Offers ─────────────────────────
Route::get('/offers',          [ApiGeneralController::class, 'offers']);

// ── Referral Code Validation (mobile deep links) ──
Route::get('/referral/{code}', [ApiGeneralController::class, 'validateReferral']);
