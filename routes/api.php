<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusesController;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes untuk Bus

// Jika ingin mengakses route dengan autentikasi
Route::middleware('auth:api')->group(function () {
    Route::get('buses', [BusesController::class, 'index']);
    Route::post('buses', [BusesController::class, 'store']);
    Route::get('buses/{id}', [BusesController::class, 'show']);
    Route::put('buses/{id}', [BusesController::class, 'update']);
    Route::delete('buses/{id}', [BusesController::class, 'destroy']);
});

// Jika ingin mengakses route tanpa autentikasi
// Route::prefix('buses')->group(function () {
//     Route::get('/', [BusesController::class, 'index']);
//     Route::post('/', [BusesController::class, 'store']);
//     Route::get('/{id}', [BusesController::class, 'show']);
//     Route::put('/{id}', [BusesController::class, 'update']);
//     Route::delete('/{id}', [BusesController::class, 'destroy']);
// });



// Route::middleware('auth:api')->group(function () {
//     Route::get('user-profile', function () {
//         return auth()->user();
//     });
// });
