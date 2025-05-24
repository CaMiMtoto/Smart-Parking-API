<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ParkingSessionController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\RateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

//Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/parking/active', [ParkingSessionController::class, 'active']);
    Route::post('/parking/check-in', [ParkingSessionController::class, 'checkIn']);
    Route::post('/parking/preview-check-out', [ParkingSessionController::class, 'previewCheckOut']);
    Route::post('/parking/checkout/{session}', [ParkingSessionController::class, 'checkOut']);
    Route::get('/parking-report', [ReportController::class, 'report']);

//});




Route::get('/rates', [RateController::class, 'index']);


