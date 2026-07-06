<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{

    public function getPbfList(Request $request): JsonResponse
    {
        $vendors = Vendor::where('status', 'pbf')->get();
        return ApiResponse::success($vendors, 'PBF list retrieved successfully');
    }
}