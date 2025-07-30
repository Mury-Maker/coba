<?php
// app/Http/Controllers/DocumentationController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\ReportData;
use App\Models\DatabaseData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DocumentationController extends Controller
{
    private function prepareCommonViewData($currentCategorySlug, $currentPageSlug, $selectedNavItem = null)
    {
        if (!Auth::check()) {
            abort(403, 'Akses Ditolak: Anda harus login.');
        }

        $currentCategory = Category::where('slug', $currentCategorySlug)->firstOrFail();
        $allMenus = NavMenu::where('category_id', $currentCategory->id)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);
        $allCategories = Category::all()->pluck('slug', 'name')->toArray();

        if (!$selectedNavItem && $currentPageSlug) {
            $selectedNavItem = $allMenus->first(function ($menu) use ($currentPageSlug) {
                return Str::slug($menu->menu_nama) === $currentPageSlug;
            });
        }

        return [
            'title'             => ($selectedNavItem ? Str::headline($selectedNavItem->menu_nama) : Str::headline($currentCategory->name)) . ' - Dokumentasi',
            'navigation'        => $navigation,
            'currentCategory'   => $currentCategory->slug,
            'currentPage'       => $currentPageSlug,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem ? $selectedNavItem->menu_id : null,
            'categories'        => $allCategories,
            'userRole'          => Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest',
            'editorMode'        => (Auth::check() && (Auth::user()->role ?? '') === 'admin')
        ];
    }

    public function index(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $defaultCategorySlug = 'epesantren';
        $defaultCategory = Category::where('slug', $defaultCategorySlug)->first();

        if (!$defaultCategory || !$defaultCategory->navMenus()->exists()) {
             $viewData = $this->prepareCommonViewData($defaultCategorySlug, 'homepage');
             $viewData['fallbackMessage'] = "<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>";
             $viewData['contentView'] = 'documentation.homepage'; // Tambahkan ini
             return view('documentation.index', $viewData);
        }

        $firstContentMenu = $defaultCategory->navMenus()
                                ->where('menu_status', 1)
                                ->orderBy('menu_order', 'asc')
                                ->first();

        if ($firstContentMenu) {
            return redirect()->route('docs', [
                'category' => $defaultCategorySlug,
                'page' => Str::slug($firstContentMenu->menu_nama),
            ]);
        } else {
            $firstAnyMenu = $defaultCategory->navMenus()
                                ->orderBy('menu_order', 'asc')
                                ->first();

            if ($firstAnyMenu) {
                return redirect()->route('docs', [
                    'category' => $defaultCategorySlug,
                    'page' => Str::slug($firstAnyMenu->menu_nama),
                ]);
            }
        }
        $viewData = $this->prepareCommonViewData($defaultCategorySlug, 'homepage');
        $viewData['contentView'] = 'documentation.homepage'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }

    public function show($categorySlug, $pageSlug = null): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) {
            return redirect()->route('docs.index');
        }

        $allMenusInCategory = NavMenu::where('category_id', $currentCategory->id)->orderBy('menu_order')->get();
        $selectedNavItem = $allMenusInCategory->first(function ($menu) use ($pageSlug) {
            return Str::slug($menu->menu_nama) === $pageSlug;
        });

        if (!$selectedNavItem) {
            $firstMenuInCategory = $allMenusInCategory->first();
            if ($firstMenuInCategory) {
                return redirect()->route('docs', [
                    'category' => $categorySlug,
                    'page' => Str::slug($firstMenuInCategory->menu_nama),
                ]);
            } else {
                $viewData = $this->prepareCommonViewData($categorySlug, 'homepage');
                $viewData['fallbackMessage'] = "<h3>Selamat Datang!</h3><p>Tidak ada menu yang ditemukan dalam kategori ini. Anda dapat menambahkan menu baru melalui panel admin.</p>";
                $viewData['contentView'] = 'documentation.homepage'; // Tambahkan ini
                return view('documentation.index', $viewData);
            }
        }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);

        if ($selectedNavItem->menu_status == 1) {
            $viewData['useCases'] = UseCase::where('menu_id', $selectedNavItem->menu_id)->orderBy('id', 'desc')->get();
            $viewData['contentView'] = 'documentation.use_case_list'; // Tambahkan ini
            return view('documentation.index', $viewData);
        } else {
            $viewData['contentView'] = 'documentation.homepage'; // Tambahkan ini
            return view('documentation.index', $viewData);
        }
    }

    public function showUseCaseDetail($categorySlug, $pageSlug, $useCaseSlug): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentCategory = Category::where('slug', $categorySlug)->firstOrFail();
        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)
                                ->where('menu_status', 1)
                                ->where(function($query) use ($pageSlug) {
                                    $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]);
                                })
                                ->firstOrFail();

        $singleUseCase = UseCase::with(['uatData.images', 'reportData', 'databaseData.images'])
                                ->where('menu_id', $selectedNavItem->menu_id)
                                ->where(function($query) use ($useCaseSlug) {
                                    $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                        ->orWhere('usecase_id', $useCaseSlug);
                                })
                                ->first();

        if (!$singleUseCase) {
            return redirect()->route('docs', [
                'category' => $categorySlug,
                'page' => Str::slug($selectedNavItem->menu_nama)
            ]);
        }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['singleUseCase'] = $singleUseCase;
        $viewData['contentTypes'] = ['UAT', 'Report', 'Database'];
        $viewData['contentView'] = 'documentation.use_case_detail'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }

    public function showUatDetailPage($categorySlug, $pageSlug, $useCaseSlug, $uatId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }
        $currentCategory = Category::where('slug', $categorySlug)->firstOrFail();
        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->firstOrFail();
        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->firstOrFail();
        $uatData = UatData::with('images')->where('use_case_id', $parentUseCase->id)->where('id_uat', $uatId)->firstOrFail();
        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['uatData'] = $uatData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.uat_entry_detail'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }

    public function showReportDetailPage($categorySlug, $pageSlug, $useCaseSlug, $reportId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }
        $currentCategory = Category::where('slug', $categorySlug)->firstOrFail();
        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->firstOrFail();
        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->firstOrFail();
        $reportData = ReportData::where('use_case_id', $parentUseCase->id)->where('id_report', $reportId)->firstOrFail();
        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['reportData'] = $reportData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.report_entry_detail'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }

    public function showDatabaseDetailPage($categorySlug, $pageSlug, $useCaseSlug, $databaseId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }
        $currentCategory = Category::where('slug', $categorySlug)->firstOrFail();
        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->firstOrFail();
        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->firstOrFail();
        $databaseData = DatabaseData::with('images')->where('use_case_id', $parentUseCase->id)->where('id_database', $databaseId)->firstOrFail();
        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['databaseData'] = $databaseData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.database_entry_detail'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }

    private function renderNoContentFallback($categorySlug): View
    {
        $currentCategory = Category::where('slug', $categorySlug)->first();
        $categoryName = $currentCategory ? Str::headline($currentCategory->name) : 'Dokumentasi';
        $fallbackMessage = "<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>";

        $viewData = $this->prepareCommonViewData($categorySlug, 'homepage');
        $viewData['fallbackMessage'] = $fallbackMessage;
        $viewData['contentView'] = 'documentation.homepage'; // Tambahkan ini
        return view('documentation.index', $viewData);
    }
}
