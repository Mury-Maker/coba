<?php

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
    /**
     * Mempersiapkan data umum yang diperlukan oleh semua tampilan dokumentasi.
     *
     * @param string $currentCategorySlug
     * @param string $currentPageSlug
     * @param NavMenu|null $selectedNavItem
     * @return array
     */
    private function prepareCommonViewData($currentCategorySlug, $currentPageSlug, $selectedNavItem = null)
    {
        if (!Auth::check()) {
            abort(403, 'Akses Ditolak: Anda harus login.');
        }

        $currentCategoryObject = Category::where('slug', $currentCategorySlug)->first();

        if (!$currentCategoryObject) {
            $defaultCategorySlug = 'epesantren';
            $currentCategoryObject = Category::where('slug', $defaultCategorySlug)->firstOrFail();
        }

        $allMenus = NavMenu::where('category_id', $currentCategoryObject->id)
                            ->orderBy('menu_order')
                            ->get();

        $navigation = NavMenu::buildTree($allMenus);
        $allCategories = Category::all()->pluck('slug', 'name')->toArray();

        if (!$selectedNavItem && $currentPageSlug) {
            $selectedNavItem = $allMenus->first(function ($menu) use ($currentPageSlug) {
                return Str::slug($menu->menu_nama) === $currentPageSlug;
            });
        }

        return [
            'title'             => ($selectedNavItem ? Str::headline($selectedNavItem->menu_nama) : Str::headline($currentCategoryObject->name)) . ' - Dokumentasi',
            'navigation'        => $navigation,
            'currentCategory'   => $currentCategoryObject->slug,
            'currentCategoryId' => $currentCategoryObject->id,
            'currentPage'       => $currentPageSlug,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem ? $selectedNavItem->menu_id : null,
            'categories'        => $allCategories,
            'userRole'          => Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest',
            'editorMode'        => (Auth::check() && (Auth::user()->role ?? '') === 'admin'),
            'currentCategoryObject' => $currentCategoryObject,
        ];
    }

    /**
     * Menampilkan halaman indeks dokumentasi. Mengarahkan ke kategori/menu default jika belum ada.
     *
     * @return View|RedirectResponse
     */
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
            $viewData['contentView'] = 'documentation.homepage';
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
        $viewData['contentView'] = 'documentation.homepage';
        return view('documentation.index', $viewData);
    }

    /**
     * Menampilkan halaman dokumentasi untuk kategori dan halaman tertentu.
     *
     * @param string $categorySlug
     * @param string|null $pageSlug
     * @return View|RedirectResponse
     */
    public function show($categorySlug, $pageSlug = null): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $allMenusInCategory = NavMenu::where('category_id', $currentCategory->id)
                                        ->orderBy('menu_order')
                                        ->get();

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
                $viewData['contentView'] = 'documentation.homepage';
                return view('documentation.index', $viewData);
            }
        }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);

        if ($selectedNavItem->menu_status == 1) {
            $viewData['useCases'] = UseCase::where('menu_id', $selectedNavItem->menu_id)->orderBy('id', 'desc')->get();
            $viewData['contentView'] = 'documentation.use_case_list';
            return view('documentation.index', $viewData);
        } else {
            $viewData['contentView'] = 'documentation.homepage';
            return view('documentation.index', $viewData);
        }
    }

    /**
     * Menampilkan detail Use Case (Aksi).
     *
     * @param string $categorySlug
     * @param string $pageSlug
     * @param string $useCaseSlug
     * @return View|RedirectResponse
     */
    public function showUseCaseDetail($categorySlug, $pageSlug, $useCaseSlug): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)
                                 ->where('menu_status', 1)
                                 ->where(function($query) use ($pageSlug) {
                                     $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]);
                                 })
                                 ->first();
        if (!$selectedNavItem) {
            return redirect()->route('docs', ['category' => $categorySlug]);
        }

        // PERBAIKAN PENTING: Muat relasi reportData dan databaseData beserta file-nya
        $singleUseCase = UseCase::with([
                                    'uatData.images',
                                    'uatData.documents', // TAMBAH: Muat relasi dokumen UAT
                                    'reportData.images',
                                    'reportData.documents',
                                    'databaseData.images',
                                    'databaseData.documents' // TAMBAH: Muat relasi dokumen Database
                                ])
                                ->where('menu_id', $selectedNavItem->menu_id)
                                ->where(function($query) use ($useCaseSlug) {
                                    $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                        ->orWhere('id', $useCaseSlug);
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
        $viewData['contentView'] = 'documentation.use_case_detail';

        return view('documentation.index', $viewData);
    }

    /**
     * Menampilkan detail entri UAT.
     *
     * @param string $categorySlug
     * @param string $pageSlug
     * @param string $useCaseSlug
     * @param int $uatId
     * @return View|RedirectResponse
     */
    public function showUatDetailPage($categorySlug, $pageSlug, $useCaseSlug, $uatId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        // PERBAIKAN PENTING: Muat relasi images dan documents untuk uatData
        $uatData = UatData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_uat', $uatId)->first();
        if (!$uatData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['uatData'] = $uatData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.uat_entry_detail';
        return view('documentation.index', $viewData);
    }

    /**
     * Menampilkan detail entri Report.
     *
     * @param string $categorySlug
     * @param string $pageSlug
     * @param string $useCaseSlug
     * @param int $reportId
     * @return View|RedirectResponse
     */
    public function showReportDetailPage($categorySlug, $pageSlug, $useCaseSlug, $reportId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $reportData = ReportData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_report', $reportId)->first();
        if (!$reportData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['reportData'] = $reportData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.report_entry_detail';
        return view('documentation.index', $viewData);
    }

    /**
     * Menampilkan detail entri Database.
     *
     * @param string $categorySlug
     * @param string $pageSlug
     * @param string $useCaseSlug
     * @param int $databaseId
     * @return View|RedirectResponse
     */
    public function showDatabaseDetailPage($categorySlug, $pageSlug, $useCaseSlug, $databaseId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        // PERBAIKAN PENTING: Muat relasi images dan documents untuk databaseData
        $databaseData = DatabaseData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_database', $databaseId)->first();
        if (!$databaseData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['databaseData'] = $databaseData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.database_entry_detail';
        return view('documentation.index', $viewData);
    }

    /**
     * Menampilkan fallback konten ketika tidak ada menu/konten yang ditemukan.
     * Metode ini sepertinya tidak lagi digunakan secara langsung karena logikanya
     * sudah terintegrasi ke dalam metode show() dan index().
     *
     * @param string $categorySlug
     * @return View
     */
    private function renderNoContentFallback($categorySlug): View
    {
        $currentCategory = Category::where('slug', $categorySlug)->first();
        $categoryName = $currentCategory ? Str::headline($currentCategory->name) : 'Dokumentasi';
        $fallbackMessage = "<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>";

        $viewData = $this->prepareCommonViewData($categorySlug, 'homepage');
        $viewData['fallbackMessage'] = $fallbackMessage;
        $viewData['contentView'] = 'documentation.homepage';
        return view('documentation.index', $viewData);
    }
}
