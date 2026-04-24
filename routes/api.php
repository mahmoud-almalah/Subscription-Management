<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterTenantController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Plan\PlanController;
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

Route::apiResource('plans', PlanController::class)
    ->middleware([
        'auth:sanctum',
        'tenant',
    ]);

Route::apiResource('customers', CustomerController::class)
    ->middleware([
        'auth:sanctum',
        'tenant',
    ]);
