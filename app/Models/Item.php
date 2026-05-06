<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'tbbarang';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'last_updated';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'kode',
        'barang_id',
        'kodeobatres',
        'kodekemasan',
        'nm_barang_resmi',
        'kodeobatpom',
        'nama_barang',
        'merk',
        'komposisi',
        'indikasi',
        'warna',
        'golongan',
        'jenis',
        'harga_jual',
        'disc',
        'jlh_stok',
        'expired',
        'no_batch',
        'modedisc',
        'harga_beli',
        'netto',
        'harga_bebas',
        'harga_resep',
        'harga_bebas_besar',
        'harga_resep_besar',
        'harga_distribusi',
        'harga_distribusi_cash',
        'harga_panel',
        'totalpcs',
        'satuan',
        'qty',
        'cat',
        'ket',
        'efek_samping',
        'gambar',
        'sqty',
        'norak',
        'kategori',
        'dosis',
        'ppn',
        'jasa',
        'aktif',
        'pabrik',
        'supplier',
        'serving',
        'barcode',
        'tgl_update_hrg',
        'sync',
        'stokmin',
        'image',
        'status',
        'hpp',
        'bonus',
        'kode_lain',
        'nama_lain',
        'distribusi',
        'berat',
    ];

    protected $casts = [
        'harga_jual' => 'double',
        'disc' => 'double',
        'jlh_stok' => 'double',
        'harga_beli' => 'double',
        'netto' => 'double',
        'harga_bebas' => 'double',
        'harga_resep' => 'double',
        'harga_bebas_besar' => 'double',
        'harga_resep_besar' => 'double',
        'harga_distribusi' => 'double',
        'harga_distribusi_cash' => 'double',
        'harga_panel' => 'double',
        'hpp' => 'double',
        'bonus' => 'double',
        'berat' => 'double',
        'expired' => 'date',
        'tgl_update_hrg' => 'datetime',
        'created_at' => 'datetime',
        'last_updated' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
