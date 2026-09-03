<?php

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\OwnerRoomDraftController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

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
