<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shipment extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'shipments';
    protected $fillable = [
        'id_customer', 'id_courier', 'tracking_no','shipping_cost', 'status','remark','CREATED_BY','CREATED_AT', 'UPDATED_AT', 'UPDATED_BY'
    ];

}
