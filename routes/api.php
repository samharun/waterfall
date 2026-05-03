<?php

use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\BillingController;
use App\Http\Controllers\Api\Customer\DashboardController;
use App\Http\Controllers\Api\Customer\DepositController;
use App\Http\Controllers\Api\Customer\DeviceTokenController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\DeliveryAuthController;
use App\Http\Controllers\Api\DeliveryManagerController;
use App\Http\Controllers\Api\DeliveryNotificationController;
use App\Http\Controllers\Api\DeliveryStaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->group(function () {

    // ── Public ─────────────────────────────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    // ── Protected ──────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'profile']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/emergency-contact', [DashboardController::class, 'emergencyContact']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/bills', [BillingController::class, 'index']);
        Route::get('/due-balance', [BillingController::class, 'dueBalance']);
        Route::get('/deposits', [DepositController::class, 'index']);
        Route::post('/device-token', [DeviceTokenController::class, 'store']);
        Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
    });
});

Route::prefix('delivery')->group(function () {
    Route::post('/login', [DeliveryAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [DeliveryAuthController::class, 'logout']);
        Route::get('/profile', [DeliveryAuthController::class, 'profile']);
        Route::post('/save-fcm-token', [DeliveryNotificationController::class, 'saveFcmToken']);
        Route::post('/delete-fcm-token', [DeliveryNotificationController::class, 'deleteFcmToken']);

        Route::get('/dashboard', [DeliveryStaffController::class, 'dashboard']);
        Route::get('/today', [DeliveryStaffController::class, 'todayDeliveries']);
        Route::post('/update-status', [DeliveryStaffController::class, 'updateStatus']);
        Route::post('/bulk-update', [DeliveryStaffController::class, 'bulkUpdate']);
        Route::post('/location', [DeliveryStaffController::class, 'updateLocation']);
    });
});

Route::prefix('delivery-manager')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/dashboard', [DeliveryManagerController::class, 'dashboard']);
        Route::get('/staff-progress', [DeliveryManagerController::class, 'staffProgress']);
        Route::get('/today-deliveries', [DeliveryManagerController::class, 'todayDeliveries']);
        Route::post('/assign', [DeliveryManagerController::class, 'assign']);
        Route::post('/reassign', [DeliveryManagerController::class, 'reassign']);
        Route::get('/pending-orders', [DeliveryManagerController::class, 'pendingOrders']);
        Route::post('/confirm-order', [DeliveryManagerController::class, 'confirmOrder']);
    });
