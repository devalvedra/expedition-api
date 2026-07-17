<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryDetail;
use App\Models\DeliveryHistory;
use App\Models\DELIVERY_STATUS;
use App\Models\Vendor;
use App\Models\ShippingCost;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /**
     * GET /api/deliveries?search=INV001&status=DIMUAT&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = Delivery::with(['pbf', 'deliveryDetails']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('no_resi', 'like', "%{$search}%")
                    ->orWhere('no_faktur', 'like', "%{$search}%")
                    ->orWhere('penerima', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereIn('no_resi', DeliveryHistory::query()
                        ->select('no_resi')
                        ->where('no_kendaraan', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);

        return ApiResponse::success(
            $query->orderBy('created_at', 'desc')->paginate($perPage),
            'Data delivery retrieved successfully'
        );
    }

    /**
     * POST /api/delivery
     * {
    *   "no_faktur": "INV001",
     *   "penerima": "VEND001",
     *   "pengirim": "VEND002",
     *   "status": "DIMUAT",
     *   "vehicle_no": "B 1234 CD",
     *   "iduser": "admin01",
        *   "items": [
        *     { "koli": 2, "ukuran": "BESAR", "harga": 150000 },
        *     { "koli": 1, "ukuran": "KECIL", "harga": 50000 }
        *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'no_faktur' => 'required|string|max:255|unique:tbinvoice,no_faktur',
            'no_resi' => 'nullable|string|max:255|unique:tbinvoice,no_resi',
            'penerima' => 'nullable|string|exists:tbvendor,kodevendor',
            'kode_vendor' => 'nullable|string|exists:tbvendor,kodevendor',
            'kode_pbf' => 'nullable|string|exists:tbvendor,kodevendor',
            'pengirim' => 'nullable|string|exists:tbvendor,kodevendor',
            'username' => 'nullable|string|max:255',
            'iduser' => 'nullable|string|max:255',
            'nilai_faktur' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.koli' => 'required_with:items|numeric|min:0',
            'items.*.ukuran' => 'required_with:items|string|max:255',
            'items.*.harga' => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $vendorCode = $request->input('penerima')
            ?? $request->input('kode_vendor')
            ?? $request->input('kode_pbf');

        if (!$vendorCode) {
            return ApiResponse::validationError([
                'penerima' => ['penerima (or kode_vendor/kode_pbf) is required'],
            ]);
        }

        $delivery = new Delivery();
        $delivery->no_faktur = $request->input('no_faktur');
        $delivery->no_resi = $request->filled('no_resi')
            ? $request->input('no_resi')
            : $this->generateNoResi(Delivery::class);
        $delivery->penerima = $vendorCode;
        $delivery->pengirim = $request->input('pengirim');
        $delivery->pelanggan_id = $request->input('penerima');
        $delivery->tgl_terima = now()->toDateString();
        $delivery->ditagih_ke = 'Penerima'; // hardcoded
        $delivery->pay = 'Credit'; // hardcoded
        $delivery->nilai_faktur = $request->input('nilai_faktur', 0);

        // calculate grandtotal based on items if provided
        $subtotal = 0;
        if ($request->filled('items')) {
            foreach ($request->input('items') as $item) {
                $subtotal += ($item['koli']) * ($item['harga']);
            }
        }
        

        $delivery->subtotal = $subtotal; // calculated based on items
        $delivery->nominal_diskon = 0; // hardcoded
        $delivery->persen_diskon = 0; // hardcoded
        $delivery->nominal_pajak = 0; // hardcoded
        $delivery->sisa = $subtotal - $delivery->nominal_diskon + $delivery->nominal_pajak; // calculated
        $delivery->grandtotal = 0;
        $delivery->status = DELIVERY_STATUS::PROCESS->value;
        $delivery->iduser = $request->input('iduser', $request->input('username'));
        $delivery->save();

        $this->syncDeliveryDetails($delivery->no_resi, $request->input('items', []));

        $this->createHistory(
            $delivery->no_resi,
            $delivery->pengirim,
            DELIVERY_STATUS::PROCESS->value,
            $request->input('iduser', $request->input('username')),
        );

        return ApiResponse::created($delivery, 'Delivery created successfully');
    }

    /**
     * GET /api/deliveries/{id}
     * Example: GET /api/deliveries/RESI001
     */
    public function show(string $id): JsonResponse
    {
        $delivery = Delivery::with(['pbf', 'deliveryDetails'])
            ->where('no_resi', $id)
            ->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        return ApiResponse::success($delivery, 'Delivery retrieved successfully');
    }

    /**
     * PUT/PATCH /api/deliveries/{id}
     * {
     *   "penerima": "VEND001",
     *   "pengirim": "VEND002",
     *   "status": "SEDANG_DIKIRIM",
     *   "vehicle_no": "B 1234 CD",
    *   "iduser": "admin01",
    *   "items": [
    *     { "koli": 2, "ukuran": "BESAR", "harga": 150000 },
    *     { "koli": 1, "ukuran": "KECIL", "harga": 50000 }
    *   ]
     * }
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $delivery = Delivery::with('deliveryDetails')
            ->where('no_resi', $id)
            ->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $validator = Validator::make($request->all(), [
            'penerima' => 'sometimes|string|exists:tbvendor,kodevendor',
            'kode_vendor' => 'sometimes|string|exists:tbvendor,kodevendor',
            'kode_pbf' => 'sometimes|string|exists:tbvendor,kodevendor',
            'pengirim' => 'nullable|string|exists:tbvendor,kodevendor',
            'status' => 'sometimes|string|in:' . implode(',', array_map(fn ($s) => $s->value, DELIVERY_STATUS::cases())),
            'vehicle_no' => 'nullable|string|max:255',
            'no_kendaraan' => 'nullable|string|max:255',
            'iduser' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.koli' => 'required_with:items|numeric|min:0',
            'items.*.ukuran' => 'required_with:items|string|max:255',
            'items.*.harga' => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $vendorCode = $request->input('penerima')
            ?? $request->input('kode_vendor')
            ?? $request->input('kode_pbf');

        if ($vendorCode !== null) {
            $delivery->penerima = $vendorCode;
        }

        if ($request->has('pengirim')) {
            $delivery->pengirim = $request->input('pengirim');
        }

        if ($request->has('status')) {
            $delivery->status = $request->input('status');
        }

        if ($request->has('iduser') || $request->has('username')) {
            $delivery->iduser = $request->input('iduser', $request->input('username'));
        }

        $delivery->save();

        if ($request->has('items')) {
            DeliveryDetail::where('no_resi', $delivery->no_resi)->delete();
            $this->syncDeliveryDetails($delivery->no_resi, $request->input('items', []));
        }

        if ($request->filled('vehicle_no') || $request->filled('no_kendaraan')) {
            $this->createHistory(
                $delivery,
                $delivery->status,
                $request->input('iduser', $request->input('username')),
                $request->input('vehicle_no', $request->input('no_kendaraan'))
            );
        }

        return ApiResponse::success($delivery->fresh(['pbf', 'deliveryDetails']), 'Delivery updated successfully');
    }

    /**
     * DELETE /api/deliveries/{id}
     * Example: DELETE /api/deliveries/RESI001
     */
    public function destroy(string $id): JsonResponse
    {
        $delivery = Delivery::where('no_resi', $id)
            ->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $delivery->delete();

        return ApiResponse::success(null, 'Delivery deleted successfully');
    }

    /**
     * POST /api/deliveries/load-items-into-vehicle
     * {
     *   "invoices": ["INV001", "INV002"],
     *   "vehicle_no": "B 1234 CD",
     *   "username": "admin01"
     * }
     */
    public function loadItemsIntoVehicle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoices' => 'required|array',
            'invoices.*' => 'string|max:255',
            'vehicle_no' => 'required|string|max:255',
            'kodevendor' => 'required|string|max:255',
            'username' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $deliveries = Delivery::whereIn('no_resi', $request->invoices)
            ->orWhereIn('no_faktur', $request->invoices)
            ->get();

        if ($deliveries->isEmpty()) {
            return ApiResponse::notFound('No deliveries found for the given invoices');
        }

        $histories = [];

        foreach ($deliveries as $delivery) {
            $delivery->status = DELIVERY_STATUS::LOADED->value;
            $delivery->no_kendaraan = $request->vehicle_no;
            $delivery->save();

            $histories[] = $this->createHistory(
                $delivery->no_resi,
                $request->kodevendor,
                DELIVERY_STATUS::LOADED->value,
                $request->username,
                $request->vehicle_no
            );
        }

        return ApiResponse::success($histories, 'Items loaded into vehicle successfully');
    }

    /**
     * POST /api/deliveries/update-status-by-invoice
     * {
     *   "invoice_no": "INV001",
     *   "status": "SELESAI",
     *   "username": "admin01"
     * }
     */
    public function updateStatusByInvoice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', array_map(fn ($s) => $s->value, DELIVERY_STATUS::cases())),
            'username' => 'required|string|max:255',
            'invoice_no' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $delivery = Delivery::where('no_resi', $request->invoice_no)
            ->orWhere('no_faktur', $request->invoice_no)
            ->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        $delivery->status = $request->status;
        $delivery->save();

        $history = $this->createHistory(
            $delivery,
            $request->status,
            $request->username,
            $this->latestVehicleNo($delivery->no_resi)
        );

        return ApiResponse::success($history, 'Status updated successfully');
    }

    /**
     * POST /api/deliveries/update-status-by-vehicle
     * {
     *   "vehicle_no": "B 1234 CD",
     *   "status": "TRANSIT",
     *   "username": "admin01"
     * }
     */
    public function updateStatusByVehicle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', array_map(fn ($s) => $s->value, DELIVERY_STATUS::cases())),
            'username' => 'required|string|max:255',
            'vehicle_no' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $noResis = DeliveryHistory::query()
            ->where('no_kendaraan', $request->vehicle_no)
            ->pluck('no_resi')
            ->unique()
            ->values();

        if ($noResis->isEmpty()) {
            return ApiResponse::notFound('No deliveries found for the given vehicle number');
        }

        $deliveries = Delivery::whereIn('no_resi', $noResis)->get();
        $histories = [];

        foreach ($deliveries as $delivery) {
            $delivery->status = $request->status;
            $delivery->save();

            $histories[] = $this->createHistory(
                $delivery,
                $request->status,
                $request->username,
                $request->vehicle_no
            );
        }

        return ApiResponse::success($histories, 'Status updated successfully');
    }

    /**
     * GET /api/deliveries/route
     * Example: GET /api/deliveries/route
     */
    public function getDeliveryRoute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vehicle_no' => 'required|string|max:255',
            'iduser' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }


        $vehicleNo = $request->input('vehicle_no');
        $idUser = $request->input('iduser');

        $deliveries = Delivery::with(['pbf', 'deliveryDetails'])
            ->where('no_kendaraan', $vehicleNo)
            ->whereIn('status', [DELIVERY_STATUS::LOADED->value, DELIVERY_STATUS::IN_DELIVERY->value, DELIVERY_STATUS::IN_TRANSIT->value])
            ->get();

        if ($deliveries->isEmpty()) {
            return ApiResponse::notFound('Delivery not found');
        }

        $lastPositionDelivery = DeliveryHistory::query()
            ->where('no_kendaraan', $vehicleNo)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPositionDelivery) {
            return ApiResponse::notFound('No deliveries found for the given vehicle number');
        }

        // set the first route point from the last position of the vehicle
        $routePoints = [
            [
                'drop_point_code' => $lastPositionDelivery->kode_vendor,
                'drop_point_name' => $this->vendorDisplayName($lastPositionDelivery->delivery->pbf),
                'sequence' => 1,
                'lat' => $this->vendorCoordinate(($lastPositionDelivery->delivery->pbf), 'lat'),
                'lng' => $this->vendorCoordinate($lastPositionDelivery->delivery->pbf, 'lng'),
                'invoices' => [],
            ],
        ];



        foreach ($deliveries as $delivery) {
            $jual = DB::table('tbjual')
                ->where('nofaktur', $delivery->no_faktur)
                ->first();


            // if ($jual) {
            //     $rows = DB::table('tbbarangrinci as tbr')
            //         ->leftJoin('tbbarang as tb', 'tbr.barang_id', '=', 'tb.barang_id')
            //         ->where('tbr.notransaksi', $jual->nojual)
            //         ->orderBy('tbr.locator')
            //         ->orderBy('tb.nama_barang')
            //         ->select([
            //             'tbr.no',
            //             'tbr.barang_id',
            //             'tbr.jlh',
            //             'tbr.locator',
            //             'tbr.no_batch',
            //             'tbr.nobd',
            //             'tbr.expired',
            //             'tb.nama_barang',
            //             'tb.satuan',
            //             'tb.sqty',
            //             'tb.qty',
            //             'tb.kategori',
            //         ])
            //         ->get();

            //     foreach ($rows as $item) {
            //         $qty = intval($item->qty ?: 1);
            //         $c = intval($item->jlh / $qty);
            //         $p = $item->jlh % $qty;

            //         if ($c === 0) {
            //             $jumlah = "{$p} {$item->sqty}";
            //         } elseif ($p === 0) {
            //             $jumlah = "{$c} {$item->satuan}";
            //         } else {
            //             $jumlah = "{$c} {$item->satuan}/{$p} {$item->sqty}";
            //         }

            //         $items[] = [
            //             'no' => $item->no,
            //             'barang_id' => $item->barang_id,
            //             'nama_barang' => $item->nama_barang,
            //             'kategori' => $item->kategori,
            //             'jumlah' => $jumlah,
            //             'locator' => $item->locator,
            //             'no_batch' => $item->no_batch,
            //             'nobd' => $item->nobd,
            //             'expired' => $item->expired,
            //         ];
            //     }
            // }



            // update delivery status to "SEDANG_DIKIRIM" if it's still "DIMUAT"
            if ($delivery->status === DELIVERY_STATUS::LOADED->value or $delivery->status === DELIVERY_STATUS::IN_TRANSIT->value) {
                $delivery->status = DELIVERY_STATUS::IN_DELIVERY->value;
                $delivery->save();

                $this->createHistory(
                    $delivery->no_resi,
                    $lastPositionDelivery->kode_vendor,
                    DELIVERY_STATUS::IN_DELIVERY->value,
                    $idUser,
                    $vehicleNo
                );
            }

            $routePoints[] = [
                'drop_point_code' => $delivery->penerima,
                'drop_point_name' => $this->vendorDisplayName($delivery->pbf) ?? $delivery->penerima,
                'sequence' => count($routePoints) + 1,
                'lat' => $this->vendorCoordinate($delivery->pbf, 'lat'),
                'lng' => $this->vendorCoordinate($delivery->pbf, 'lng'),
                'status' => $delivery->status,
                'invoices' => [
                    [
                        'no_faktur' => $delivery->no_faktur,
                        'no_resi' => $delivery->no_resi,
                        'vehicle_no' => $this->latestVehicleNo($delivery->no_resi),
                        'items' => $delivery->deliveryDetails->map(function ($detail) {
                            return [
                                'koli' => $detail->koli,
                                'ukuran' => $detail->ukuran,
                                'harga' => $detail->harga,
                            ];
                        })->values(),
                    ],
                ],
            ];
        }

        return ApiResponse::success([
            'code' => 'Rute',
            'name' => 'Rute Pengiriman',
            'route_points' => $routePoints,
        ], 'Route retrieved successfully');
    }

    /**
     * GET /api/deliveries/items/{invoiceNo}
     * Example: GET /api/deliveries/items/RESI001
     */
    public function getItemsByInvoice($invoiceNo): JsonResponse
    {
        $delivery = Delivery::with(['pbf', 'deliveryDetails'])
            ->where('no_resi', $invoiceNo)
            ->orWhere('no_faktur', $invoiceNo)
            ->first();

        if (!$delivery) {
            return ApiResponse::notFound('Delivery not found');
        }

        return ApiResponse::success([
            'no_faktur' => $delivery->no_faktur,
            'no_resi' => $delivery->no_resi,
            'kode_vendor' => $delivery->penerima,
            'nama_vendor' => $this->vendorDisplayName($delivery->pbf),
            'vehicle_no' => $this->latestVehicleNo($delivery->no_resi),
            'delivery_details' => $delivery->deliveryDetails->map(function ($detail) {
                return [
                    'koli' => $detail->koli,
                    'ukuran' => $detail->ukuran,
                    'harga' => $detail->harga,
                ];
            })->values(),
        ], 'Delivery items retrieved successfully');
    }

    /**
     * Helper: resolve the best available vendor display name.
     */
    private function vendorDisplayName(?Vendor $vendor): ?string
    {
        if (!$vendor) {
            return null;
        }

        foreach (['nama_vendor', 'nama', 'namavendor', 'nama_pbf'] as $field) {
            if (isset($vendor->{$field}) && $vendor->{$field} !== null && $vendor->{$field} !== '') {
                return $vendor->{$field};
            }
        }

        return $vendor->kodevendor;
    }

    /**
     * Helper: resolve a numeric vendor coordinate when available.
     */
    private function vendorCoordinate(?Vendor $vendor, string $key): ?float
    {
        if (!$vendor) {
            return null;
        }

        if (isset($vendor->{$key}) && is_numeric($vendor->{$key})) {
            return (float) $vendor->{$key};
        }

        return null;
    }

    /**
     * Helper: create a delivery history record, optionally storing vehicle number.
     */
    private function createHistory(string $noResi, string $kodeVendor, string $status, ?string $idUser, ?string $vehicleNo = null): DeliveryHistory
    {
        return DeliveryHistory::create([
            'no_resi' => $noResi,
            'kode_vendor' => $kodeVendor,
            'no_kendaraan' => $vehicleNo,
            'status' => $status,
            'iduser' => $idUser,
        ]);
    }

    /**
     * Helper: fetch the latest vehicle assignment for a delivery resi.
     */
    private function latestVehicleNo(string $noResi): ?string
    {
        return DeliveryHistory::query()
            ->where('no_resi', $noResi)
            ->whereNotNull('no_kendaraan')
            ->orderByDesc('created_at')
            ->value('no_kendaraan');
    }

    /**
     * Store one DeliveryDetail row per item payload.
     */
    private function syncDeliveryDetails(string $noResi, array $items): void
    {
        foreach ($items as $item) {
            DeliveryDetail::create([
                'no_resi' => $noResi,
                'koli' => $item['koli'],
                'ukuran' => $item['ukuran'],
                'harga' => $item['harga'],
                'total' => ($item['koli']) * ($item['harga']),
            ]);
        }
    }

    /**
     * Generate the next no_resi value from the delivery table.
     * Format: YYYY/MM/DD/NNNNN — index continues from the last stored number.
     */
    private function generateNoResi(string $currentTable = 'tbinvoice'): string
    {
        $table = $currentTable === Delivery::class ? (new Delivery())->getTable() : $currentTable;

        $todayPrefix = now()->format('Y/m/d') . '/';

        $lastResi = DB::table($table)
            ->where('no_resi', 'like', $todayPrefix . '%')
            ->orderByDesc('no_resi')
            ->value('no_resi');

        $nextIndex = 1;
        if ($lastResi) {
            $lastIndex = (int) substr($lastResi, strlen($todayPrefix));
            if ($lastIndex > 0) {
                $nextIndex = $lastIndex + 1;
            }
        }

        return sprintf('%s/%s/%s/%05d', now()->format('Y'), now()->format('m'), now()->format('d'), $nextIndex);
    }

    /**
     * GET /api/deliveries/shipping-cost
     * {
     *   "sender": "VEND001",
     *   "destination": "VEND002"
     * }
     */
    public function getShippingCost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sender' => 'required|string|exists:tbvendor,kodevendor',
            'destination' => 'required|string|exists:tbvendor,kodevendor',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $destinationCity = Vendor::where('kodevendor', $request->destination)->first()->kota;

        $shippingCost = ShippingCost::where('kodevendor', $request->sender)
            ->where('kota', $destinationCity)
            ->first();

        if (!$shippingCost) {
            return ApiResponse::notFound('Shipping cost not found for the given vendor and city');
        }

        return ApiResponse::success($shippingCost, 'Shipping cost retrieved successfully');

    }
}
