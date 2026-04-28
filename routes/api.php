<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController; 
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\AiSuggestionController;

Route::post('/ai/suggest', [AiSuggestionController::class, 'suggest']);

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password Reset Routes
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);
Route::get('/verify-reset-token', [PasswordResetController::class, 'verifyToken']);

// Public Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Protected User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    
    // Payment Routes
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/payment/process', [PaymentController::class, 'processPayment']);
    Route::get('/payment/verify/{orderId}', [PaymentController::class, 'verifyPayment']);
});

// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
    
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

    Route::get('/analytics', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index']);
    Route::get('/finance', [App\Http\Controllers\Admin\AdminFinanceController::class, 'index']);

    // Profile Management
    Route::get('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Admin\AdminProfileController::class, 'update']);
    Route::post('/profile/avatar', [App\Http\Controllers\Admin\AdminProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [App\Http\Controllers\Admin\AdminProfileController::class, 'removeAvatar']);
    Route::put('/profile/password', [App\Http\Controllers\Admin\AdminProfileController::class, 'changePassword']);
});