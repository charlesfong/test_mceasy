<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer_extra extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'customers_extras';
    protected $fillable = [
        'id_customer','name', 'address', 'phone1','phone2', 'phone3', 'email', 'CREATED_AT', 'UPDATED_AT'
    ];

    public function customer() {
        return $this->belongsTo('App\customer','id_customer');
    }
}
