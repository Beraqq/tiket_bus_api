<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusesController;
use App\Http\Controllers\RoutesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SchedulesController;
use Psy\Command\HistoryCommand;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('user-profile', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Routes untuk entity buses
Route::middleware('auth:api')->group(function () {
    Route::get('buses', [BusController::class, 'index']);
    Route::post('buses', [BusController::class, 'store']);
    Route::get('buses/class/{class}', [BusController::class, 'getByClass']);
    Route::get('buses/{id}', [BusController::class, 'show']);
    Route::put('buses/{id}', [BusController::class, 'update']);
    Route::delete('buses/{id}', [BusController::class, 'destroy']);
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
    Route::get('schedules', [ScheduleController::class, 'index']);
    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::get('schedules/{id}', [ScheduleController::class, 'show']);
    Route::put('schedules/{id}', [ScheduleController::class, 'update']);
    Route::delete('schedules/{id}', [ScheduleController::class, 'destroy']);
    Route::get('schedules/available/{busCode}/{date}', [ScheduleController::class, 'getAvailable']);
});

// Route untuk entity bookinngs
Route::middleware('auth:api')->group(function () {
    // Endpoint untuk cek ketersediaan kursi
    Route::get('/bookings/check-availability/{scheduleId}', [BookingController::class, 'checkAvailability']);

    // CRUD Booking
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
});

// Route untuk entity paymnets
Route::middleware('auth:api')->group(function () {
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);
    Route::put('payments/{id}', [PaymentController::class, 'update']);
    Route::delete('payments/{id}', [PaymentController::class, 'destroy']);
});

// Route untuk entity tickets
Route::middleware('auth:api')->group(function () {
    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::put('tickets/{id}', [TicketController::class, 'update']);
    Route::delete('tickets/{id}', [TicketController::class, 'destroy']);
});

// Route untuk entity histories
Route::middleware('auth:api')->group(function () {
    Route::get('histories', [HistoryController::class, 'index']);
    Route::post('histories', [HistoryController::class, 'store']);
    Route::get('histories/{id}', [HistoryController::class, 'show']);
    Route::put('histories/{id}', [HistoryController::class, 'update']);
    Route::delete('histories/{id}', [HistoryController::class, 'destroy']);
});


// Route::middleware('auth:api')->group(function () {
//     Route::get('user-profile', function () {
//         return auth()->user();
//     });
// });
