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
            // Ini seharusnya tidak terpicu jika ada middleware auth, tapi sebagai fallback.
            abort(403, 'Akses Ditolak: Anda harus login.');
        }

        // Ambil objek kategori berdasarkan slug. Jika tidak ditemukan, fallback ke 'epesantren'.
        $currentCategoryObject = Category::where('slug', $currentCategorySlug)->first();

        // Jika kategori tidak ditemukan, defaultkan ke 'epesantren'
        if (!$currentCategoryObject) {
            $defaultCategorySlug = 'epesantren';
            $currentCategoryObject = Category::where('slug', $defaultCategorySlug)->firstOrFail();
            // firstOrFail() di sini aman karena 'epesantren' diasumsikan selalu ada.
            // Jika 'epesantren' juga tidak ada, itu adalah masalah setup database yang lebih serius.
        }

        // Ambil semua menu untuk kategori yang aktif
        $allMenus = NavMenu::where('category_id', $currentCategoryObject->id)
                            ->orderBy('menu_order')
                            ->get();

        // Bangun struktur pohon navigasi
        $navigation = NavMenu::buildTree($allMenus);

        // Ambil semua kategori untuk dropdown di header/sidebar
        $allCategories = Category::all()->pluck('slug', 'name')->toArray();

        // Temukan item navigasi yang sedang dipilih jika belum disediakan
        if (!$selectedNavItem && $currentPageSlug) {
            $selectedNavItem = $allMenus->first(function ($menu) use ($currentPageSlug) {
                return Str::slug($menu->menu_nama) === $currentPageSlug;
            });
        }

        return [
            'title'             => ($selectedNavItem ? Str::headline($selectedNavItem->menu_nama) : Str::headline($currentCategoryObject->name)) . ' - Dokumentasi',
            'navigation'        => $navigation,
            'currentCategory'   => $currentCategoryObject->slug, // Tetap slug untuk URL
            'currentCategoryId' => $currentCategoryObject->id,
            'currentPage'       => $currentPageSlug,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem ? $selectedNavItem->menu_id : null,
            'categories'        => $allCategories,
            'userRole'          => Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest',
            'editorMode'        => (Auth::check() && (Auth::user()->role ?? '') === 'admin'),
            'currentCategoryObject' => $currentCategoryObject, // Bisa digunakan di view untuk info kategori lengkap
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

        // Jika kategori default tidak ada atau tidak memiliki menu, tampilkan halaman sambutan
        if (!$defaultCategory || !$defaultCategory->navMenus()->exists()) {
            $viewData = $this->prepareCommonViewData($defaultCategorySlug, 'homepage');
            $viewData['fallbackMessage'] = "<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>";
            $viewData['contentView'] = 'documentation.homepage';
            return view('documentation.index', $viewData);
        }

        // Cari menu pertama dengan status 'konten' (1), jika ada
        $firstContentMenu = $defaultCategory->navMenus()
                                            ->where('menu_status', 1)
                                            ->orderBy('menu_order', 'asc')
                                            ->first();

        if ($firstContentMenu) {
            // Redirect ke menu pertama yang memiliki konten
            return redirect()->route('docs', [
                'category' => $defaultCategorySlug,
                'page' => Str::slug($firstContentMenu->menu_nama),
            ]);
        } else {
            // Jika tidak ada menu yang berstatus 'konten', coba redirect ke menu apapun (termasuk folder)
            $firstAnyMenu = $defaultCategory->navMenus()
                                            ->orderBy('menu_order', 'asc')
                                            ->first();

            if ($firstAnyMenu) {
                // Redirect ke menu pertama yang ditemukan
                return redirect()->route('docs', [
                    'category' => $defaultCategorySlug,
                    'page' => Str::slug($firstAnyMenu->menu_nama),
                ]);
            }
        }

        // Fallback terakhir jika tidak ada menu sama sekali di kategori default
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

        // Ambil kategori. Jika tidak ada, redirect ke indeks default.
        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) {
            return redirect()->route('docs.index');
        }

        // Ambil semua menu di kategori ini
        $allMenusInCategory = NavMenu::where('category_id', $currentCategory->id)
                                    ->orderBy('menu_order')
                                    ->get();

        // Coba temukan NavMenu berdasarkan pageSlug
        $selectedNavItem = $allMenusInCategory->first(function ($menu) use ($pageSlug) {
            return Str::slug($menu->menu_nama) === $pageSlug;
        });

        // Jika pageSlug tidak cocok dengan menu manapun
        if (!$selectedNavItem) {
            $firstMenuInCategory = $allMenusInCategory->first();
            if ($firstMenuInCategory) {
                // Redirect ke menu pertama di kategori tersebut
                return redirect()->route('docs', [
                    'category' => $categorySlug,
                    'page' => Str::slug($firstMenuInCategory->menu_nama),
                ]);
            } else {
                // Jika tidak ada menu sama sekali di kategori ini
                $viewData = $this->prepareCommonViewData($categorySlug, 'homepage');
                $viewData['fallbackMessage'] = "<h3>Selamat Datang!</h3><p>Tidak ada menu yang ditemukan dalam kategori ini. Anda dapat menambahkan menu baru melalui panel admin.</p>";
                $viewData['contentView'] = 'documentation.homepage';
                return view('documentation.index', $viewData);
            }
        }

        // Siapkan data umum view
        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);

        // Tampilkan daftar Use Case jika menu berstatus 'konten' (1)
        if ($selectedNavItem->menu_status == 1) {
            $viewData['useCases'] = UseCase::where('menu_id', $selectedNavItem->menu_id)->orderBy('id', 'desc')->get();
            $viewData['contentView'] = 'documentation.use_case_list';
            return view('documentation.index', $viewData);
        } else {
            // Jika menu adalah folder (status 0), tampilkan homepage fallback
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

        // Pastikan kategori ada, jika tidak redirect
        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        // Pastikan NavMenu ada dan berstatus konten, jika tidak redirect
        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)
                                ->where('menu_status', 1)
                                ->where(function($query) use ($pageSlug) {
                                    $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]);
                                })
                                ->first();
        if (!$selectedNavItem) {
            return redirect()->route('docs', ['category' => $categorySlug]);
        }


        // Ambil detail UseCase beserta data terkait (UAT, Report, Database)
        $singleUseCase = UseCase::with(['uatData.images', 'reportData', 'databaseData.images'])
                                ->where('menu_id', $selectedNavItem->menu_id)
                                ->where(function($query) use ($useCaseSlug) {
                                    $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                        ->orWhere('usecase_id', $useCaseSlug); // Memungkinkan pencarian berdasarkan usecase_id juga
                                })
                                ->first();

        // Jika UseCase tidak ditemukan, redirect ke halaman daftar Use Case
        if (!$singleUseCase) {
            return redirect()->route('docs', [
                'category' => $categorySlug,
                'page' => Str::slug($selectedNavItem->menu_nama)
            ]);
        }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['singleUseCase'] = $singleUseCase;
        $viewData['contentTypes'] = ['UAT', 'Report', 'Database']; // Tipe konten yang tersedia
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

        // Validasi dan ambil kategori, menu, dan use case induk
        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        // Ambil data UAT spesifik
        $uatData = UatData::with('images')->where('use_case_id', $parentUseCase->id)->where('id_uat', $uatId)->first();
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

        // Validasi dan ambil kategori, menu, dan use case induk
        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        // Ambil data Report spesifik
        $reportData = ReportData::where('use_case_id', $parentUseCase->id)->where('id_report', $reportId)->first();
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

        // Validasi dan ambil kategori, menu, dan use case induk
        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('usecase_id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        // Ambil data Database spesifik
        $databaseData = DatabaseData::with('images')->where('use_case_id', $parentUseCase->id)->where('id_database', $databaseId)->first();
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
        // Logika di sini sebagian besar sudah dipindahkan ke prepareCommonViewData, index, dan show.
        // Anda mungkin bisa menghapus metode ini jika sudah tidak ada panggilan langsung ke sini.
        $currentCategory = Category::where('slug', $categorySlug)->first();
        $categoryName = $currentCategory ? Str::headline($currentCategory->name) : 'Dokumentasi';
        $fallbackMessage = "<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>";

        $viewData = $this->prepareCommonViewData($categorySlug, 'homepage');
        $viewData['fallbackMessage'] = $fallbackMessage;
        $viewData['contentView'] = 'documentation.homepage';
        return view('documentation.index', $viewData);
    }
}
