<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportImage extends Model
{
    use HasFactory;

    // Pastikan nama tabel ini benar
    protected $table = 'report_images';
    protected $fillable = [
        'report_data_id',
        'path',
        'filename',
    ];

    public function reportData()
    {
        return $this->belongsTo(ReportData::class, 'report_data_id', 'id_report');
    }
}
