<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PaymentController;

Route::post('/register', [RegisterController::class, 'store']);

// Payment routes
Route::group(['prefix' => 'payment'], function () {
    Route::post('/initialize', [PaymentController::class, 'initializePayment']);
    Route::post('/callback', [PaymentController::class, 'paymentCallback']);
    Route::post('/webhook', [PaymentController::class, 'handleWebhook']);
    Route::get('/status/{reference}', [PaymentController::class, 'getPaymentStatus']);
});


// Configure Webhook:

// Add webhook URL in Paystack dashboard: your-domain.com/api/payment/webhook