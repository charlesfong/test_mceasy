<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class supplier extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'suppliers';
    protected $fillable = [
        'name', 'address', 'phone1','phone2', 'phone3', 'email', 'UPDATED_AT'
    ];

    public function products()
    {
        return $this->hasMany('App\product','id','id_supplier');
    }
}
