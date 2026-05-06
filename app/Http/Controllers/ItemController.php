<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Item::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('barang_id', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kategoriRes
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $item = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($item, 'Data item retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|string|unique:tbbarang,barang_id',
            'nama_barang' => 'required|string|max:255',
            'harga_jual' => 'nullable|numeric',
            'harga_beli' => 'nullable|numeric',
            'jlh_stok' => 'nullable|numeric',
            'satuan' => 'nullable|string',
            'kategori' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $item = Item::create($request->all());

        return ApiResponse::created($item, 'Item created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = Item::find($id);

        if (!$item) {
            return ApiResponse::notFound('Item not found');
        }

        return ApiResponse::success($item, 'Item retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $item = Item::find($id);

        if (!$item) {
            return ApiResponse::notFound('Item not found');
        }

        $validator = Validator::make($request->all(), [
            'barang_id' => 'sometimes|string|unique:tbbarang,barang_id,' . $id,
            'nama_barang' => 'sometimes|string|max:255',
            'harga_jual' => 'nullable|numeric',
            'harga_beli' => 'nullable|numeric',
            'jlh_stok' => 'nullable|numeric',
            'satuan' => 'nullable|string',
            'kategori' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $item->update($request->all());

        return ApiResponse::success($item, 'Item updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $item = Item::find($id);

        if (!$item) {
            return ApiResponse::notFound('Item not found');
        }

        $item->delete();

        return ApiResponse::success(null, 'Item deleted successfully');
    }
}
