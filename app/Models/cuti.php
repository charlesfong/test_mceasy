<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cuti extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'cuti';
    public $timestamps = false;
    protected $fillable = [
        'nomor_induk', 'tanggal_cuti', 'lama_cuti','keterangan'
    ];

    public function karyawan()
    {
        return $this->belongsTo(karyawan::class, 'nomor_induk');
    }
}
