<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingsControllers;
use App\Http\Controllers\BusesController;
use App\Http\Controllers\RoutesController;
use App\Http\Controllers\SchedulesController;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('user-profile', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Routes untuk entity buses
Route::middleware('auth:api')->group(function () {
    Route::get('buses', [BusesController::class, 'index']);
    Route::post('buses', [BusesController::class, 'store']);
    Route::get('buses/{id}', [BusesController::class, 'show']);
    Route::put('buses/{id}', [BusesController::class, 'update']);
    Route::delete('buses/{id}', [BusesController::class, 'destroy']);
});

// Routes untuk entity routes
Route::middleware('auth:api')->group(function () {
    Route::get('routes', [RoutesController::class, 'index']);
    Route::post('routes', [RoutesController::class, 'store']);
    Route::get('routes/{id}', [RoutesController::class, 'show']);
    Route::put('routes/{id}', [RoutesController::class, 'update']);
    Route::delete('routes/{id}', [RoutesController::class, 'destroy']);
});

// Route untuk entity schedules
Route::middleware('auth:api')->group(function () {
    Route::get('schedules', [SchedulesController::class, 'index']);
    Route::post('schedules', [SchedulesController::class, 'store']);
    Route::get('schedules/{id}', [SchedulesController::class, 'show']);
    Route::put('schedules/{id}', [SchedulesController::class, 'update']);
    Route::delete('schedules/{id}', [SchedulesController::class, 'destroy']);
});

// Route untuk entity bookinngs
Route::middleware('auth:api')->group(function () {
    Route::get('bookings', [BookingsControllers::class, 'index']);
    Route::post('bookings', [BookingsControllers::class, 'store']);
    Route::get('bookings/{id}', [BookingsControllers::class, 'show']);
    Route::put('bookings/{id}', [BookingsControllers::class, 'update']);
    Route::delete('bookings/{id}', [BookingsControllers::class, 'destroy']);
});


// Route::middleware('auth:api')->group(function () {
//     Route::get('user-profile', function () {
//         return auth()->user();
//     });
// });
