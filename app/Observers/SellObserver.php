<?php

namespace App\Observers;

use App\Models\Sales;
use App\Models\Sell;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class SellObserver
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle the Sell "created" event.
     */
    public function created(Sell $sell): void
    {
        // Only send notification if sales_id is present
        if (!$sell->sales_id) {
            Log::info('No sales_id found for sell: ' . $sell->nojual);
            return;
        }

        try {
            // Find sales with matching sales_id and FCM token
            $sales = Sales::where('sales_id', $sell->sales_id)
                ->whereNotNull('fcm_token')
                ->get();

            if ($sales->isEmpty()) {
                Log::info("No sales with FCM tokens found for sales_id: {$sell->sales_id}");
                return;
            }

            // Prepare notification data
            $title = 'New Sell Order';
            $body = "New sell order #{$sell->nojual} has been created";
            $data = [
                'type' => 'new_sell',
                'nojual' => $sell->nojual,
                'sales_id' => $sell->sales_id,
                'grandtotal' => (string) $sell->grandtotal,
                'tgl' => $sell->tgl?->format('Y-m-d') ?? '',
            ];

            // Send notification to each sales
            foreach ($sales as $sale) {
                $this->firebaseService->sendToToken(
                    $sale->fcm_token,
                    $title,
                    $body,
                    $data
                );
            }

            Log::info("FCM notifications sent for sell #{$sell->nojual} to {$sales->count()} sales(s)");
        } catch (\Exception $e) {
            Log::error('Error sending FCM notification: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Sell "updated" event.
     */
    public function updated(Sell $sell): void
    {
        // You can add notification logic for updates if needed
    }

    /**
     * Handle the Sell "deleted" event.
     */
    public function deleted(Sell $sell): void
    {
        // You can add notification logic for deletions if needed
    }
}
