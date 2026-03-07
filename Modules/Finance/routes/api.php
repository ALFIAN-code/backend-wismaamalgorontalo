<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/invoice/{invoiceId}/pay', [PaymentController::class, 'pay']);
});
