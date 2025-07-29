<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class NavMenu extends Model
{
    use HasFactory;

    protected $table = 'navmenu';
    protected $primaryKey = 'menu_id'; // Kunci utama kustom
    public $timestamps = true; // Migrasi kita punya timestamps sekarang

    protected $fillable = [
        'category_id', // Ubah dari 'category' string
        'menu_nama',
        'menu_link',
        'menu_icon',
        'menu_child',
        'menu_order',
        'menu_status',
    ];

    // Relasi: NavMenu ini dimiliki oleh satu Category
    public function category()
    {
        return $this->belongsTo(Category::class); // Default FK adalah category_id
    }

    // Relasi: Satu NavMenu memiliki banyak UseCase
    public function useCases()
    {
        return $this->hasMany(UseCase::class, 'menu_id', 'menu_id')->orderBy('nama_proses');
    }

    // Relasi rekursif: NavMenu ini memiliki parent
    public function parent()
    {
        return $this->belongsTo(NavMenu::class, 'menu_child', 'menu_id');
    }

    // Relasi rekursif: NavMenu ini memiliki children (sub-menu)
    public function children()
    {
        return $this->hasMany(NavMenu::class, 'menu_child', 'menu_id')->orderBy('menu_order');
    }

    // Mutator: Otomatis buat slug untuk menu_link dari menu_nama
    public function setMenuNamaAttribute($value)
    {
        $this->attributes['menu_nama'] = $value;
        $this->attributes['menu_link'] = Str::slug($value);
    }

    /**
     * Membangun menu hierarkis dari koleksi.
     * Digunakan di Controller untuk menyiapkan data sidebar.
     */
    public static function buildTree(Collection $elements, $parentId = 0): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->menu_child == $parentId) {
                $item = clone $element; // Kloning objek untuk manipulasi aman

                // Relasi 'children' tidak perlu diset di sini lagi jika sudah ada di model,
                // tapi jika ingin memuat eager loading secara manual:
                // $item->children = self::buildTree($elements, $item->menu_id);

                $branch[] = $item;
            }
        }

        usort($branch, function($a, $b) {
            return $a->menu_order <=> $b->menu_order; // Urutkan berdasarkan menu_order
        });

        return $branch;
    }

    /**
     * Memeriksa apakah menu ini adalah turunan dari potensi parent ID.
     */
    public function isDescendantOf($potentialParentId): bool
    {
        $current = $this;
        while ($current->menu_child !== 0 && $current->menu_child !== null) {
            if ($current->menu_child == $potentialParentId) {
                return true;
            }
            $current = NavMenu::find($current->menu_child); // Temukan instance parent
            if (!$current) break;
        }
        return false;
    }
}
