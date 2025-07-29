<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatImage extends Model
{
    use HasFactory;

    protected $table = 'uat_images'; // Pastikan nama tabel sesuai migrasi
    protected $fillable = [
        'uat_data_id',
        'path',
        'filename',
    ];

    // Relasi: UatImage ini dimiliki oleh satu UatData
    public function uatData()
    {
        return $this->belongsTo(UatData::class, 'uat_data_id', 'id_uat');
    }
}
