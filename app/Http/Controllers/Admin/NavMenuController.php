<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavMenu;
use App\Models\Category;
use App\Models\UseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NavMenuController extends Controller
{
    // Helper untuk memastikan pengguna adalah admin
    private function ensureAdminAccess()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin Admin.');
        }
    }

    public function getMenuData(NavMenu $navMenu)
    {
        $this->ensureAdminAccess();
        return response()->json($navMenu);
    }

    public function getParentMenus(Request $request, $categorySlug)
    {
        $this->ensureAdminAccess();

        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $query = NavMenu::where('category_id', $category->id)
            ->where('menu_status', 0) // Hanya folder yang bisa jadi parent
            ->orderBy('menu_nama');

        if ($request->has('editing_menu_id')) {
            $editingMenuId = $request->input('editing_menu_id');
            $query->where('menu_id', '!=', $editingMenuId); // Menu tidak bisa jadi parent dirinya sendiri

            // Dapatkan semua ID turunan dari menu yang sedang diedit
            $descendantIds = $this->getDescendantIds($editingMenuId);
            if (!empty($descendantIds)) {
                $query->whereNotIn('menu_id', $descendantIds); // Kecualikan turunan dari daftar parent
            }
        }

        $parents = $query->get(['menu_id', 'menu_nama']);
        return response()->json($parents);
    }

    // Metode ini harus ada dan benar di NavMenuController
    // Ini adalah metode rekursif untuk mendapatkan semua ID turunan
    private function getDescendantIds($parentId): array
    {
        $descendantIds = [];
        $queue = collect([$parentId]); // Mulai dengan parent itu sendiri

        while (!$queue->isEmpty()) {
            $currentParentId = $queue->shift();

            // Dapatkan anak-anak langsung dari currentParentId
            $children = NavMenu::where('menu_child', $currentParentId)->pluck('menu_id')->toArray();

            foreach ($children as $childId) {
                if (!in_array($childId, $descendantIds)) { // Hindari duplikasi
                    $descendantIds[] = $childId;
                    $queue->push($childId); // Tambahkan anak ke antrian untuk diperiksa lebih lanjut
                }
            }
        }

        return array_unique($descendantIds);
    }


    public function getAllMenusForSidebar($categorySlug)
    {
        $this->ensureAdminAccess();

        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $allMenus = NavMenu::where('category_id', $category->id)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);

        $html = View::make('partials._menu_item', [
            'items' => $navigation,
            'editorMode' => true,
            'selectedNavItemId' => null,
            'currentCategorySlug' => $categorySlug,
            'level' => 0
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function store(Request $request)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'menu_nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('navmenu', 'menu_nama')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id)
                                 ->where('menu_child', $request->menu_child);
                }),
            ],
            'menu_child' => 'required|integer',
            'menu_order' => 'nullable|integer',
            'menu_icon' => 'nullable|string|max:250',
            'menu_status' => 'boolean',
        ]);

        try {
            $newlyCreatedMenu = null;
            $newlyCreatedMenu = DB::transaction(function () use ($request) {
                $menu = NavMenu::create([
                    'category_id' => $request->category_id,
                    'menu_nama' => $request->menu_nama,
                    'menu_icon' => $request->menu_icon,
                    'menu_child' => $request->menu_child,
                    'menu_order' => $request->menu_order ?? 0,
                    'menu_status' => $request->boolean('menu_status'),
                ]);
                return $menu;
            });

            return response()->json([
                'success' => 'Menu berhasil ditambahkan!',
                'menu_id' => $newlyCreatedMenu->menu_id,
                'new_menu_nama' => $newlyCreatedMenu->menu_nama,
                'new_menu_link' => $newlyCreatedMenu->menu_link,
                'current_category_slug' => $newlyCreatedMenu->category->slug,
                'menu_status' => $newlyCreatedMenu->menu_status,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan menu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan menu.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, NavMenu $navMenu)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'menu_nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('navmenu', 'menu_nama')->where(function ($query) use ($request, $navMenu) {
                    return $query->where('category_id', $request->category_id)
                                 ->where('menu_child', $request->menu_child)
                                 ->where('menu_id', '!=', $navMenu->menu_id);
                }),
            ],
            'menu_child' => 'required|integer',
            'menu_order' => 'required|integer',
            'menu_icon' => 'nullable|string|max:250',
            'menu_status' => 'boolean',
        ]);

        // Validasi circular dependency di sini
        if ($request->menu_child == $navMenu->menu_id) {
            return response()->json(['message' => 'Menu tidak bisa menjadi parent-nya sendiri.'], 422);
        }
        $navMenu->load('children');
        if ($request->menu_child != 0 && $navMenu->isDescendantOf($request->menu_child)) {
            return response()->json(['message' => 'Tidak bisa mengatur sub-menu sebagai parent dari menu induknya.'], 422);
        }

        $oldMenuLink = $navMenu->menu_link;

        try {
            DB::transaction(function () use ($request, $navMenu, $oldMenuLink) {
                $oldMenuStatus = $navMenu->menu_status;
                $newMenuStatus = $request->boolean('menu_status');

                $navMenu->update([
                    'category_id' => $request->category_id,
                    'menu_nama' => $request->menu_nama,
                    'menu_icon' => $request->menu_icon,
                    'menu_child' => $request->menu_child,
                    'menu_order' => $request->menu_order ?? 0,
                    'menu_status' => $newMenuStatus,
                ]);

                if ($oldMenuStatus == 1 && $newMenuStatus == 0) {
                    $navMenu->useCases()->delete();
                }
            });

            return response()->json([
                'success' => 'Menu berhasil diperbarui!',
                'menu_id' => $navMenu->menu_id,
                'new_menu_nama' => $navMenu->menu_nama,
                'new_menu_link' => $navMenu->menu_link,
                'current_category_slug' => $navMenu->category->slug,
                'menu_status' => $navMenu->menu_status,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui menu: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui menu.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus menu dan semua sub-menu terkait secara rekursif.
     * Menerima $menuId (integer) karena Route Model Binding dinonaktifkan untuk rute delete ini.
     *
     * @param int $menuId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($menuId)
    {
        $this->ensureAdminAccess();

        // Cari objek NavMenu secara manual.
        $navMenu = NavMenu::find($menuId);
        if (!$navMenu) {
            Log::warning('NavMenuController@destroy: Menu dengan ID ' . $menuId . ' tidak ditemukan untuk dihapus (mungkin sudah di-cascade delete dari tempat lain).');
            return response()->json(['success' => 'Menu sudah dihapus.'], 200); // Anggap sukses jika tidak ditemukan
        }

        // Muat relasi kategori sebelum menu dihapus (karena setelah delete, relasi mungkin hilang)
        $navMenu->load('category');
        $currentCategorySlug = $navMenu->category ? $navMenu->category->slug : null;
        $deletedCategoryName = $navMenu->category ? $navMenu->category->name : 'Unknown Category';

        try {
            DB::transaction(function () use ($navMenu, $menuId, $currentCategorySlug) {
                // --- PERUBAHAN KRITIS: Kumpulkan semua ID dan hapus secara massal ---
                $allMenuIdsToDelete = $this->collectAllDescendantAndSelfIds($menuId);

                // Hapus UseCase yang terkait dengan semua menu ini
                // Asumsi: use_cases.menu_id memiliki foreign key ke navmenu.menu_id dengan ON DELETE CASCADE
                // Jika TIDAK, maka Anda perlu menghapus UseCase secara eksplisit:
                UseCase::whereIn('menu_id', $allMenuIdsToDelete)->delete();

                // Hapus entri NavMenu secara massal
                NavMenu::whereIn('menu_id', $allMenuIdsToDelete)->delete();
                // --- AKHIR PERUBAHAN KRITIS ---

                Log::info('NavMenuController@destroy: Menu ID ' . $menuId . ' (' . $navMenu->menu_nama . ') dan semua turunannya berhasil dihapus dari DB.');
            });

            // --- Logika redirect tetap sama seperti sebelumnya (sudah diperbaiki) ---
            $redirectUrl = route('docs.index');

            if ($currentCategorySlug) {
                $firstAvailableMenu = NavMenu::whereHas('category', function($query) use ($currentCategorySlug) {
                                            $query->where('slug', $currentCategorySlug);
                                        })
                                        ->orderBy('menu_order')
                                        ->orderBy('menu_id')
                                        ->first();

                if ($firstAvailableMenu) {
                    $redirectUrl = route('docs', [
                        'category' => $currentCategorySlug,
                        'page' => Str::slug($firstAvailableMenu->menu_nama)
                    ]);
                    Log::info('NavMenuController@destroy: Redirect ke menu tersedia: ' . $redirectUrl);
                } else {
                    $categoryExists = Category::where('slug', $currentCategorySlug)->exists();
                    if ($categoryExists) {
                        $berandaSlug = Str::slug('Dashboard ' . $deletedCategoryName);
                        $berandaMenu = NavMenu::where('category_id', $navMenu->category->id)
                                                ->where('menu_link', $berandaSlug)
                                                ->first();

                        if ($berandaMenu) {
                             $redirectUrl = route('docs', ['category' => $currentCategorySlug, 'page' => $berandaSlug]);
                             Log::info('NavMenuController@destroy: Redirect ke dashboard kategori: ' . $redirectUrl);
                        } else {
                             $redirectUrl = route('docs', ['category' => $currentCategorySlug]);
                             Log::info('NavMenuController@destroy: Redirect ke kategori tanpa menu spesifik: ' . $redirectUrl);
                        }

                    } else {
                        $redirectUrl = route('docs.index');
                        Log::info('NavMenuController@destroy: Kategori induk tidak ada, redirect ke docs.index');
                    }
                }
            } else {
                $redirectUrl = route('docs.index');
                Log::info('NavMenuController@destroy: Category Slug NULL, redirect ke docs.index');
            }

            return response()->json([
                'success' => 'Menu dan semua sub-menu berhasil dihapus!',
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus menu: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan tidak terduga saat menghapus menu.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper untuk mengumpulkan semua ID menu, termasuk dirinya sendiri dan semua turunannya.
     * Digunakan untuk penghapusan massal.
     * @param int $menuId
     * @return array
     */
    private function collectAllDescendantAndSelfIds(int $menuId): array
    {
        $ids = [$menuId]; // Mulai dengan ID menu saat ini

        // Ambil semua anak langsung dari menu ini
        $children = NavMenu::where('menu_child', $menuId)->pluck('menu_id')->toArray();

        foreach ($children as $childId) {
            // Rekursif panggil untuk setiap anak
            $ids = array_merge($ids, $this->collectAllDescendantAndSelfIds($childId));
        }
        return array_unique($ids); // Pastikan ID unik
    }
}
