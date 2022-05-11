<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'customers';
    protected $fillable = [
        'name', 'address', 'phone1','phone2', 'phone3', 'email', 'UPDATED_AT','CREATED_BY','UPDATED_BY'
    ];

    public function customer_extra()
    {
        return $this->hasMany('App\customers_extras','id','id_customer');
    }
}
