<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = ['id' => 'string'];
    protected $table = 'orders';
    protected $fillable = [
        'id','id_customer','id_address','name_address','name_customer' , 'shipping_address', 'total_pay','status', 'fee_out', 'remark','CREATED_AT', 'UPDATED_AT', 'UPDATED_BY'
    ];

    public function orders_details()
    {
        return $this->hasMany('App\order_detail','id','id_order');
    }
}
