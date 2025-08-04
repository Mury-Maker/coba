<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatData extends Model
{
    use HasFactory;

    protected $table = 'uat_data';
    protected $primaryKey = 'id_uat';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'use_case_id',
        'nama_proses_usecase',
        'keterangan_uat',
        'status_uat',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class, 'use_case_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(UatImage::class, 'uat_data_id', 'id_uat');
    }

    // TAMBAHAN: Relasi untuk dokumen
    public function documents()
    {
        return $this->hasMany(UatDocument::class, 'uat_data_id', 'id_uat');
    }
}
