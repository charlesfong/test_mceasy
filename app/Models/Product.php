<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = ['id' => 'string','customer_price'=>'int','supplier_price'=>'int','weight'=>'int'];
    protected $keyType = 'string';
    protected $table = 'products';
    protected $fillable = [
        'id','name', 'customer_price', 'supplier_price', 'description', 'brand', 'weight','id_category','id_supplier','status','CREATED_BY',
    ];
    
    public function supplier() {
        return $this->belongsTo('App\supplier','id_supplier');
    }
}
