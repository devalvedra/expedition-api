<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum DELIVERY_STATUS: string
{
    case PROCESS = 'PROSES';
    case LOADED = 'DIMUAT';
    case IN_DELIVERY = 'SEDANG_DIKIRIM';
    case IN_TRANSIT = 'TRANSIT';
    case COMPLETED = 'SELESAI';

    public function label(): string
    {
        return match($this) {
            self::PROCESS     => 'Proses',
            self::LOADED      => 'Dimuat',
            self::IN_DELIVERY => 'Sedang Dikirim',
            self::IN_TRANSIT  => 'Transit',
            self::COMPLETED   => 'Selesai',
        };
    }
}

class Delivery extends Model
{

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
