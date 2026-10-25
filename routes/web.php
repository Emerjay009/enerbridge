<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TelegramLinkController;
use App\Http\Middleware\EnsureAdministrator;
use App\Http\Middleware\EnsureApproved;
use Illuminate\Support\Facades\Route;

// Public pages (only for people who are not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::view('/pending', 'auth.pending');
});

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('intro'))->name('home');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Pages for approved accounts only
Route::middleware(['auth', EnsureApproved::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data', [DashboardController::class, 'data']);
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
    Route::get('/rooms/{room}/data', [RoomController::class, 'data']);
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit']);
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone']);
    Route::post('/profile/telegram/link', [TelegramLinkController::class, 'generate']);
    Route::post('/profile/telegram/check', [TelegramLinkController::class, 'check']);
    Route::post('/profile/telegram/unlink', [TelegramLinkController::class, 'unlink']);

    // Administrator-only pages
    Route::middleware(EnsureAdministrator::class)->prefix('admin')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index']);
        Route::post('/approvals/{user}/approve', [ApprovalController::class, 'approve']);
        Route::post('/approvals/{user}/reject', [ApprovalController::class, 'reject']);
    });
});
