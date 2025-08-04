<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class CategoryController extends Controller
{
    // Helper untuk memastikan pengguna adalah admin
    private function ensureAdminAccess()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin Admin.');
        }
    }

    public function getCategoryData(Category $category)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        try {
            $newCategory = null;
            $newCategory = DB::transaction(function () use ($request) {
                $category = Category::create([
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                ]);

                $homeMenu = NavMenu::create([
                    'category_id' => $category->id,
                    'menu_nama' => 'Beranda ' . Str::headline($category->name),
                    'menu_link' => Str::slug('Beranda ' . $category->name),
                    'menu_icon' => 'fa-solid fa-home',
                    'menu_child' => 0,
                    'menu_order' => 0,
                    'menu_status' => 1,
                ]);

                UseCase::create([
                    'menu_id' => $homeMenu->menu_id,
                    'usecase_id' => 'INFO-BERANDA',
                    'nama_proses' => 'Informasi Umum',
                    'deskripsi_aksi' => 'Informasi pengantar untuk kategori ' . Str::headline($category->name) . '.',
                    'aktor' => 'Sistem',
                    'tujuan' => 'Memberikan gambaran umum kategori.',
                    'kondisi_awal' => 'Pengguna mengakses halaman beranda kategori.',
                    'kondisi_akhir' => 'Informasi umum ditampilkan.',
                    'aksi_aktor' => 'Pengguna membaca konten.',
                    'reaksi_sistem' => 'Sistem menyajikan informasi.',
                ]);

                return $category;
            });

            return response()->json([
                'success' => 'Kategori berhasil ditambahkan!',
                'redirect_url' => route('docs', [
                    'category' => $newCategory->slug,
                    'page' => Str::slug('Beranda ' . $newCategory->name)
                ])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Category $category)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $oldCategorySlug = $category->slug;

        try {
            DB::transaction(function () use ($request, $category, $oldCategorySlug) {
                $category->update(['name' => $request->name]);

                $newCategorySlug = $category->slug;
                $newCategoryName = $category->name;

                $homeMenu = NavMenu::where('category_id', $category->id)
                                    ->where('menu_child', 0)
                                    ->where('menu_order', 0)
                                    ->where('menu_status', 1)
                                    ->first();

                if ($homeMenu) {
                    $homeMenu->update([
                        'menu_nama' => 'Beranda ' . Str::headline($newCategoryName),
                        'menu_link' => Str::slug('Beranda ' . $newCategoryName),
                    ]);

                    $infoUseCase = $homeMenu->useCases()->where('usecase_id', 'INFO-BERANDA')->first();
                    if ($infoUseCase) {
                        $infoUseCase->update([
                            'nama_proses' => 'Informasi Umum Kategori ' . Str::headline($newCategoryName),
                            'deskripsi_aksi' => 'Informasi pengantar untuk kategori ' . Str::headline($newCategoryName) . '.',
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => 'Kategori berhasil diperbarui!',
                'new_slug' => $category->slug,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Category $category)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        if ($category->slug === 'epesantren') {
            return response()->json(['message' => 'Kategori "ePesantren" tidak dapat dihapus.'], 403);
        }

        try {
            DB::transaction(function () use ($category) {
                $category->delete();
            });

            $defaultCategorySlug = 'epesantren';
            $redirectUrl = route('docs.index');

            $firstMenuInDefaultCategory = NavMenu::whereHas('category', function($query) use ($defaultCategorySlug) {
                                            $query->where('slug', $defaultCategorySlug);
                                        })
                                        ->orderBy('menu_order')
                                        ->orderBy('menu_id')
                                        ->first();

            if ($firstMenuInDefaultCategory) {
                $redirectUrl = route('docs', [
                    'category' => $defaultCategorySlug,
                    'page' => Str::slug($firstMenuInDefaultCategory->menu_nama)
                ]);
            }

            return response()->json([
                'success' => 'Kategori dan semua kontennya berhasil dihapus!',
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus kategori.', 'error' => $e->getMessage()], 500);
        }
    }
}
