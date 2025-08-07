<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocRelations extends Model
{
    protected $fillable = ['from_tableid', 'from_columnid', 'to_tableid', 'to_columnid'];

    public function fromTable()
    {
        return $this->belongsTo(DocTables::class, 'from_tableid');
    }

    public function toTable()
    {
        return $this->belongsTo(DocTables::class, 'to_tableid');
    }

    public function fromColumn()
    {
        return $this->belongsTo(DocColumns::class, 'from_columnid');
    }

    public function toColumn()
    {
        return $this->belongsTo(DocColumns::class, 'to_columnid');
    }
}


