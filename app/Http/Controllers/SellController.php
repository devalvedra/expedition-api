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

    public function getPickupItems(): JsonResponse
    {
        $listInvoice = DB::select("select nojual from tbjual where sales_id='Aling' order by tgl desc");
        // $nojual = 'P-0725-000004';

        $data = [];

        foreach ($listInvoice as $invoice) {
            $nojual = $invoice->nojual;

            // Obat lantai 1
            $floor1 = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('A','B','C','D')
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");
            // Obat lantai 2
            $floor2a = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('F','G','H')
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");
            $floor2b = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('E')
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");
                
            // Alkes lantai 3
            $floor3 = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='alkes'	and (SUBSTR(tbr.locator, 1, 1) not in ('E'))
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");
            // Alkes lantai 2
            $floor2c = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='alkes'	and (SUBSTR(tbr.locator, 1, 1) in ('E'))
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");

            if (count($floor1) > 0) {
                $data[] = [
                    'nojual' => $nojual,
                    'kategori' => 'obat',
                    'lantai' => '1',
                    'list_barang' => array_map(function($item) {

                        $c = intval($item->jlh / $item->qty);
                        $p = $item->jlh % $item->qty;


                        if ($c == 0) {
                            $jlhbrg = "$p $item->sqty";
                        } elseif ($p == 0) {
                            $jlhbrg = "$c $item->satuan";
                        } else {
                            $jlhbrg = "$c $item->satuan/$p $item->sqty";
                        }

                        return [
                            'no' => $item->no,
                            'barang_id' => $item->barang_id,
                            'nama_barang' => $item->nama_barang,
                            'kategori' => $item->kategori,
                            'jumlah' => $jlhbrg,
                            'locator' => $item->locator,
                        ];
                    }, $floor1),
                ];
            }
            if (count($floor2a) > 0) {
                $data[] = [
                    'nojual' => $nojual,
                    'kategori' => 'obat',
                    'lantai' => '2a',
                    'list_barang' => array_map(function($item) {
                        $c = intval($item->jlh / $item->qty);
                        $p = $item->jlh % $item->qty;


                        if ($c == 0) {
                            $jlhbrg = "$p $item->sqty";
                        } elseif ($p == 0) {
                            $jlhbrg = "$c $item->satuan";
                        } else {
                            $jlhbrg = "$c $item->satuan/$p $item->sqty";
                        }

                        return [
                            'no' => $item->no,
                            'barang_id' => $item->barang_id,
                            'nama_barang' => $item->nama_barang,
                            'kategori' => $item->kategori,
                            'jumlah' => $jlhbrg,
                            'locator' => $item->locator,
                        ];
                    }, $floor2a),
                ];
            }
            if (count($floor2b) > 0) {
                $data[] = [
                    'nojual' => $nojual,
                    'kategori' => 'obat',
                    'lantai' => '2b',
                    'list_barang' => array_map(function($item) {
                        $c = intval($item->jlh / $item->qty);
                        $p = $item->jlh % $item->qty;


                        if ($c == 0) {
                            $jlhbrg = "$p $item->sqty";
                        } elseif ($p == 0) {
                            $jlhbrg = "$c $item->satuan";
                        } else {
                            $jlhbrg = "$c $item->satuan/$p $item->sqty";
                        }

                        return [
                            'no' => $item->no,
                            'barang_id' => $item->barang_id,
                            'nama_barang' => $item->nama_barang,
                            'kategori' => $item->kategori,
                            'jumlah' => $jlhbrg,
                            'locator' => $item->locator,
                        ];
                    }, $floor2b),
                ];
            }
            if (count($floor2c) > 0) {
                $data[] = [
                    'nojual' => $nojual,
                    'kategori' => 'alkes',
                    'lantai' => '2c',
                    'list_barang' => array_map(function($item) {
                        $c = intval($item->jlh / $item->qty);
                        $p = $item->jlh % $item->qty;


                        if ($c == 0) {
                            $jlhbrg = "$p $item->sqty";
                        } elseif ($p == 0) {
                            $jlhbrg = "$c $item->satuan";
                        } else {
                            $jlhbrg = "$c $item->satuan/$p $item->sqty";
                        }

                        return [
                            'no' => $item->no,
                            'barang_id' => $item->barang_id,
                            'nama_barang' => $item->nama_barang,
                            'kategori' => $item->kategori,
                            'jumlah' => $jlhbrg,
                            'locator' => $item->locator,
                        ];
                    }, $floor2c),
                ];
            }
            if (count($floor3) > 0) {
                $data[] = [
                    'nojual' => $nojual,
                    'kategori' => 'alkes',
                    'lantai' => '3',
                    'list_barang' => array_map(function($item) {
                        $c = intval($item->jlh / $item->qty);
                        $p = $item->jlh % $item->qty;


                        if ($c == 0) {
                            $jlhbrg = "$p $item->sqty";
                        } elseif ($p == 0) {
                            $jlhbrg = "$c $item->satuan";
                        } else {
                            $jlhbrg = "$c $item->satuan/$p $item->sqty";
                        }

                        return [
                            'no' => $item->no,
                            'barang_id' => $item->barang_id,
                            'nama_barang' => $item->nama_barang,
                            'kategori' => $item->kategori,
                            'jumlah' => $jlhbrg,
                            'locator' => $item->locator,
                        ];
                    }, $floor3),
                ];
            }
        }    

        return ApiResponse::success($data, 'Pickup items retrieved successfully');
    }


    public function getPickupItemsByQrCode(string $qrCode): JsonResponse
    {
        // qrcode format: {floor}-{nospb}, example: 1-SPB-P-0725-000004
        $split = explode('-SPB-', $qrCode);
        $floor = $split[0];
        $nojual = $split[1];

        $data = null;

        $itemType = substr($nojual, 0, 1) == 'P' ? 'obat' : 'alkes';

        if ($itemType === 'obat') {
            if ($floor == '1') {
                // Obat lantai 1
                $floor1 = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                        from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                        where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('A','B','C','D')
                        and tbr.diambil='N'
                        order by tbr.locator ,tb.nama_barang asc");

                if (count($floor1) > 0) {
                    $data = [
                        'nojual' => $nojual,
                        'kategori' => 'obat',
                        'lantai' => '1',
                        'list_barang' => array_map(function($item) {

                            $c = intval($item->jlh / $item->qty);
                            $p = $item->jlh % $item->qty;


                            if ($c == 0) {
                                $jlhbrg = "$p $item->sqty";
                            } elseif ($p == 0) {
                                $jlhbrg = "$c $item->satuan";
                            } else {
                                $jlhbrg = "$c $item->satuan/$p $item->sqty";
                            }

                            return [
                                'no' => $item->no,
                                'barang_id' => $item->barang_id,
                                'nama_barang' => $item->nama_barang,
                                'kategori' => $item->kategori,
                                'jumlah' => $jlhbrg,
                                'locator' => $item->locator,
                                'no_batch' => $item->no_batch,
                                'nobd' => $item->nobd,
                                'expired' => $item->expired,
                            ];
                        }, $floor1),
                    ];
                }
            }
            elseif ($floor == '2') {
        
                // Obat lantai 2
                $floor2a = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                            from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                            where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('F','G','H')
                            and tbr.diambil='N'
                            order by tbr.locator ,tb.nama_barang asc");
                $floor2b = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                            from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                            where tbr.notransaksi='$nojual' and tb.kategori='obat'	and SUBSTR(tbr.locator, 1, 1) in ('E')
                            and tbr.diambil='N'
                            order by tbr.locator ,tb.nama_barang asc");

                $floor2 = array_merge($floor2a, $floor2b);

                if (count($floor2) > 0) {
                    $data = [
                        'nojual' => $nojual,
                        'kategori' => 'obat',
                        'lantai' => '2',
                        'list_barang' => array_map(function($item) {
                            $c = intval($item->jlh / $item->qty);
                            $p = $item->jlh % $item->qty;


                            if ($c == 0) {
                                $jlhbrg = "$p $item->sqty";
                            } elseif ($p == 0) {
                                $jlhbrg = "$c $item->satuan";
                            } else {
                                $jlhbrg = "$c $item->satuan/$p $item->sqty";
                            }

                            return [
                                'no' => $item->no,
                                'barang_id' => $item->barang_id,
                                'nama_barang' => $item->nama_barang,
                                'kategori' => $item->kategori,
                                'jumlah' => $jlhbrg,
                                'locator' => $item->locator,
                                'no_batch' => $item->no_batch,
                                'nobd' => $item->nobd,
                                'expired' => $item->expired,
                            ];
                        }, $floor2),
                    ];
                }
            }
        }
        else {
            if ($floor == '3') {
                // Alkes lantai 3
                $floor3 = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                            from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                            where tbr.notransaksi='$nojual' and tb.kategori='alkes'	and (SUBSTR(tbr.locator, 1, 1) not in ('E'))
                            and tbr.diambil='N'
                            order by tbr.locator ,tb.nama_barang asc");

                if (count($floor3) > 0) {
                    $data = [
                        'nojual' => $nojual,
                        'kategori' => 'alkes',
                        'lantai' => '3',
                        'list_barang' => array_map(function($item) {
                            $c = intval($item->jlh / $item->qty);
                            $p = $item->jlh % $item->qty;


                            if ($c == 0) {
                                $jlhbrg = "$p $item->sqty";
                            } elseif ($p == 0) {
                                $jlhbrg = "$c $item->satuan";
                            } else {
                                $jlhbrg = "$c $item->satuan/$p $item->sqty";
                            }

                            return [
                                'no' => $item->no,
                                'barang_id' => $item->barang_id,
                                'nama_barang' => $item->nama_barang,
                                'kategori' => $item->kategori,
                                'jumlah' => $jlhbrg,
                                'locator' => $item->locator,
                                'no_batch' => $item->no_batch,
                                'nobd' => $item->nobd,
                                'expired' => $item->expired,
                            ];
                        }, $floor3),
                    ];
                }
            }
            else {
                  // Alkes lantai 2
                $floor2c = DB::select("select tbr.*, tb.nama_barang,tb.satuan,tb.kategori,tb.sqty 
                            from tbbarangrinci tbr left join tbbarang tb on tbr.barang_id = tb.barang_id 
                            where tbr.notransaksi='$nojual' and tb.kategori='alkes'	and (SUBSTR(tbr.locator, 1, 1) in ('E'))
                            and tbr.diambil='N'
                            order by tbr.locator ,tb.nama_barang asc");

                if (count($floor2c) > 0) {
                    $data = [
                        'nojual' => $nojual,
                        'kategori' => 'alkes',
                        'lantai' => '2',
                        'list_barang' => array_map(function($item) {
                            $c = intval($item->jlh / $item->qty);
                            $p = $item->jlh % $item->qty;


                            if ($c == 0) {
                                $jlhbrg = "$p $item->sqty";
                            } elseif ($p == 0) {
                                $jlhbrg = "$c $item->satuan";
                            } else {
                                $jlhbrg = "$c $item->satuan/$p $item->sqty";
                            }

                            return [
                                'no' => $item->no,
                                'barang_id' => $item->barang_id,
                                'nama_barang' => $item->nama_barang,
                                'kategori' => $item->kategori,
                                'jumlah' => $jlhbrg,
                                'locator' => $item->locator,
                                'no_batch' => $item->no_batch,
                                'nobd' => $item->nobd,
                                'expired' => $item->expired,
                            ];
                        }, $floor2c),
                    ];
                }
            }
        }

        return ApiResponse::success($data, 'Pickup items retrieved successfully');
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
                return ApiResponse::validationError(['items' => 'Each item must have barang_id, nobd, and waktu_ambil']);
            }

            // Update the item in the database (example using Eloquent)
            DB::table('tbbarangrinci')
                ->where('notransaksi', $nojual)
                ->where('nobd', $item['nobd'])
                ->update(['diambil' => 'Y', 'waktu_ambil' => $item['waktu_ambil']]);
        }

        return ApiResponse::success(null, 'Pickup items updated successfully');
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
