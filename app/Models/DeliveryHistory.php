<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryHistory extends Model
{
    protected $table = 'tbhistorypengiriman';

    protected $fillable = [
        'no_invoice',
        'kode_pbf',
        'status',
        'username'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'no_invoice', 'no_invoice');
    }
}
