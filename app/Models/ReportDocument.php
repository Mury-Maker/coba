<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDocument extends Model
{
    use HasFactory;

    protected $table = 'report_documents';
    protected $fillable = [
        'report_data_id',
        'path',
        'filename',
    ];

    // Relasi: ReportDocument ini dimiliki oleh satu ReportData
    public function reportData()
    {
        return $this->belongsTo(ReportData::class, 'report_data_id', 'id_report');
    }
}
