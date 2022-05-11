<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class po_orders extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'po_orders';
    protected $fillable = [
        'id_customer', 'shipping_address', 'total_pay','status','CREATED_BY','CREATED_AT', 'UPDATED_AT'
    ];

    public function po_orders_details()
    {
        return $this->hasMany('App\po_order_detail','id','id_order');
    }
}
