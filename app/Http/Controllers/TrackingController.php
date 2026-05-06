<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryHistory;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking.index');
    }

    public function show(string $invoice)
    {
        $delivery = Delivery::with('pbf')->find($invoice);

        if (! $delivery) {
            return view('tracking.show', [
                'notFound' => true,
                'invoice'  => $invoice,
            ]);
        }

        $history = DeliveryHistory::where('no_invoice', $invoice)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('tracking.show', compact('delivery', 'history'));
    }
}
