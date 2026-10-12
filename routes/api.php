<?php

use App\Http\Controllers\ReadingController;
use App\Http\Controllers\RoomHardwareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// The Pi sensor service sends readings here (protected by the X-API-Key header)
Route::post('/readings', [ReadingController::class, 'store']);
Route::post('/rooms/sync', [RoomHardwareController::class, 'sync']);
