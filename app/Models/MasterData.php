<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MasterData extends Model
{

    protected $table = 'tbmaster';

    protected $fillable = [
        'name',
        'kategori',
        'ket'
    ];

}
