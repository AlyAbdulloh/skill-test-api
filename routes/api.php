<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseResidentController;
use App\Http\Controllers\PaymentBillController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::prefix('reports')->group(function (){
        Route::get('summary', [ReportController::class, 'index']);
        Route::get('detail', [ReportController::class, 'detail']);
    });

    Route::apiResource('residents', ResidentController::class);
    Route::get('houses/{house}/residents', [HouseController::class, 'residents']);
    Route::apiResource('houses', HouseController::class);
    Route::apiResource('house-residents', HouseResidentController::class);
    Route::apiResource('fee-types', FeeTypeController::class);
    Route::apiResource('payment-bills', PaymentBillController::class);
    Route::apiResource('expenses', ExpenseController::class);
});


