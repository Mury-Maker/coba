<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatDocument extends Model
{
    use HasFactory;

    protected $table = 'uat_documents';
    protected $fillable = [
        'uat_data_id',
        'path',
        'filename',
    ];

    public function uatData()
    {
        return $this->belongsTo(UatData::class, 'uat_data_id', 'id_uat');
    }
}
