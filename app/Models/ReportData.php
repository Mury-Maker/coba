<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportData extends Model
{
    use HasFactory;

    protected $table = 'report_data';
    protected $primaryKey = 'id_report';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'use_case_id',
        'aktor',
        'nama_report',
        'keterangan',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }

    public function images()
    {
        return $this->hasMany(ReportImage::class, 'report_data_id', 'id_report');
    }

    // Tambahkan relasi untuk dokumen
    public function documents()
    {
        return $this->hasMany(ReportDocument::class, 'report_data_id', 'id_report');
    }
}
