<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pbf extends Model
{
    protected $table = 'tbpbf';

    protected $fillable = [
        'kode_pbf',
        'nama_pbf',
        'alamat',
        'lat',
        'lng',
        'image_path',
    ];
}
