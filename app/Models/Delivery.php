<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Delivery extends Model
{

    protected $table = 'tbinvoice';
    protected $primaryKey = 'no_resi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_resi', # varchar
        'tgl_terima', # date
        'penerima', # varchar
        'pengirim', # varchar
        'pelanggan_id', # varchar
        'ditagih_ke', # varchar
        'sisa', # double
        'no_faktur', # varchar
        'nilai_faktur', # double
        'iduser', # varchar 
        'subtotal', # double
        'nominal_diskon', # double
        'persen_diskon', # double
        'nominal_pajak', # double
        'grandtotal', # double
        'keterangan', # text
        'status', # varchar
    ];

    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetail::class, 'no_resi', 'no_resi');
    }

    public function pbf()
    {
        return $this->belongsTo(Vendor::class, 'penerima', 'kodevendor');
    }

    public function pengirim()
    {
        return $this->belongsTo(Vendor::class, 'pengirim', 'kodevendor');
    }
}
