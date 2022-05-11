<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class po_order_detail extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'po_orders_details';
    protected $fillable = [
        'id_order', 'id_invoices_details', 'id_product','qty', 'price', 'd_price', 'profit','CREATED_AT', 'UPDATED_AT'
    ];

    public function orders_details()
    {
        return $this->belongsTo('App\order','id_order');
    }
}
