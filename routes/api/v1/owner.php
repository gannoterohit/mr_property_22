<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiOwnerController;
use App\Http\Controllers\Api\ApiRoomController;
use App\Http\Controllers\Api\ApiRoomDraftController;

Route::middleware(['auth:sanctum', 'role:owner'])->prefix('owner')->group(function () {

    Route::get('/dashboard',    [ApiOwnerController::class, 'dashboard']);


    Route::get('/enquiries',    [ApiOwnerController::class, 'enquiries']);
    Route::get('/rooms/drafts', [ApiRoomDraftController::class, 'index']);
    Route::get('/rooms/drafts/latest', [ApiRoomDraftController::class, 'latest']);
    Route::get('/rooms/drafts/{id}', [ApiRoomDraftController::class, 'show']);
    Route::post('/rooms/drafts/save', [ApiRoomDraftController::class, 'save']);
    Route::delete('/rooms/drafts/{id}', [ApiRoomDraftController::class, 'destroy']);

    // ── Room Management ────────────────────
    Route::get('/rooms',                        [ApiRoomController::class, 'myRooms']);
    Route::get('/rooms/{room}',                 [ApiRoomController::class, 'ownerShow']);
    Route::post('/rooms',                       [ApiRoomController::class, 'store']);
    Route::put('/rooms/{room}',                 [ApiRoomController::class, 'update']);
    Route::post('/rooms/{room}',                [ApiRoomController::class, 'update']); // Multipart photo/video edits
    Route::delete('/rooms/{room}',              [ApiRoomController::class, 'destroy']);
    Route::post('/rooms/{room}/toggle-status',  [ApiRoomController::class, 'toggleStatus']);
    Route::post('/rooms/{room}/booked',          [ApiRoomController::class, 'markBooked']);
    Route::post('/rooms/{room}/available',       [ApiRoomController::class, 'markAvailable']);
    Route::post('/rooms/{room}/feature',        [ApiRoomController::class, 'makeFeatured']);
});
