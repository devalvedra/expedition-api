<?php

namespace App\Http\Controllers;

use App\Models\Pbf;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PbfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pbf::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_pbf', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $pbf = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($pbf, 'Data PBF retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_pbf' => 'required|string|max:255',
            'nama_pbf' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $data = $request->only(['kode_pbf', 'nama_pbf', 'alamat', 'lat', 'lng']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('pbf', 'public');
        }

        $pbf = Pbf::create($data);

        return ApiResponse::created($pbf, 'PBF created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $pbf = Pbf::find($id);

        if (!$pbf) {
            return ApiResponse::notFound('PBF not found');
        }

        return ApiResponse::success($pbf, 'PBF retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $pbf = Pbf::find($id);

        if (!$pbf) {
            return ApiResponse::notFound('PBF not found');
        }

        $validator = Validator::make($request->all(), [
            'kode_pbf' => 'sometimes|string|max:255',
            'nama_pbf' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string|max:255',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $data = $request->only(['kode_pbf', 'nama_pbf', 'alamat', 'lat', 'lng']);

        if ($request->hasFile('image')) {
            if ($pbf->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pbf->image_path);
            }
            $data['image_path'] = $request->file('image')->store('pbf', 'public');
        }

        $pbf->update($data);

        return ApiResponse::success($pbf, 'PBF updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $pbf = Pbf::find($id);

        if (!$pbf) {
            return ApiResponse::notFound('PBF not found');
        }

        $pbf->delete();

        return ApiResponse::success(null, 'PBF deleted successfully');
    }
}
