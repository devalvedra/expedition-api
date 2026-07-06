<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryDashboardController;

// Delivery Dashboard
Route::redirect('/', '/delivery');
Route::resource('delivery', DeliveryDashboardController::class);
Route::get('delivery/{delivery}/status', [DeliveryDashboardController::class, 'statusCheck'])
    ->name('delivery.statusCheck');


