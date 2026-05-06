<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryHistory;
use App\Models\Pbf;
use App\Models\DELIVERY_STATUS;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Delivery::with('pbf');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', "%{$search}%")
                  ->orWhere('kode_pbf', 'like', "%{$search}%")
                  ->orWhere('no_kendaraan', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $deliveries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($deliveries, 'Data delivery retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'no_invoice'           => 'required|string|max:255|unique:tbpengiriman,no_invoice',
            'kode_pbf'             => 'required|string|exists:tbpbf,kode_pbf',
            'jumlah_barang_besar'  => 'required|integer|min:0',
            'jumlah_barang_sedang' => 'required|integer|min:0',
            'jumlah_barang_kecil'  => 'required|integer|min:0',
            'no_kendaraan'         => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $data = $request->only(['no_invoice', 'kode_pbf', 'jumlah_barang_besar', 'jumlah_barang_sedang', 'jumlah_barang_kecil', 'no_kendaraan']);
        $data['status'] = DELIVERY_STATUS::PENDING->value;

        $delivery = Delivery::create($data);

        $history = DeliveryHistory::create([
            'no_invoice' => $delivery->no_invoice,
            'kode_pbf' => $delivery->kode_pbf,
            'status' => 'Menunggu Staff PBF', 
            'username' => $request->username
        ]);

        return ApiResponse::created($delivery, 'Delivery created successfully');
    }

    public function show(string $id): JsonResponse
    {
        $delivery = Delivery::with('pbf')->find($id);

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        // TODO:
        // Generate qr codes to be used in the app
        // The Qr code will be generated using the no_invoice and kode_pbf, and can be used to identify the delivery in the app
        // The format of the qr code will be : {no_invoice|A|kode_pbf|B|jumlah_barang_besar|jumlah_barang_sedang|total_jumlah_barang|index}
        // The example format is : {INV001|A|T-001|B|2|5|00001}

        return ApiResponse::success($delivery, 'Delivery retrieved successfully');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $validator = Validator::make($request->all(), [
            'kode_pbf'             => 'sometimes|string|exists:tbpbf,kode_pbf',
            'jumlah_barang_besar'  => 'sometimes|integer|min:0',
            'jumlah_barang_sedang' => 'sometimes|integer|min:0',
            'jumlah_barang_kecil'  => 'sometimes|integer|min:0',
            'status'               => 'sometimes|string|in:' . implode(',', array_map(fn($status) => $status->value, \App\Models\Delivery\STATUSES::cases())),
            'no_kendaraan'         => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $delivery->update($request->all());

        return ApiResponse::success($delivery, 'Delivery updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $delivery->delete();

        return ApiResponse::success(null, 'Delivery deleted successfully');
    }


    public function loadItemsIntoVehicle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoices' => 'required|array',
            'invoices.*' => 'string|exists:tbpengiriman,no_invoice',
            'vehicle_no' => 'required|string|max:255',
            'username' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $deliveries = Delivery::whereIn('no_invoice', $request->invoices)->get();

        if ($deliveries->isEmpty()) {
            return ApiResponse::notFound('No deliveries found for the given invoices');
        }

        foreach ($deliveries as $delivery) {
            $delivery->no_kendaraan = $request->vehicle_no;
            $delivery->status = DELIVERY_STATUS::WAITING_DRIVER->value; // update status to "Menunggu Supir"
            $delivery->save();

            $history = DeliveryHistory::create([
                'no_invoice' => $delivery->no_invoice,
                'kode_pbf' => $delivery->kode_pbf,
                'status' => DELIVERY_STATUS::LOADED->value, 
                'username' => $request->username
            ]);
        }

        return ApiResponse::success($history, 'Items loaded into vehicle successfully');
    }


    public function updateStatusByInvoice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'invoice_no' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }


        if ($request->invoice_no) {
            $delivery = Delivery::find($request->invoice_no);

            if (!$delivery) {
                return ApiResponse::notFound('Delivery not found');
            }
 

            $delivery->status = $request->status;
            $delivery->save();
        }

        $historyStatus = DELIVERY_STATUS::LOADED->value;
        switch ($request->status) {
            case DELIVERY_STATUS::WAITING_DRIVER->value:
                $historyStatus = DELIVERY_STATUS::LOADED->value;
                break;
            case DELIVERY_STATUS::IN_TRANSIT->value:
                $historyStatus = DELIVERY_STATUS::IN_TRANSIT->value;
                break;
            case DELIVERY_STATUS::COMPLETED->value:
                $historyStatus = DELIVERY_STATUS::COMPLETED->value;
                break;
        }

        $history = DeliveryHistory::create([
            'no_invoice' => $delivery->no_invoice,
            'kode_pbf' => $delivery->kode_pbf,
            'status' => $historyStatus, 
            'username' => $request->username
        ]);

        return ApiResponse::success($history, 'Status updated successfully');
    }

    public function updateStatusByVehicle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'vehicle_no' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        if ($request->vehicle_no) {
            $deliveries = Delivery::where('no_kendaraan', $request->vehicle_no)->get();

            if ($deliveries->isEmpty()) {
                return ApiResponse::notFound('No deliveries found for the given vehicle number');
            }

            foreach ($deliveries as $delivery) {
                $delivery->status = $request->status;
                $delivery->save();

                $historyStatus = DELIVERY_STATUS::LOADED->value;
                switch ($request->status) {
                    case DELIVERY_STATUS::WAITING_DRIVER->value:
                        $historyStatus = DELIVERY_STATUS::LOADED->value;
                        break;
                    case DELIVERY_STATUS::IN_TRANSIT->value:
                        $historyStatus = DELIVERY_STATUS::IN_TRANSIT->value;
                        break;
                    case DELIVERY_STATUS::COMPLETED->value:
                        $historyStatus = DELIVERY_STATUS::COMPLETED->value;
                        break;
                }

                DeliveryHistory::create([
                    'no_invoice' => $delivery->no_invoice,
                    'kode_pbf' => $delivery->kode_pbf,
                    'status' => $historyStatus, 
                    'username' => $request->username
                ]);
            }
        }

        return ApiResponse::success(null, 'Status updated successfully');
    }

    public function getDeliveryRoute($vehicleNo): JsonResponse
    {
        $delivery = Delivery::with('pbf')->where('no_kendaraan', $vehicleNo)->where('status', DELIVERY_STATUS::WAITING_DRIVER->value)->get();

        if ($delivery->isEmpty()) {
            return ApiResponse::notFound('Delivery not found');
        }


        // Always set the first route point to the head office (HO)
        $HO = Pbf::where('kode_pbf', 'HO')->first();

        
        $routePoints = [
            [
                "drop_point_code" => "HO",
                "drop_point_name" => "Head Office",
                "sequence" => 1,
                "lat" => $HO->lat,
                "lng" => $HO->lng
            ],
        ];

        foreach ($delivery as $d) {
            $routePoints[] = [
                "drop_point_code" => $d->kode_pbf,
                "drop_point_name" => $d->pbf->nama_pbf,
                "sequence" => count($routePoints) + 1,
                "lat" => $d->pbf->lat,
                "lng" => $d->pbf->lng
            ];
        }

        $data = [
            'code' => 'Rute',
            'name' => 'Rute Pengiriman',
            'route_points' => $routePoints
        ];


        return ApiResponse::success($data, 'Route retrieved successfully');
    }

    public function getItemsByInvoice($invoiceNo): JsonResponse
    {
        $delivery = Delivery::with('pbf')->where('no_invoice', $invoiceNo)->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $data = [
            'no_invoice' => $delivery->no_invoice,
            'kode_pbf' => $delivery->kode_pbf,
            'nama_pbf' => $delivery->pbf->nama_pbf,
            'jumlah_barang_besar' => $delivery->jumlah_barang_besar,
            'jumlah_barang_sedang' => $delivery->jumlah_barang_sedang,
            'jumlah_barang_kecil' => $delivery->jumlah_barang_kecil,
        ];

        return ApiResponse::success($data, 'Delivery items retrieved successfully');
    }
}
