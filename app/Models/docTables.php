<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocTables extends Model
{
    protected $fillable = ['category_id', 'nama_tabel', 'syntax'];

    public function columns()
    {
        return $this->hasMany(DocColumns::class, 'table_id');
    }

    public function foreignKeysFrom()
    {
        return $this->hasMany(DocRelations::class, 'from_tableid');
    }

    public function category()
    {
        return $this->belongsTo(Navmenu::class, 'category_id', 'id');
    }

    public function relations()
    {
        return $this->hasMany(DocRelations::class, 'from_tableid');
    }
}


