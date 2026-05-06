<?php

namespace App\Http\Controllers;

use App\Models\DeliveryHistory;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeliveryHistoryController extends Controller
{
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $history = DeliveryHistory::find($id);

        if (!$history) {
            return ApiResponse::error('Delivery history not found', 404);
        }

        $history->update(['status' => $request->status]);

        return ApiResponse::success($history, 'Status updated successfully');
    }
}
