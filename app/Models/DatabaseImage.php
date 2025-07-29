<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseImage extends Model
{
    use HasFactory;

    protected $table = 'database_images'; // Pastikan nama tabel sesuai migrasi
    protected $fillable = [
        'database_data_id',
        'path',
        'filename',
    ];

    // Relasi: DatabaseImage ini dimiliki oleh satu DatabaseData
    public function databaseData()
    {
        return $this->belongsTo(DatabaseData::class, 'database_data_id', 'id_database');
    }
}
