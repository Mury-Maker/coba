<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocSqlFile extends Model
{
    protected $fillable = ['category_id', 'file_name', 'file_path'];

    public function navmenu()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
