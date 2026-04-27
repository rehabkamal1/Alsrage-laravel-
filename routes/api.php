<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ExternalOfficeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\SaudiOfficeController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/clients/search', [ClientController::class, 'search']);
    Route::post('/clients/quick', [ClientController::class, 'quickStore']);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('saudi-offices', SaudiOfficeController::class);
    Route::apiResource('external-offices', ExternalOfficeController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('orders', OrderController::class)->except('show');
    Route::apiResource('order-tracking', OrderTrackingController::class);
    Route::apiResource('transactions', TransactionController::class);
});