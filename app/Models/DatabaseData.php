<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseData extends Model
{
    use HasFactory;

    protected $table = 'database_data';
    protected $primaryKey = 'id_database'; // Kunci utama kustom
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'use_case_id',
        'keterangan',
        // 'gambar_database', // Pastikan baris ini sudah dihapus
        'relasi',
    ];

    // Relasi: DatabaseData ini dimiliki oleh satu UseCase
    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }

    // Relasi: Satu DatabaseData memiliki banyak DatabaseImage
    public function images()
    {
        return $this->hasMany(DatabaseImage::class, 'database_data_id', 'id_database');
    }
}
