<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class info extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'info';
    protected $fillable = [
        'country', 'email', 'address', 'phone1','phone2', 'phone3', 'whatsapp', 'instagram_link', 'bank',
        'bank_account_name', 'bank_account_no', 'created_at', 'CREATED_BY', 'updated_at', 'UPDATED_BY'
    ];
}
