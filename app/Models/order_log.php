<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_log extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    // protected $casts = ['id' => 'string'];
    public $incrementing = true;
    protected $table = 'orders_log';
    protected $keyType = 'string';
    protected $fillable = [
        'id_order','old_status', 'new_status', 'CREATED_AT','CREATED_BY'
    ];

}
