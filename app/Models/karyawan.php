<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class karyawan extends Model
{
    use HasFactory;
    protected $primaryKey = 'nomor_induk';
    public $incrementing = false; 
    public $timestamps = false;
    protected $table = 'karyawan';
    protected $fillable = [
        'nomor_induk', 'nama', 'alamat', 'tanggal_lahir','tanggal_bergabung'
    ];

    public function cuti()
    {
        return $this->hasMany(cuti::class);
    }
}
