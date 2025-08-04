<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseData extends Model
{
    use HasFactory;

    protected $table = 'database_data';
    protected $primaryKey = 'id_database';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'use_case_id',
        'keterangan',
        'relasi',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }

    public function images()
    {
        return $this->hasMany(DatabaseImage::class, 'database_data_id', 'id_database');
    }

    // TAMBAHAN: Relasi untuk dokumen
    public function documents()
    {
        return $this->hasMany(DatabaseDocument::class, 'database_data_id', 'id_database');
    }
}
