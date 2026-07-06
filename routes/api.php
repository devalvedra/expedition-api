<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\VendorController;;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Eshia API is running',
        'version' => '1.0.0'
    ]);
});

// Delivery CRUD endpoints
Route::prefix('delivery')->group(function () {
    Route::get('/', [DeliveryController::class, 'index']);
    Route::post('/', [DeliveryController::class, 'store']);
    Route::post('/get-delivery-route', [DeliveryController::class, 'getDeliveryRoute']);
    Route::post('/load-items-into-vehicle', [DeliveryController::class, 'loadItemsIntoVehicle']);
    Route::post('/update-status-by-invoice', [DeliveryController::class, 'updateStatusByInvoice']);
    Route::post('/update-status-by-vehicle', [DeliveryController::class, 'updateStatusByVehicle']);
    Route::get('/{id}', [DeliveryController::class, 'show']);
    Route::put('/{id}', [DeliveryController::class, 'update']);
    Route::delete('/{id}', [DeliveryController::class, 'destroy']);
});


Route::prefix('vendor')->group(function() {
    Route::get('/get-pbf-list', [VendorController::class, 'getPbfList']);
});