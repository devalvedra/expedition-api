<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryDashboardController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\PbfDashboardController;
use App\Http\Controllers\TrackingController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// PBF Dashboard
Route::resource('pbf', PbfDashboardController::class)->except(['show']);

// Item Dashboard
Route::get('/item', [ItemDashboardController::class, 'index'])->name('item.index');

// Delivery Dashboard
Route::resource('delivery', DeliveryDashboardController::class);
Route::get('delivery/{delivery}/status', [DeliveryDashboardController::class, 'statusCheck'])
    ->name('delivery.statusCheck');

// Public Tracking
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/tracking/{invoice}', [TrackingController::class, 'show'])->name('tracking.show');


