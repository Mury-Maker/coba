<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\ReportData;
use App\Models\DatabaseData;
use App\Models\DocTables;
use App\Models\DocColumns;
use App\Models\DocSqlFile;
use App\Models\DocRelations;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            'title'                 => ($selectedNavItem ? Str::headline($selectedNavItem->menu_nama) : Str::headline($currentCategoryObject->name)) . ' - Dokumentasi',
            'navigation'            => $navigation,
            'currentCategory'       => $currentCategoryObject->slug,
            'currentCategoryId'     => $currentCategoryObject->id,
            'currentPage'           => $currentPageSlug,
            'selectedNavItem'       => $selectedNavItem,
            'menu_id'               => $selectedNavItem ? $selectedNavItem->menu_id : null,
            'categories'            => $allCategories,
            'userRole'              => Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest',
            'editorMode'            => (Auth::check() && (Auth::user()->role ?? '') === 'admin'),
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
     * @param Request $request
     * @param string $categorySlug
     * @param string|null $pageSlug
     * @return View|RedirectResponse
     */
    public function show(Request $request, $categorySlug, $pageSlug = null): View|RedirectResponse
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

        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('search', '');

        if ($selectedNavItem->menu_status == 1) {
            $word = "daftar-tabel";
            if (Str::contains($selectedNavItem->menu_link, $word)) {
                $sqlFile = DocSqlFile::where('navmenu_id', $selectedNavItem->menu_id)->first();
                $fullPath = $sqlFile ? Storage::disk('public')->path('sql_files/' . $sqlFile->file_name) : null;
                $viewData['contentView'] = 'documentation.tables_list';
                $viewData['sqlFile'] = $sqlFile;
                $viewData['sqlPath'] = $fullPath ?? 'Tidak ada FileSql';
                return view('documentation.index', $viewData);
            } else {
                $query = UseCase::where('menu_id', $selectedNavItem->menu_id)->orderBy('id', 'desc');

                if ($searchTerm) {
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nama_proses', 'like', "%{$searchTerm}%")
                          ->orWhere('aktor', 'like', "%{$searchTerm}%")
                          ->orWhere('kondisi_awal', 'like', "%{$searchTerm}%")
                          ->orWhere('kondisi_akhir', 'like', "%{$searchTerm}%");
                    });
                }

                $useCases = $query->paginate($perPage)->withQueryString();

                $viewData['useCases'] = $useCases;
                $viewData['contentView'] = 'documentation.use_case_list';
                $viewData['per_page'] = (int) $perPage;
                $viewData['search_term'] = $searchTerm;
                return view('documentation.index', $viewData);
            }
        } else {
            $viewData['contentView'] = 'documentation.homepage';
            return view('documentation.index', $viewData);
        }
    }

    /**
     * Menampilkan detail Use Case (Aksi).
     *
     * @param Request $request
     * @param string $categorySlug
     * @param string $pageSlug
     * @param string $useCaseSlug
     * @return View|RedirectResponse
     */
    public function showUseCaseDetail(Request $request, string $categorySlug, string $pageSlug, string $useCaseSlug): View|RedirectResponse
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

        $singleUseCase = UseCase::with(['uatData.images', 'uatData.documents', 'reportData.images', 'reportData.documents', 'databaseData.images', 'databaseData.documents'])
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

        // Ambil data untuk setiap tabel dengan paginasi dan pencarian
        $perPageReport = $request->input('report_per_page', 5);
        $searchReport = $request->input('report_search');
        $reportDataQuery = ReportData::where('use_case_id', $singleUseCase->id);
        if ($searchReport) {
            $reportDataQuery->where('nama_report', 'like', "%{$searchReport}%")
                            ->orWhere('aktor', 'like', "%{$searchReport}%")
                            ->orWhere('keterangan', 'like', "%{$searchReport}%");
        }
        $reportDataPaginated = $reportDataQuery->paginate($perPageReport, ['*'], 'report_page')->withQueryString();

        $perPageDatabase = $request->input('database_per_page', 5);
        $searchDatabase = $request->input('database_search');
        $databaseDataQuery = DatabaseData::where('use_case_id', $singleUseCase->id);
        if ($searchDatabase) {
            $databaseDataQuery->where('keterangan', 'like', "%{$searchDatabase}%")
                              ->orWhere('relasi', 'like', "%{$searchDatabase}%");
        }
        $databaseDataPaginated = $databaseDataQuery->paginate($perPageDatabase, ['*'], 'database_page')->withQueryString();

        $perPageUat = $request->input('uat_per_page', 5);
        $searchUat = $request->input('uat_search');
        $uatDataQuery = UatData::where('use_case_id', $singleUseCase->id);
        if ($searchUat) {
            $uatDataQuery->where('nama_proses_usecase', 'like', "%{$searchUat}%")
                         ->orWhere('keterangan_uat', 'like', "%{$searchUat}%")
                         ->orWhere('status_uat', 'like', "%{$searchUat}%");
        }
        $uatDataPaginated = $uatDataQuery->paginate($perPageUat, ['*'], 'uat_page')->withQueryString();

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['singleUseCase'] = $singleUseCase;
        $viewData['contentTypes'] = ['UAT', 'Report', 'Database'];
        $viewData['contentView'] = 'documentation.use_case_detail';
        $viewData['reportDataPaginated'] = $reportDataPaginated;
        $viewData['databaseDataPaginated'] = $databaseDataPaginated;
        $viewData['uatDataPaginated'] = $uatDataPaginated;

        return view('documentation.index', $viewData);
    }

    public function showUatDetailPage($categorySlug, $pageSlug, $useCaseSlug, $uatId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        $uatData = UatData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_uat', $uatId)->first();
        if (!$uatData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['uatData'] = $uatData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.uat_entry_detail';
        return view('documentation.index', $viewData);
    }

    public function showReportDetailPage($categorySlug, $pageSlug, $useCaseSlug, $reportId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        $reportData = ReportData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_report', $reportId)->first();
        if (!$reportData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['reportData'] = $reportData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.report_entry_detail';
        return view('documentation.index', $viewData);
    }

    public function showDatabaseDetailPage($categorySlug, $pageSlug, $useCaseSlug, $databaseId): View|RedirectResponse
    {
        if (!Auth::check()) { return redirect()->route('login'); }

        $currentCategory = Category::where('slug', $categorySlug)->first();
        if (!$currentCategory) { return redirect()->route('docs.index'); }

        $selectedNavItem = NavMenu::where('category_id', $currentCategory->id)->where(function($query) use ($pageSlug) { $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($pageSlug)]); })->first();
        if (!$selectedNavItem) { return redirect()->route('docs', ['category' => $categorySlug]); }

        $parentUseCase = UseCase::where('menu_id', $selectedNavItem->menu_id)->where(function($query) use ($useCaseSlug) { $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])->orWhere('id', $useCaseSlug); })->first();
        if (!$parentUseCase) { return redirect()->route('docs', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama)]); }

        $databaseData = DatabaseData::with(['images', 'documents'])->where('use_case_id', $parentUseCase->id)->where('id_database', $databaseId)->first();
        if (!$databaseData) { return redirect()->route('docs.use_case_detail', ['category' => $categorySlug, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($parentUseCase->nama_proses)]); }

        $viewData = $this->prepareCommonViewData($categorySlug, $pageSlug, $selectedNavItem);
        $viewData['databaseData'] = $databaseData;
        $viewData['parentUseCase'] = $parentUseCase;
        $viewData['contentView'] = 'documentation.database_entry_detail';
        return view('documentation.index', $viewData);
    }

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

    public function uploadSQL(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
            'navmenu_id' => 'required|exists:navmenu,menu_id',
        ]);

        $navmenuId = $request->input('navmenu_id');
        $uploadedFile = $request->file('sql_file');

        $filename = $request->sql_file->getClientOriginalName();

        $path = $uploadedFile->storeAs('sql_files', $filename, 'public');

        DocSqlFile::updateOrCreate(
            ['navmenu_id' => $navmenuId],
            ['file_name' => $filename, 'file_path' => 'public/' . $path]
        );

        $exists = Storage::disk('public')->exists('sql_files/' . $filename);
        Log::info("File disimpan di: public/sql_files/{$filename} | Exists? " . ($exists ? 'yes' : 'no'));

        Log::info("Upload SQL berhasil untuk navmenu_id: {$navmenuId}, file: {$filename}");

        return redirect()->back();
    }

    public function parse($navmenuId)
    {
        $sqlFile = DocSqlFile::where('navmenu_id', $navmenuId)->first();

        if (!$sqlFile || !Storage::disk('public')->exists('sql_files/' . $sqlFile->file_name)) {
            Log::error("SQL file tidak ditemukan untuk navmenu_id: {$navmenuId}");
            return back()->with('error', 'File SQL tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path('sql_files/' . $sqlFile->file_name);
        $sqlContent = file_get_contents($fullPath);

        Log::info("Mulai parsing HeidiSQL: {$sqlFile->file_name}");

        $oldTables = DocTables::where('menu_id', $navmenuId)->get();
        foreach ($oldTables as $tbl) {
            DocColumns::where('table_id', $tbl->id)->delete();
            DocRelations::where('from_tableid', $tbl->id)->orWhere('to_tableid', $tbl->id)->delete();
        }
        DocTables::where('menu_id', $navmenuId)->delete();

        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)? `(.*?)`\s*\((.*?)\)\s*(ENGINE|TYPE)=/si', $sqlContent, $matches, PREG_SET_ORDER);

        $tableMap = [];
        $columnMap = [];

        foreach ($matches as $match) {
            $tableName = $match[1];
            $rawColumns = $match[2];

            $table = DocTables::create([
                'menu_id' => $navmenuId,
                'nama_tabel' => $tableName,
            ]);
            $tableMap[$tableName] = $table;

            preg_match_all('/PRIMARY KEY\s+\(`(.*?)`\)/i', $rawColumns, $pkMatches);
            $primaryKeys = isset($pkMatches[1][0]) ? explode('`,`', $pkMatches[1][0]) : [];

            preg_match_all('/`([^`]+)`\s+((?:(?!,\n).)*)(?:,|\n)/i', $rawColumns, $columnMatches, PREG_SET_ORDER);
            foreach ($columnMatches as $col) {
                $name = $col[1];
                $type = trim($col[2]);

                if (strtoupper($type) === 'FOREIGN') {
                    continue;
                }

                $isPrimary = in_array($name, $primaryKeys);
                $isNullable = !str_contains(strtolower($type), 'not null');
                $isUnique = str_contains(strtolower($type), 'unique');

                $column = DocColumns::create([
                    'table_id' => $table->id,
                    'nama_kolom' => $name,
                    'tipe' => $type,
                    'is_primary' => $isPrimary,
                    'is_nullable' => $isNullable,
                    'is_unique' => $isUnique,
                    'is_foreign' => false,
                ]);

                $columnMap["$tableName.$name"] = $column;
            }
        }

        foreach ($matches as $match) {
            $tableName = $match[1];
            $rawColumns = $match[2];

            preg_match_all('/FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/i', $rawColumns, $fkMatches, PREG_SET_ORDER);
            foreach ($fkMatches as $rel) {
                $fromColumnName = $rel[1];
                $toTableName = $rel[2];
                $toColumnName = $rel[3];

                $fromColumn = $columnMap["$tableName.$fromColumnName"] ?? null;
                $toColumn = $columnMap["$toTableName.$toColumnName"] ?? null;

                if ($fromColumn && $toColumn) {
                    DocRelations::create([
                        'from_tableid' => $fromColumn->table_id,
                        'from_columnid' => $fromColumn->id,
                        'to_tableid' => $toColumn->table_id,
                        'to_columnid' => $toColumn->id,
                    ]);

                    $fromColumn->update(['is_foreign' => true]);
                }
            }
        }

        $erdData = $this->generateGoJsData($navmenuId);
        session(['erd' => $erdData]);

        return redirect()->back()->with('success', 'ERD berhasil digenerate.');
    }
    public function generateGoJsData($navmenuId)
    {
        $tables = DocTables::with(['columns', 'relations'])->where('menu_id', $navmenuId)->get();

        $nodes = [];
        $links = [];

        foreach ($tables as $table) {
            $fields = [];

            foreach ($table->columns as $col) {
                $tipe = trim($col->tipe);

                if (ltrim($tipe)[0] === '(') {
                    continue;
                }

                if (stripos($tipe, 'foreign key') !== false) {
                    continue;
                }

                $fields[] = [
                    'name' => $col->nama_kolom,
                    'type' => strtok($col->tipe, " "),
                    'suffix' => trim(
                        ($col->is_primary ? 'PK' : '') .
                        ($col->is_unique ? ' UK' : '') .
                        ($col->is_foreign ? ' FK' : '')
                    )
                ];
            }

            $nodes[] = [
                'key' => $table->nama_tabel,
                'fields' => $fields,
            ];
        }

        $relations = DocRelations::with(['fromColumn.table', 'toColumn.table'])
            ->whereIn('from_tableid', $tables->pluck('id'))
            ->orWhereIn('to_tableid', $tables->pluck('id'))
            ->get();

        foreach ($relations as $rel) {
            $fromTable = $rel->fromColumn->table->nama_tabel ?? null;
            $toTable = $rel->toColumn->table->nama_tabel ?? null;

            if ($fromTable && $toTable) {
                $links[] = [
                    'from' => $fromTable,
                    'to' => $toTable,
                    'relationship' => "{$rel->fromColumn->nama_kolom} → {$rel->toColumn->nama_kolom}"
                ];
            }
        }

        return ['nodes' => $nodes, 'links' => $links];
    }



    // Hapus File dari penyimpanan lokal dan Database
    public function destroySQL($navmenuId)
    {
        $sqlFile = DocSqlFile::where('navmenu_id', $navmenuId)->first();

        if (!$sqlFile) {
            return back()->with('error', 'File SQL tidak ditemukan di database.');
        }

        // Hapus file fisik
        $filePath = 'sql_files/' . $sqlFile->file_name;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // Hapus data dari database
        $tableIds = DocTables::where('menu_id', $navmenuId)->pluck('id');

        DocRelations::whereIn('from_tableid', $tableIds)
                    ->orWhereIn('to_tableid', $tableIds)
                    ->delete();

        DocColumns::whereIn('table_id', $tableIds)->delete();
        DocTables::where('menu_id', $navmenuId)->delete();
        $sqlFile->delete();

        return back()->with('success', 'File SQL dan datanya berhasil dihapus.');
    }
}
