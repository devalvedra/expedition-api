<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    protected $table = 'tbpickup';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nojual',
        'sales_id',
        'kategori',
        'lantai',
        'list_barang',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'list_barang' => 'array',
    ];

    /**
     * Relationship with Sales model
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'sales_id');
    }
}
