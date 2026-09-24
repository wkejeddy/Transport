<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\TripApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\CheckinApiController;

Route::prefix('v1')->group(function () {
    Route::get('/trips/search', [TripApiController::class, 'search']);
    Route::get('/trips/{trip}', [TripApiController::class, 'show']);
    Route::get('/shipments/track/{code}', [ShipmentApiController::class, 'track']);
    Route::post('/trips/{trip}/checkin', [CheckinApiController::class, 'scan']);
});

// Mobile Money Webhook Callbacks
Route::post('/payments/momo/callback', [PaymentWebhookController::class, 'mtnMomoCallback']);
Route::post('/payments/om/callback', [PaymentWebhookController::class, 'orangeMoneyCallback']);
