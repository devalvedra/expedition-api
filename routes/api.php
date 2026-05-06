<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\PbfController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FcmTestController;

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

// Item CRUD endpoints
Route::prefix('item')->group(function () {
    Route::get('/', [ItemController::class, 'index']);
    Route::post('/', [ItemController::class, 'store']);
    Route::get('/{id}', [ItemController::class, 'show']);
    Route::put('/{id}', [ItemController::class, 'update']);
    Route::delete('/{id}', [ItemController::class, 'destroy']);
});

// Alternative using apiResource (uncomment if preferred)
// Route::apiResource('item', ItemController::class);

// Sell CRUD endpoints
Route::prefix('sell')->group(function () {
    Route::get('/', [SellController::class, 'index']);
    Route::get('/pickup-items', [SellController::class, 'getPickupItems']);
    Route::get('/pickup-items/{qrcode}', [SellController::class, 'getPickupItemsByQrCode']);
    Route::put('/update-pickup-items', [SellController::class, 'updatePickupItems']);
    // Route::post('/', [SellController::class, 'store']);
    // Route::get('/{id}', [SellController::class, 'show']);
    // Route::put('/{id}', [SellController::class, 'update']);
    // Route::delete('/{id}', [SellController::class, 'destroy']);
});

// Alternative using apiResource (uncomment if preferred)
// Route::apiResource('sell', SellController::class);

// Pickup CRUD endpoints
Route::prefix('pickup')->group(function () {
    Route::get('/pending', [PickupController::class, 'getPendingBySalesId']);
    Route::get('/', [PickupController::class, 'index']);
    Route::post('/', [PickupController::class, 'store']);
    Route::get('/{id}', [PickupController::class, 'show']);
    Route::put('/{id}', [PickupController::class, 'update']);
    Route::delete('/{id}', [PickupController::class, 'destroy']);
});

// Alternative using apiResource (uncomment if preferred)
// Route::apiResource('pickup', PickupController::class);

// FCM Test endpoints (for development/testing)
Route::prefix('fcm-test')->group(function () {
    Route::post('/send', [FcmTestController::class, 'sendTestNotification']);
    Route::post('/send-to-sales', [FcmTestController::class, 'sendToSalesId']);
    Route::post('/send-with-nojual', [FcmTestController::class, 'sendNotificationWithNoJual']);
    Route::post('/send-to-topic', [FcmTestController::class, 'sendToTopic']);
    Route::post('/update-token', [FcmTestController::class, 'updateToken']);
    Route::post('/subscribe-topic', [FcmTestController::class, 'subscribeToTopic']);
    Route::post('/unsubscribe-topic', [FcmTestController::class, 'unsubscribeFromTopic']);
    Route::post('/simulate-sell', [FcmTestController::class, 'simulateNewSell']);
});

// PBF CRUD endpoints
Route::prefix('pbf')->group(function () {
    Route::get('/', [PbfController::class, 'index']);
    Route::post('/', [PbfController::class, 'store']);
    Route::get('/{id}', [PbfController::class, 'show']);
    Route::put('/{id}', [PbfController::class, 'update']);
    Route::delete('/{id}', [PbfController::class, 'destroy']);
});

// Delivery CRUD endpoints
Route::prefix('delivery')->group(function () {
    Route::get('/', [DeliveryController::class, 'index']);
    Route::get('/get-delivery-route/{vehicleNo}', [DeliveryController::class, 'getDeliveryRoute']);
    Route::post('/', [DeliveryController::class, 'store']);
    Route::post('/load-items-into-vehicle', [DeliveryController::class, 'loadItemsIntoVehicle']);
    Route::post('/update-status-by-invoice', [DeliveryController::class, 'updateStatusByInvoice']);
    Route::post('/update-status-by-vehicle', [DeliveryController::class, 'updateStatusByVehicle']);
    Route::get('/{id}', [DeliveryController::class, 'show']);
    Route::put('/{id}', [DeliveryController::class, 'update']);
    Route::delete('/{id}', [DeliveryController::class, 'destroy']);
});
