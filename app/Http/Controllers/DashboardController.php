<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Pbf;
use App\Models\Pickup;
use App\Models\Sell;

class DashboardController extends Controller
{
    public function index()
    {
        $pbfCount = Pbf::count();
        $itemCount = Item::count();
        $sellCount = Sell::count();
        $pickupCount = Pickup::count();

        return view('dashboard', compact('pbfCount', 'itemCount', 'sellCount', 'pickupCount'));
    }
}
