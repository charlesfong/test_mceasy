<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_detail extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = ['id' => 'string'];
    protected $table = 'orders_details';
    protected $fillable = [
        'id_order', 'name_product', 'id_invoices_details', 'id_product','qty', 'price', 'd_price', 'profit','CREATED_AT', 'UPDATED_AT'
    ];

    public function orders_details()
    {
        return $this->belongsTo('App\order','id_order');
    }
}
