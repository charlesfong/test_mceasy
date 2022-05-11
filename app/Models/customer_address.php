<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer_address extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'customers_address';
    protected $fillable = [
        'id_customer','name', 'address', 'cp_name1', 'cp_name2', 'cp_name3','phone1','phone2', 'phone3', 'email', 'CREATED_AT'
    ];

    public function customer_extra()
    {
        return $this->hasMany('App\customers_extras','id','id_customer');
    }

    // public function customer() {
    //     return $this->belongsTo('App\customer','id_customer');
    // }
}
