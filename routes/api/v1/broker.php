<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiBrokerController;
use App\Http\Controllers\Api\ApiRoomController;

Route::middleware(['auth:sanctum', 'role:broker'])->prefix('broker')->group(function () {

    Route::get('/dashboard',    [ApiBrokerController::class, 'dashboard']);
    Route::get('/properties',   [ApiBrokerController::class, 'properties']);
    Route::get('/enquiries',    [ApiBrokerController::class, 'enquiries']);
    Route::get('/payments',     [ApiBrokerController::class, 'payments']);
    Route::get('/transactions', [ApiBrokerController::class, 'transactions']);
    Route::get('/profile',      [ApiBrokerController::class, 'profile']);
    Route::patch('/profile',    [ApiBrokerController::class, 'updateProfile']);

    // ── Room Management (Broker) ────────────
    Route::get('/rooms',                        [ApiRoomController::class, 'myRooms']);
    Route::get('/rooms/{room}',                 [ApiRoomController::class, 'ownerShow']);
    Route::post('/rooms',                       [ApiRoomController::class, 'store']);
    Route::put('/rooms/{room}',                 [ApiRoomController::class, 'update']);
    Route::post('/rooms/{room}',                [ApiRoomController::class, 'update']);
    Route::delete('/rooms/{room}',              [ApiRoomController::class, 'destroy']);
    Route::post('/rooms/{room}/toggle-status',  [ApiRoomController::class, 'toggleStatus']);
    Route::post('/rooms/{room}/feature',        [ApiRoomController::class, 'makeFeatured']);
});
