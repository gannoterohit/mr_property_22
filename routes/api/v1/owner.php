<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiOwnerController;
use App\Http\Controllers\Api\ApiRoomController;

Route::middleware(['auth:sanctum', 'role:owner'])->prefix('owner')->group(function () {

    Route::get('/dashboard',    [ApiOwnerController::class, 'dashboard']);


    Route::get('/enquiries',    [ApiOwnerController::class, 'enquiries']);

    // ── Room Management ────────────────────
    Route::get('/rooms',                        [ApiRoomController::class, 'myRooms']);
    Route::get('/rooms/{room}',                 [ApiRoomController::class, 'ownerShow']);
    Route::post('/rooms',                       [ApiRoomController::class, 'store']);
    Route::put('/rooms/{room}',                 [ApiRoomController::class, 'update']);
    Route::post('/rooms/{room}',                [ApiRoomController::class, 'update']); // Multipart photo/video edits
    Route::delete('/rooms/{room}',              [ApiRoomController::class, 'destroy']);
    Route::post('/rooms/{room}/toggle-status',  [ApiRoomController::class, 'toggleStatus']);
    Route::post('/rooms/{room}/feature',        [ApiRoomController::class, 'makeFeatured']);
});
