<?php

namespace App\Http\Controllers;

use App\Models\Sell;
use App\Utils\ApiResponse;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sell::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nojual', 'like', "%{$search}%")
                  ->orWhere('nofaktur', 'like', "%{$search}%")
                  ->orWhere('pelanggan_id', 'like', "%{$search}%")
                  ->orWhere('po', 'like', "%{$search}%");
            });
        }

        // Filter by pelanggan_id
        if ($request->has('pelanggan_id')) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        // Filter by sales_id
        if ($request->has('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tgl', [$request->start_date, $request->end_date]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $sell = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($sell, 'Data sell retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nojual' => 'required|string|unique:tbjual,nojual',
            'tgl' => 'nullable|date',
            'pelanggan_id' => 'nullable|string',
            'grandtotal' => 'nullable|numeric',
            'subtotal' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $sell = Sell::create($request->all());

        return ApiResponse::created($sell, 'Sell created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $sell = Sell::find($id);

        if (!$sell) {
            return ApiResponse::notFound('Sell not found');
        }

        return ApiResponse::success($sell, 'Sell retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $sell = Sell::find($id);

        if (!$sell) {
            return ApiResponse::notFound('Sell not found');
        }

        $validator = Validator::make($request->all(), [
            'nojual' => 'sometimes|string|unique:tbjual,nojual,' . $id . ',nojual',
            'tgl' => 'nullable|date',
            'pelanggan_id' => 'nullable|string',
            'grandtotal' => 'nullable|numeric',
            'subtotal' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $sell->update($request->all());

        return ApiResponse::success($sell, 'Sell updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $sell = Sell::find($id);

        if (!$sell) {
            return ApiResponse::notFound('Sell not found');
        }

        $sell->delete();

        return ApiResponse::success(null, 'Sell deleted successfully');
    }

    public function getPickupItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'iduser'           => 'required|string',
            'assigned_floor'   => 'required_unless:iduser,admin|array',
            'assigned_floor.*' => 'string|in:1,2,3',
            'status_pickup'    => 'required_if:iduser,admin|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $iduser = $request->input('iduser');

        if ($iduser === 'admin') {
            $statusPickup = $request->input('status_pickup');
            $statusspb = match ($statusPickup) {
                'pending' => 'print',
                'selesai' => 'ambil',
                default   => null,
            };

            $listInvoice = DB::select(
                "select nojual, status_pickup from tbjual where status_pickup = ? and statusspb = ? order by created_at desc",
                [$statusPickup, $statusspb]
            );

            $data = [];
            foreach ($listInvoice as $invoice) {
                $items = $this->fetchAllItemsForInvoice($invoice->nojual);
                $data[] = [
                    'nojual'        => $invoice->nojual,
                    'status_pickup' => $invoice->status_pickup,
                    'list_barang'   => array_map(fn($item) => array_merge(
                        $this->mapPickupItem($item, withBatchInfo: true),
                        [
                            'lantai'       => $this->resolveFloor($item),
                            'diambil'      => $item->diambil,
                            'waktu_ambil'  => $item->waktu_ambil,
                            'iduser_ambil' => $item->iduser_ambil,
                        ]
                    ), $items),
                ];
            }

            return ApiResponse::success($data, 'Pickup items retrieved successfully');
        }

        $assignedFloor = $request->input('assigned_floor');

        $allSections = [
            '1'  => ['kategori' => 'obat',  'lantai' => '1',  'locators' => ['A','B','C','D'], 'notIn' => false],
            '2a' => ['kategori' => 'obat',  'lantai' => '2a', 'locators' => ['F','G','H'],     'notIn' => false],
            '2b' => ['kategori' => 'obat',  'lantai' => '2b', 'locators' => ['E'],             'notIn' => false],
            '2c' => ['kategori' => 'alkes', 'lantai' => '2c', 'locators' => ['E'],             'notIn' => false],
            '3'  => ['kategori' => 'alkes', 'lantai' => '3',  'locators' => ['E'],             'notIn' => true],
        ];

        $sections = array_filter($allSections, fn($key) => in_array(strval($key)[0], $assignedFloor), ARRAY_FILTER_USE_KEY);

        $listInvoice = DB::select(
            "select nojual from tbjual where statusspb='print' and status_pickup='pending' order by created_at asc limit 1"
        );

        $data = [];

        foreach ($listInvoice as $invoice) {
            $nojual = $invoice->nojual;

            foreach ($sections as $section) {
                $items = $this->fetchItemsByLocator($nojual, $section['kategori'], $section['locators'], $section['notIn']);
                if (count($items) > 0) {
                    $data[] = $this->buildFloorSection($nojual, $section['kategori'], $section['lantai'], $items);

                    $noList = array_column($items, 'no');
                    DB::table('tbbarangrinci')
                        ->where('notransaksi', $nojual)
                        ->whereIn('no', $noList)
                        ->update(['diambil' => 'P']);
                }
            }
        }

        return ApiResponse::success($data, 'Pickup items retrieved successfully');
    }


    public function getPickupItemsByQrCode(string $qrCode): JsonResponse
    {
        // qrcode format: {floor}-SPB-{nojual}, example: 1-SPB-P-0725-000004
        $split = explode('-SPB-', $qrCode);
        $floor = $split[0];
        $nojual = $split[1];

        $data = null;
        $itemType = substr($nojual, 0, 1) === 'P' ? 'obat' : 'alkes';

        if ($itemType === 'obat') {
            if ($floor === '1') {
                $items = $this->fetchItemsByLocator($nojual, 'obat', ['A','B','C','D']);
            } elseif ($floor === '2') {
                $floor2a = $this->fetchItemsByLocator($nojual, 'obat', ['F','G','H']);
                $floor2b = $this->fetchItemsByLocator($nojual, 'obat', ['E']);
                $items = array_merge($floor2a, $floor2b);
            }
        } else {
            if ($floor === '3') {
                $items = $this->fetchItemsByLocator($nojual, 'alkes', ['E'], notIn: true);
            } else {
                $items = $this->fetchItemsByLocator($nojual, 'alkes', ['E']);
            }
        }

        if (!empty($items)) {
            $data = $this->buildFloorSection($nojual, $itemType, $floor, $items, withBatchInfo: true);
        }

        return ApiResponse::success($data, 'Pickup items retrieved successfully');
    }

    private function resolveFloor(object $item): string
    {
        $firstChar = strtoupper(substr($item->locator, 0, 1));

        if ($item->kategori === 'obat') {
            if (in_array($firstChar, ['A','B','C','D'])) return '1';
            if (in_array($firstChar, ['E','F','G','H'])) return '2';
        } else {
            if ($firstChar === 'E') return '2';
            return '3';
        }

        return '-';
    }

    private function fetchAllItemsForInvoice(string $nojual): array
    {
        return DB::select(
            "select tbr.*, tb.nama_barang, tb.satuan, tb.kategori, tb.sqty
             from tbbarangrinci tbr
             left join tbbarang tb on tbr.barang_id = tb.barang_id
             where tbr.notransaksi = ?
             order by tbr.locator, tb.nama_barang asc",
            [$nojual]
        );
    }

    private function fetchItemsByLocator(string $nojual, string $kategori, array $locators, bool $notIn = false): array
    {
        $locatorList = implode("','", $locators);
        $operator = $notIn ? 'not in' : 'in';

        return DB::select(
            "select tbr.*, tb.nama_barang, tb.satuan, tb.kategori, tb.sqty
             from tbbarangrinci tbr
             left join tbbarang tb on tbr.barang_id = tb.barang_id
             where tbr.notransaksi = ? and tb.kategori = ?
             and SUBSTR(tbr.locator, 1, 1) $operator ('$locatorList')
             and tbr.diambil = 'N'
             order by tbr.locator, tb.nama_barang asc",
            [$nojual, $kategori]
        );
    }

    private function buildFloorSection(string $nojual, string $kategori, string $lantai, array $items, bool $withBatchInfo = true): array
    {
        return [
            'nojual'      => $nojual,
            'kategori'    => $kategori,
            'lantai'      => $lantai,
            'no_qr'       => "$lantai-SPB-$nojual",
            'list_barang' => array_map(fn($item) => $this->mapPickupItem($item, $withBatchInfo), $items),
        ];
    }

    private function mapPickupItem(object $item, bool $withBatchInfo = true): array
    {
        $mapped = [
            'no'          => $item->no,
            'barang_id'   => $item->barang_id,
            'nama_barang' => $item->nama_barang,
            'kategori'    => $item->kategori,
            'jumlah'      => $this->formatItemQuantity($item),
            'locator'     => $item->locator,
        ];

        if ($withBatchInfo) {
            $mapped['no_batch'] = $item->no_batch;
            $mapped['nobd']     = $item->nobd;
            $mapped['expired']  = $item->expired;
        }

        return $mapped;
    }

    private function formatItemQuantity(object $item): string
    {
        $c = intval($item->jlh / $item->qty);
        $p = $item->jlh % $item->qty;

        if ($c === 0) {
            return "$p $item->sqty";
        } elseif ($p === 0) {
            return "$c $item->satuan";
        }
        return "$c $item->satuan/$p $item->sqty";
    }

    public function updatePickupItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'iduser' => 'required|string',
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $qr_code = $request->input('qr_code');
        $iduser = $request->input('iduser');

        // Update status ambil
        $this->updateStatusAmbil($qr_code, $iduser);
    
        $nojual = explode('-SPB-', $qr_code)[1];
        
        $items = $request->input('items');

        // Validate input
        if (!is_array($items)) {
            return ApiResponse::validationError(['items' => 'Items must be an array']);
        }

        Log::info("Updating pickup items for nojual: $nojual", ['items' => $items]);

        foreach ($items as $item) {
            Log::info("Processing item", ['item' => $item]);
            if (!isset($item['nobd']) || !isset($item['waktu_ambil'])) {
                return ApiResponse::validationError(['items' => 'Each item must have nobd and waktu_ambil']);
            }

            // Update the item in the database (example using Eloquent)
            DB::table('tbbarangrinci')
                ->where('notransaksi', $nojual)
                ->where('nobd', $item['nobd'])
                ->update(['diambil' => 'Y', 'waktu_ambil' => $item['waktu_ambil'], 'iduser_ambil' => $iduser]);
        }

        $remainingItems = DB::table('tbbarangrinci')
            ->where('notransaksi', $nojual)
            ->where('diambil', 'N')
            ->count();

        if ($remainingItems === 0) {
            DB::table('tbjual')
                ->where('nojual', $nojual)
                ->update(['status_pickup' => 'selesai']);
        }

        return ApiResponse::success(null, 'Pickup items updated successfully');
    }
    
    
    public function cancelPickupItems(Request $request): JsonResponse
    {
        Log::info("Cancel pickup items request received", ['request' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'iduser'  => 'required|string',
            'items'   => 'required|array',
            'items.*.nobd' => 'required|string',
        ]);


        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $nojual = explode('-SPB-', $request->input('qr_code'))[1];
        $nobdList = array_column($request->input('items'), 'nobd');

        Log::info("Cancelling pickup items for nojual: $nojual", ['nobd_list' => $nobdList]);

        DB::table('tbbarangrinci')
            ->where('notransaksi', $nojual)
            ->whereIn('nobd', $nobdList)
            ->where('diambil', 'P')
            ->update(['diambil' => 'N']);

        return ApiResponse::success(null, 'Pickup items cancellation successful');
    }


    public function updateStatusAmbil($no_qr, $iduser)
    {
        try {
            DB::beginTransaction();

            $act = 'ambil';
            // $iduser = 'aling';
            $tgl = date("Y-m-d H:i:s");

            $distribusi_print = substr($no_qr, 0, 1); //ambl digit pertama
            if (is_numeric($distribusi_print)) { //cek digit pertama angka bukan?
                $no = substr($no_qr, 2); //jika angka balikin ke nojualnya
            } else {
                $distribusi_print = 0;
            }

            
            
            $sqlcj = "select * from tbjual where nospb = '$no'";
            // $querycj = mysql_query($sqlcj);
            // $nom = mysql_num_rows($querycj);
            $recj = DB::select($sqlcj);
            $recj = $recj[0];
            $distribusi_lantai = $recj->distribusi_lantai;
            $cek_distribusi_print = $recj->distribusi_print; ///cek distribusi print yang ditentukan
            $cek_distribusi_ambil = $recj->distribusi_ambil; ///cek sdh ambil sampai lantai brp
            if ($recj) {
                $sql = "select * from tbspb_history where no = '$no' and status = '$act'";
                // $query = mysql_query($sql);
                $query = DB::select($sql);
                $num = count($query);

                if ($num == 0) {
                    // $num=0;
                    // Get the last inserted id and increment by 1
                    $lastIdResult = DB::select("SELECT COALESCE(MAX(id), 0) as max_id FROM tbspb_history");
                    $nextId = $lastIdResult[0]->max_id + 1;
                    
                    $sql1 = "INSERT INTO `tbspb_history` (`id`, `no`, `status`, `user`, `kepada`) VALUES ($nextId, '$no',  '$act', '$iduser', '');";
                    DB::insert($sql1);
                    ///untuk isi tgl dan jam nya
                    //	$tgl = date("Y-m-d H:i:s");
                    if ($act == 'ambil') {
                        if ($cek_distribusi_print != ($cek_distribusi_ambil + $distribusi_print)) { //jika blm selesai, jgn update status spb, ttp print aja
                            $act = "print";
                        }
                        $tambahan = ", ambil='$tgl', distribusi_ambil = distribusi_ambil+$distribusi_print, distribusi_lantai = concat(distribusi_lantai,',',$distribusi_print)";
                    }

                    $sql2 = "UPDATE `tbjual` SET `statusspb` = '$act' $tambahan WHERE `tbjual`.`nospb` = '$no'";
                    DB::update($sql2);
                }
            } else {
                if ($act == 'ambil') {
                    $cari_scan = strpbrk($distribusi_lantai, $distribusi_print); //cek ada yg sama ga, kl tdk baru diinput
                    if (!$cari_scan) {
                        if ($cek_distribusi_print != ($cek_distribusi_ambil + $distribusi_print)) { //jika blm selesai, jgn update status spb, ttp print aja
                            $act = "print";
                        }
                        $tambahan = ", ambil='$tgl', distribusi_ambil = distribusi_ambil+$distribusi_print, distribusi_lantai = concat(distribusi_lantai,',',$distribusi_print)";
                        $sql2 = "UPDATE `tbjual` SET `statusspb` = '$act' $tambahan WHERE `tbjual`.`nospb` = '$no'";
                        DB::update($sql2);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in updateStatusAmbil: ' . $e->getMessage());
            throw $e;
        }
    }
}
