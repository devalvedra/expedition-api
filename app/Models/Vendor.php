<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    protected $table = 'tbvendor';
    
    protected $primaryKey = 'kodevendor';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'last_updated';
    const DELETED_AT = 'deleted_at';

    protected $guarded = [
        
    ];
}
