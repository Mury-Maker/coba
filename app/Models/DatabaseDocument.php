<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseDocument extends Model
{
    use HasFactory;

    protected $table = 'database_documents';
    protected $fillable = [
        'database_data_id',
        'path',
        'filename',
    ];

    public function databaseData()
    {
        return $this->belongsTo(DatabaseData::class, 'database_data_id', 'id_database');
    }
}
