<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Sell;
use App\Models\User;
use App\Services\FirebaseService;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FcmTestController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Test sending notification to a specific token
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $result = $this->firebaseService->sendToToken(
            $request->token,
            $request->title,
            $request->body,
            $request->data ?? []
        );

        if ($result) {
            return ApiResponse::success(null, 'Test notification sent successfully');
        }

        return ApiResponse::error('Failed to send notification', 500);
    }

    /**
     * Test sending notification to users with specific sales_id
     */
    public function sendToSalesId(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|string',
            // 'title' => 'required|string|max:255',
            // 'body' => 'required|string',
            // 'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        // Find sales with matching sales_id and FCM token
        $sales = Sales::where('sales_id', $request->sales_id)
            ->whereNotNull('fcm_token')
            ->get();

        if ($sales->isEmpty()) {
            return ApiResponse::notFound('No sales found with this sales_id and FCM token');
        }

        $tokens = $sales->pluck('fcm_token')->toArray();
        
        $results = $this->firebaseService->sendToMultipleTokens(
            $tokens,
            "Pesanan Baru (TEST)",
            "Pesanan baru dengan sales_id {$request->sales_id} telah dibuat (Ini adalah notifikasi tes)",
            []
        );
        // $results = $this->firebaseService->sendToMultipleTokens(
        //     $tokens,
        //     $request->title,
        //     $request->body,
        //     $request->data ?? []
        // );

        return ApiResponse::success($results, "Notifications sent to {$sales->count()} sales(s)");
    }

    /**
     * Test sending notification to users with specific sales_id
     */
    public function sendNotificationWithNoJual(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nojual' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        // Find sales with matching sales_id and FCM token
        $sell = Sell::where('nojual', $request->nojual)
            ->firstOrFail();
        
        if (!$sell) {
            return ApiResponse::notFound('No sell found with this nojual');
        }


        $sales = Sales::where('sales_id', $sell->sales_id)
            ->whereNotNull('fcm_token')
            ->get();

        if ($sales->isEmpty()) {
            return ApiResponse::notFound('No sales found with this sales_id and FCM token');
        }

        $tokens = $sales->pluck('fcm_token')->toArray();
        
        $results = $this->firebaseService->sendToMultipleTokens(
            $tokens,
            "Pesanan Baru",
            "Pesanan baru dengan no jual {$request->nojual} telah dibuat",
            []
        );
        // $results = $this->firebaseService->sendToMultipleTokens(
        //     $tokens,
        //     $request->title,
        //     $request->body,
        //     $request->data ?? []
        // );

        return ApiResponse::success($results, "Notifications sent to {$sales->count()} sales(s)");
    }

    /**
     * Test sending notification to a topic
     */
    public function sendToTopic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $result = $this->firebaseService->sendToTopic(
            $request->topic,
            $request->title,
            $request->body,
            $request->data ?? []
        );

        if ($result) {
            return ApiResponse::success(null, 'Notification sent to topic successfully');
        }

        return ApiResponse::error('Failed to send notification to topic', 500);
    }

    /**
     * Update user's FCM token
     */
    public function updateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
            'sales_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $user = Sales::find($request->user_id);
        $user->fcm_token = $request->fcm_token;
        
        if ($request->has('sales_id')) {
            $user->sales_id = $request->sales_id;
        }
        
        $user->save();

        return ApiResponse::success($user, 'FCM token updated successfully');
    }

    /**
     * Subscribe tokens to a topic
     */
    public function subscribeToTopic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tokens' => 'required|array',
            'tokens.*' => 'required|string',
            'topic' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $result = $this->firebaseService->subscribeToTopic(
            $request->tokens,
            $request->topic
        );

        if ($result) {
            return ApiResponse::success(null, 'Tokens subscribed to topic successfully');
        }

        return ApiResponse::error('Failed to subscribe tokens to topic', 500);
    }

    /**
     * Unsubscribe tokens from a topic
     */
    public function unsubscribeFromTopic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tokens' => 'required|array',
            'tokens.*' => 'required|string',
            'topic' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $result = $this->firebaseService->unsubscribeFromTopic(
            $request->tokens,
            $request->topic
        );

        if ($result) {
            return ApiResponse::success(null, 'Tokens unsubscribed from topic successfully');
        }

        return ApiResponse::error('Failed to unsubscribe tokens from topic', 500);
    }

    /**
     * Simulate new sell creation to test notification
     */
    public function simulateNewSell(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|string',
            'nojual' => 'required|string',
            'grandtotal' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        // Find users with matching sales_id and FCM token
        $users = Sales::where('sales_id', $request->sales_id)
            ->whereNotNull('fcm_token')
            ->get();

        if ($users->isEmpty()) {
            return ApiResponse::notFound('No users found with this sales_id and FCM token');
        }

        // Prepare notification data
        $title = 'New Sell Order (TEST)';
        $body = "New sell order #{$request->nojual} has been created (This is a test notification)";
        $data = [
            'type' => 'new_sell_test',
            'nojual' => $request->nojual,
            'sales_id' => $request->sales_id,
            'grandtotal' => (string) ($request->grandtotal ?? 0),
            'tgl' => now()->format('Y-m-d'),
        ];

        $tokens = $users->pluck('fcm_token')->toArray();
        
        $results = $this->firebaseService->sendToMultipleTokens(
            $tokens,
            $title,
            $body,
            $data
        );

        return ApiResponse::success(
            array_merge($results, ['users_notified' => $users->count()]),
            "Test notification sent for simulated sell #{$request->nojual}"
        );
    }
}
