<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PickupController extends Controller
{
    /**
     * Get pickup list with pending status by sales_id.
     */
    public function getPendingBySalesId(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError(
                $validator->errors(),
                'Validation Error'
            );
        }

        $salesId = $request->sales_id;

        // Get pickups with pending status for the sales_id
        $pickups = Pickup::where('sales_id', $salesId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success(
            $pickups,
            'Pending pickups retrieved successfully'
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pickup::query();

        // Filter by sales_id
        if ($request->has('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by lantai
        if ($request->has('lantai')) {
            $query->where('lantai', $request->lantai);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $pickups = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($pickups, 'Pickup list retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nojual' => 'required|string',
            'sales_id' => 'required|string',
            'kategori' => 'required|string',
            'lantai' => 'required|string',
            'list_barang' => 'required|array',
            'status' => 'nullable|in:pending,selesai,batal',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError(
                $validator->errors(),
                'Validation Error'
            );
        }

        $pickup = Pickup::create($request->all());

        return ApiResponse::success(
            $pickup,
            'Pickup created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $pickup = Pickup::find($id);

        if (!$pickup) {
            return ApiResponse::notFound('Pickup not found');
        }

        return ApiResponse::success($pickup, 'Pickup retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $pickup = Pickup::find($id);

        if (!$pickup) {
            return ApiResponse::notFound('Pickup not found');
        }

        $validator = Validator::make($request->all(), [
            'nojual' => 'sometimes|string',
            'sales_id' => 'sometimes|string',
            'kategori' => 'sometimes|string',
            'lantai' => 'sometimes|string',
            'list_barang' => 'sometimes|array',
            'status' => 'sometimes|in:pending,selesai,batal',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError(
                $validator->errors(),
                'Validation Error'
            );
        }

        $pickup->update($request->all());

        return ApiResponse::success($pickup, 'Pickup updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $pickup = Pickup::find($id);

        if (!$pickup) {
            return ApiResponse::notFound('Pickup not found');
        }

        $pickup->delete();

        return ApiResponse::success(null, 'Pickup deleted successfully');
    }
}
