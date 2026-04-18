<?php

use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\BillingController;
use App\Http\Controllers\Api\Customer\DashboardController;
use App\Http\Controllers\Api\Customer\DepositController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->group(function () {

    // ── Public ─────────────────────────────────────────────────────
    Route::post('/register',    [AuthController::class, 'register']);
    Route::post('/login',       [AuthController::class, 'login']);
    Route::post('/verify-otp',  [AuthController::class, 'verifyOtp']);

    // ── Protected ──────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::get('/profile',          [ProfileController::class, 'profile']);
        Route::put('/profile',          [ProfileController::class, 'update']);
        Route::get('/dashboard',        [DashboardController::class, 'index']);
        Route::get('/products',         [ProductController::class, 'index']);
        Route::post('/orders',          [OrderController::class, 'store']);
        Route::get('/orders',           [OrderController::class, 'index']);
        Route::get('/orders/{id}',      [OrderController::class, 'show']);
        Route::get('/bills',            [BillingController::class, 'index']);
        Route::get('/due-balance',      [BillingController::class, 'dueBalance']);
        Route::get('/deposits',         [DepositController::class, 'index']);
    });
});
