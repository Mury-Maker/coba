<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatData extends Model
{
    use HasFactory;

    protected $table = 'uat_data';
    protected $primaryKey = 'id_uat'; // Kunci utama kustom
    public $incrementing = true; // Pastikan Laravel tahu ini auto-incrementing
    protected $keyType = 'int'; // Tipe data kunci utama

    protected $fillable = [
        'use_case_id',
        'nama_proses_usecase',
        'keterangan_uat',
        'status_uat',
        // 'gambar_uat', // Pastikan baris ini sudah dihapus
    ];

    // Relasi: UatData ini dimiliki oleh satu UseCase
    public function useCase()
    {
        return $this->belongsTo(UseCase::class, 'use_case_id', 'id');
    }

    // Relasi: Satu UatData memiliki banyak UatImage
    public function images()
    {
        return $this->hasMany(UatImage::class, 'uat_data_id', 'id_uat');
    }
}
