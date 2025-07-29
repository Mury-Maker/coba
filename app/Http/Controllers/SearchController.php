<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized: Anda harus login untuk melakukan pencarian.'], 401);
        }

        $query = $request->input('query');

        if (!$query) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $searchTerm = '%' . strtolower($query) . '%';

        // Cari di NavMenu (judul menu)
        $menuMatches = NavMenu::whereRaw('LOWER(menu_nama) LIKE ?', [$searchTerm])
            ->with('category')
            ->get();

        foreach ($menuMatches as $menu) {
            $categorySlug = $menu->category ? $menu->category->slug : 'unknown-category';
            $categoryName = $menu->category ? Str::headline($menu->category->name) : 'Unknown Category';

            $results[] = [
                'id' => $menu->menu_id,
                'name' => $menu->menu_nama,
                'category_name' => $categoryName,
                'url' => route('docs', ['category' => $categorySlug, 'page' => Str::slug($menu->menu_nama)]),
                'context' => 'Judul Menu',
            ];
        }

        // Cari di UseCase (nama_proses, deskripsi_aksi, dll.)
        $useCaseMatches = UseCase::whereRaw('LOWER(nama_proses) LIKE ?', [$searchTerm])
                                ->orWhereRaw('LOWER(deskripsi_aksi) LIKE ?', [$searchTerm])
                                ->orWhereRaw('LOWER(aktor) LIKE ?', [$searchTerm])
                                ->orWhereRaw('LOWER(tujuan) LIKE ?', [$searchTerm])
                                ->with('menu.category')
                                ->get();

        foreach ($useCaseMatches as $useCase) {
            if ($useCase->menu && $useCase->menu->category) {
                $categorySlug = $useCase->menu->category->slug;
                $categoryName = Str::headline($useCase->menu->category->name);
                $pageSlug = Str::slug($useCase->menu->menu_nama);

                $results[] = [
                    'id' => $useCase->id,
                    'name' => $useCase->nama_proses,
                    'category_name' => $categoryName . ' > ' . $useCase->menu->menu_nama,
                    'url' => route('docs.use_case_detail', [
                        'category' => $categorySlug,
                        'page' => $pageSlug,
                        'useCaseSlug' => Str::slug($useCase->nama_proses)
                    ]),
                    'context' => 'Detail Aksi',
                ];
            }
        }

        return response()->json(['results' => array_values($results)]);
    }
}
