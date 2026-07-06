<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sell extends Model
{
    use SoftDeletes;

    protected $table = 'tbjual';
    
    protected $primaryKey = 'nojual';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'last_updated';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'nojual',
        'nofaktur',
        'tgl',
        'tgl_pajak',
        'tgl_bu',
        'tgltempo',
        'pelanggan_id',
        'pay',
        'pajak',
        'subtotal',
        'sales_id',
        'grandtotal',
        'sisa',
        'disc',
        'po',
        'iduser',
        'modedisc',
        'kas',
        'cat',
        'reture',
        'gudang_id',
        'service_charge',
        'bayar_cash',
        'bayar_lain',
        'kembalian',
        'mode_service_charge',
        'pay_detail',
        'noresep',
        'kodedokter',
        'modepajak',
        'sync',
        'status',
        'nospb',
        'statusspb',
        'halodoc',
        'peracik',
        'nofpajak',
        'pajak_rp',
        'sp',
        'tgl_temp',
        'ambil',
        'checking',
        'antar',
        'kembali',
        'distribusi_print',
        'distribusi_ambil',
        'distribusi_lantai',
        'upload_coretax',
    ];

    protected $casts = [
        'nofaktur' => 'integer',
        'tgl' => 'date',
        'tgl_pajak' => 'date',
        'tgl_bu' => 'date',
        'tgltempo' => 'date',
        'pajak' => 'double',
        'subtotal' => 'double',
        'grandtotal' => 'double',
        'sisa' => 'double',
        'disc' => 'double',
        'reture' => 'double',
        'service_charge' => 'double',
        'bayar_cash' => 'double',
        'bayar_lain' => 'double',
        'kembalian' => 'double',
        'noresep' => 'integer',
        'pajak_rp' => 'double',
        'tgl_temp' => 'date',
        'ambil' => 'datetime',
        'checking' => 'datetime',
        'antar' => 'datetime',
        'kembali' => 'datetime',
        'distribusi_print' => 'integer',
        'distribusi_ambil' => 'integer',
        'created_at' => 'datetime',
        'last_updated' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'pelanggan_id', 'kodevendor');
    }
}
