<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ShippingCost extends Model
{

    protected $table = 'tbharga';

    protected $fillable = [
        'kodevendor', # varchar
        'kota', # varchar
        'kecil', # double
        'sedang', # double
        'besar', # double
        'xl', # double
        'vaksin_kecil', # double
        'vaksin_besar', # double
        'dll', # double
    ];

    public function city()
    {
        return $this->belongsTo(MasterData::class, 'kota', 'kota')->where('kategori', 'kota');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'kodevendor', 'kodevendor');
    }
}
