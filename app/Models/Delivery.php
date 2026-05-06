<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum DELIVERY_STATUS: string{
    case PENDING = 'PENDING';
    case LOADED = 'DIMUAT';
    case WAITING_DRIVER = 'MENUNGGU_SUPIR';
    case IN_TRANSIT = 'SEDANG_DIKIRIM';
    case COMPLETED = 'SELESAI';
}

class Delivery extends Model
{
    const STATUSES = ['PENDING', 'DIMUAT', 'MENUNGGU_SUPIR', 'SEDANG_DIKIRIM', 'SELESAI'];

    const STATUS_LABELS = [
        'PENDING'        => 'Pending',
        'DIMUAT'         => 'Dimuat',
        'MENUNGGU_SUPIR' => 'Menunggu Supir',
        'SEDANG_DIKIRIM' => 'Sedang Dikirim',
        'SELESAI'        => 'Selesai',
    ];

    protected $table = 'tbpengiriman';
    protected $primaryKey = 'no_invoice';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_invoice',
        'kode_pbf',
        'jumlah_barang_besar',
        'jumlah_barang_sedang',
        'jumlah_barang_kecil',
        'status',
        'no_kendaraan',
    ];

    public function pbf()
    {
        return $this->belongsTo(Pbf::class, 'kode_pbf', 'kode_pbf');
    }
}
