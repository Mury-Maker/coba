<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection; // Pastikan ini di-import

class NavMenu extends Model
{
    use HasFactory;

    protected $table = 'navmenu';
    protected $primaryKey = 'menu_id';
    public $timestamps = true;

    protected $fillable = [
        'category_id',
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
        return $this->belongsTo(Category::class);
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
                $item = clone $element;
                // Eager load children secara rekursif
                $item->children = self::buildTree($elements, $item->menu_id); // Memuat children di sini

                $branch[] = $item;
            }
        }

        usort($branch, function($a, $b) {
            return $a->menu_order <=> $b->menu_order;
        });

        return $branch;
    }

    /**
     * Memeriksa apakah menu ini adalah turunan dari potensi parent ID.
     * Digunakan untuk mencegah circular dependency.
     * @param int $potentialParentId - ID parent yang akan diperiksa.
     * @return bool
     */
    public function isDescendantOf(int $potentialParentId): bool
    {
        // Jika potentialParentId adalah 0 (Menu Utama), tidak mungkin ada circular dependency
        if ($potentialParentId === 0) {
            return false;
        }

        // Dapatkan semua turunan dari menu yang sedang diedit (this)
        $descendantIds = $this->getDescendantIdsRecursive($this->menu_id);

        // Periksa apakah potentialParentId ada di antara turunan-turunan tersebut
        return in_array($potentialParentId, $descendantIds);
    }

    /**
     * Helper rekursif untuk mendapatkan semua ID turunan dari sebuah menu.
     * @param int $menuId - ID menu induk.
     * @return array - Array berisi ID semua turunan.
     */
    private function getDescendantIdsRecursive(int $menuId): array
    {
        $descendants = [];
        $children = NavMenu::where('menu_child', $menuId)->pluck('menu_id')->toArray();

        foreach ($children as $childId) {
            $descendants[] = $childId;
            // Rekursif memanggil untuk anak-anak dari anak
            $descendants = array_merge($descendants, $this->getDescendantIdsRecursive($childId));
        }

        return array_unique($descendants);
    }
}
