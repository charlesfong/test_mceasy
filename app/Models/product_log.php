<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_log extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    // protected $casts = ['id' => 'string'];
    public $incrementing = true;
    protected $table = 'products_log';
    protected $fillable = [
        'id_product','remark', 'CREATED_AT','CREATED_BY'
    ];
}
