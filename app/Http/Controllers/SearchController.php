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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Melakukan pencarian di seluruh dokumentasi dengan filter kategori dan relevansi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized: Anda harus login untuk melakukan pencarian.'], 401);
        }

        $query = $request->input('query');
        $currentCategorySlug = $request->input('category_slug');

        if (!$query) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $searchTerm = strtolower($query);

        $categoryId = null;
        if ($currentCategorySlug) {
            $category = Category::where('slug', $currentCategorySlug)->first();
            if ($category) {
                $categoryId = $category->id;
            }
        }

        // --- LOGIKA PENCARIAN & SKOR RELEVANSI ---

        // 1. Cari di NavMenu (Judul Menu)
        $menuQuery = NavMenu::query();
        if ($categoryId) {
            $menuQuery->where('category_id', $categoryId);
        }
        $menuMatches = $menuQuery
            ->whereRaw('LOWER(menu_nama) LIKE ?', ["%$searchTerm%"])
            ->with('category')
            ->get();

        foreach ($menuMatches as $menu) {
            $categorySlug = $menu->category ? $menu->category->slug : 'unknown-category';
            $categoryName = $menu->category ? Str::headline($menu->category->name) : 'Unknown Category';

            $relevance = 0;
            if (strtolower($menu->menu_nama) === $searchTerm) {
                $relevance = 100;
            } else if (Str::startsWith(strtolower($menu->menu_nama), $searchTerm)) {
                $relevance = 80;
            } else {
                $relevance = 50;
            }

            $results[] = [
                'id' => $menu->menu_id,
                'name' => $menu->menu_nama,
                'category_name' => $categoryName,
                'url' => route('docs', ['category' => $categorySlug, 'page' => Str::slug($menu->menu_nama)]),
                'context' => 'Judul Menu',
                'relevance' => $relevance,
                'snippet' => ''
            ];
        }

        // 2. Cari di UseCase (nama_proses & deskripsi_aksi)
        $useCaseQuery = UseCase::query();
        if ($categoryId) {
            $useCaseQuery->whereHas('menu', fn ($q) => $q->where('category_id', $categoryId));
        }
        $useCaseMatches = $useCaseQuery
            ->where(fn ($q) =>
                $q->whereRaw('LOWER(nama_proses) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(deskripsi_aksi) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(aktor) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%$searchTerm%"])
            )
            ->with('menu.category')
            ->get();

        foreach ($useCaseMatches as $useCase) {
            if ($useCase->menu && $useCase->menu->category) {
                $relevance = 40;
                $snippet = '';
                $context = 'Detail Aksi';

                if (Str::contains(strtolower($useCase->deskripsi_aksi), $searchTerm)) {
                    $relevance = 70;
                    $snippet = $useCase->deskripsi_aksi;
                    $context = 'Deskripsi Aksi'; // Konteks lebih spesifik
                } else if (Str::contains(strtolower($useCase->nama_proses), $searchTerm)) {
                    $relevance = 90;
                    $snippet = $useCase->nama_proses;
                    $context = 'Nama Proses'; // Konteks lebih spesifik
                }

                $results[] = [
                    'id' => $useCase->id,
                    'name' => $useCase->nama_proses,
                    'category_name' => Str::headline($useCase->menu->category->name) . ' > ' . $useCase->menu->menu_nama,
                    'url' => route('docs.use_case_detail', [
                        'category' => $useCase->menu->category->slug,
                        'page' => Str::slug($useCase->menu->menu_nama),
                        'useCaseSlug' => Str::slug($useCase->nama_proses)
                    ]),
                    'context' => $context,
                    'relevance' => $relevance,
                    'snippet' => Str::limit(strip_tags($snippet), 150)
                ];
            }
        }

        // 3. Cari di UatData
        $uatDataQuery = UatData::query();
        if ($categoryId) {
            $uatDataQuery->whereHas('useCase.menu', fn ($q) => $q->where('category_id', $categoryId));
        }
        $uatMatches = $uatDataQuery
            ->where(fn ($q) =>
                $q->whereRaw('LOWER(nama_proses_usecase) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(keterangan_uat) LIKE ?', ["%$searchTerm%"])
            )
            ->with('useCase.menu.category')
            ->get();

        foreach ($uatMatches as $uat) {
            if ($uat->useCase && $uat->useCase->menu && $uat->useCase->menu->category) {
                $snippet = '';
                $context = 'Detail UAT';

                if (Str::contains(strtolower($uat->keterangan_uat), $searchTerm)) {
                    $snippet = $uat->keterangan_uat;
                    $context = 'Keterangan UAT'; // Konteks lebih spesifik
                } else {
                    $snippet = $uat->nama_proses_usecase;
                    $context = 'Nama Proses UAT'; // Konteks lebih spesifik
                }

                $results[] = [
                    'id' => $uat->id,
                    'name' => $uat->nama_proses_usecase,
                    'category_name' => Str::headline($uat->useCase->menu->category->name) . ' > ' . $uat->useCase->menu->menu_nama . ' > ' . $uat->useCase->nama_proses,
                    'url' => route('docs.use_case_uat_detail_page', [
                        'category' => $uat->useCase->menu->category->slug,
                        'page' => Str::slug($uat->useCase->menu->menu_nama),
                        'useCaseSlug' => Str::slug($uat->useCase->nama_proses),
                        'uatId' => $uat->id_uat
                    ]),
                    'context' => $context,
                    'relevance' => 60,
                    'snippet' => Str::limit(strip_tags($snippet), 150)
                ];
            }
        }

        // 4. Cari di ReportData
        $reportDataQuery = ReportData::query();
        if ($categoryId) {
            $reportDataQuery->whereHas('useCase.menu', fn ($q) => $q->where('category_id', $categoryId));
        }
        $reportMatches = $reportDataQuery
            ->where(fn ($q) =>
                $q->whereRaw('LOWER(nama_report) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(keterangan) LIKE ?', ["%$searchTerm%"])
            )
            ->with('useCase.menu.category')
            ->get();

        foreach ($reportMatches as $report) {
            if ($report->useCase && $report->useCase->menu && $report->useCase->menu->category) {
                $snippet = Str::contains(strtolower($report->keterangan), $searchTerm) ? $report->keterangan : $report->nama_report;

                $results[] = [
                    'id' => $report->id,
                    'name' => $report->nama_report,
                    'category_name' => Str::headline($report->useCase->menu->category->name) . ' > ' . $report->useCase->menu->menu_nama . ' > ' . $report->useCase->nama_proses,
                    'url' => route('docs.use_case_report_detail_page', [
                        'category' => $report->useCase->menu->category->slug,
                        'page' => Str::slug($report->useCase->menu->menu_nama),
                        'useCaseSlug' => Str::slug($report->useCase->nama_proses),
                        'reportId' => $report->id_report
                    ]),
                    'context' => 'Detail Laporan',
                    'relevance' => 55,
                    'snippet' => Str::limit(strip_tags($snippet), 150)
                ];
            }
        }

        // 5. Cari di DatabaseData
        $databaseDataQuery = DatabaseData::query();
        if ($categoryId) {
            $databaseDataQuery->whereHas('useCase.menu', fn ($q) => $q->where('category_id', $categoryId));
        }
        $databaseMatches = $databaseDataQuery
            ->where(fn ($q) =>
                $q->whereRaw('LOWER(keterangan) LIKE ?', ["%$searchTerm%"])
                  ->orWhereRaw('LOWER(relasi) LIKE ?', ["%$searchTerm%"])
            )
            ->with('useCase.menu.category')
            ->get();

        foreach ($databaseMatches as $database) {
            if ($database->useCase && $database->useCase->menu && $database->useCase->menu->category) {
                $snippet = Str::contains(strtolower($database->relasi), $searchTerm) ? $database->relasi : $database->keterangan;

                $results[] = [
                    'id' => $database->id,
                    'name' => $database->keterangan,
                    'category_name' => Str::headline($database->useCase->menu->category->name) . ' > ' . $database->useCase->menu->menu_nama . ' > ' . $database->useCase->nama_proses,
                    'url' => route('docs.use_case_database_detail_page', [
                        'category' => $database->useCase->menu->category->slug,
                        'page' => Str::slug($database->useCase->menu->menu_nama),
                        'useCaseSlug' => Str::slug($database->useCase->nama_proses),
                        'databaseId' => $database->id_database
                    ]),
                    'context' => 'Detail Database',
                    'relevance' => 55,
                    'snippet' => Str::limit(strip_tags($snippet), 150)
                ];
            }
        }

        // Urutkan hasil berdasarkan relevansi (tertinggi ke terendah)
        usort($results, fn ($a, $b) => $b['relevance'] <=> $a['relevance']);

        return response()->json(['results' => array_values($results)]);
    }
}
