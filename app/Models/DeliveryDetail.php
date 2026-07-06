<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDetail extends Model
{

    protected $table = 'tbinvoicedetil';

    protected $fillable = [
        'no_resi', # varchar
        'koli', # double
        'ukuran', # varchar
        'harga', # double
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'no_resi', 'no_resi');
    }

}
