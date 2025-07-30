<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    // Mutator untuk otomatis membuat slug saat nama diatur
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // --- TAMBAHKAN FUNGSI INI ---
    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    // --- AKHIR TAMBAHAN ---

    // Relasi: Satu Category memiliki banyak NavMenu
    public function navMenus()
    {
        return $this->hasMany(NavMenu::class);
    }
}
