<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Accounting\AccountingController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterTenantController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Invoice\InvoiceController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Plan\PlanController;
use App\Http\Controllers\Api\Subscription\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    'middleware' => ['guest'],
], function () {
    Route::post('register', RegisterTenantController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
    Route::post('logout', LogoutController::class)
        ->name('logout')
        ->withoutMiddleware('guest')
        ->middleware('auth:sanctum');
});

Route::middleware([
    'auth:sanctum',
    'tenant',
])->group(function () {
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('accounts', AccountingController::class);
});
