<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NavMenuController;
use App\Http\Controllers\UseCaseData\UseCaseController;
use App\Http\Controllers\UseCaseData\UatDataController;
use App\Http\Controllers\UseCaseData\ReportDataController;
use App\Http\Controllers\UseCaseData\DatabaseDataController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Diperlukan untuk rute API jika menggunakan Request binding

// Mengatur rute root '/'
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('docs.index'); // Redirect ke halaman docs jika sudah login
    }
    return redirect()->route('login'); // Redirect ke halaman login jika belum login
})->name('home');

// Rute Login/Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- Rute Dokumentasi (Web) ---
Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
Route::get('/docs/{category}/{page?}', [DocumentationController::class, 'show'])->name('docs');
Route::get('/docs/{category}/{page}/{useCaseSlug}', [DocumentationController::class, 'showUseCaseDetail'])->name('docs.use_case_detail');
Route::get('/docs/{category}/{page}/{useCaseSlug}/uat/{uatId}', [DocumentationController::class, 'showUatDetailPage'])->name('docs.use_case_uat_detail_page');
Route::get('/docs/{category}/{page}/{useCaseSlug}/report/{reportId}', [DocumentationController::class, 'showReportDetailPage'])->name('docs.use_case_report_detail_page');
Route::get('/docs/{category}/{page}/{useCaseSlug}/database/{databaseId}', [DocumentationController::class, 'showDatabaseDetailPage'])->name('docs.use_case_database_detail_page');

// --- Rute API (Sekarang di web.php) ---
// Autentikasi & Otorisasi akan ditangani di dalam Controller methods masing-masing.
// Tidak ada middleware 'api' atau 'auth:sanctum' di sini, hanya middleware 'web' default.

// Rute API untuk Pencarian
Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

// --- Rute API untuk Kategori (CRUD) ---
Route::prefix('api/categories')->group(function () {
    Route::get('/{category}', [CategoryController::class, 'getCategoryData'])->name('api.categories.get');
    Route::post('/', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
});

// --- Rute API untuk Navigasi/Menu (CRUD) ---
Route::prefix('api/navmenu')->group(function () {
    Route::get('/all/{categorySlug}', [NavMenuController::class, 'getAllMenusForSidebar'])->name('api.navmenu.all');
    Route::get('/parents/{categorySlug}', [NavMenuController::class, 'getParentMenus'])->name('api.navmenu.parents');
    Route::get('/{navMenu}', [NavMenuController::class, 'getMenuData'])->name('api.navmenu.get');
    Route::post('/', [NavMenuController::class, 'store'])->name('api.navmenu.store');
    Route::put('/{navMenu}', [NavMenuController::class, 'update'])->name('api.navmenu.update');
    Route::delete('/{navMenu}', [NavMenuController::class, 'destroy'])->name('api.navmenu.destroy');
});

// --- Rute API untuk UseCase Utama (CRUD) ---
Route::prefix('api/usecase')->group(function () {
    Route::post('/', [UseCaseController::class, 'store'])->name('api.usecase.store');
    Route::put('/{useCase}', [UseCaseController::class, 'update'])->name('api.usecase.update');
    Route::delete('/{useCase}', [UseCaseController::class, 'destroy'])->name('api.usecase.destroy');

    // --- Rute API untuk UAT Data ---
    Route::post('/uat', [UatDataController::class, 'store'])->name('api.usecase.uat.store');
    Route::post('/uat/{uatData}', [UatDataController::class, 'update'])->name('api.usecase.uat.update'); // Update dengan POST + _method override
    Route::delete('/uat/{uatData}', [UatDataController::class, 'destroy'])->name('api.usecase.uat.destroy');

    // --- Rute API untuk Report Data ---
    Route::post('/report', [ReportDataController::class, 'store'])->name('api.usecase.report.store');
    Route::put('/report/{reportData}', [ReportDataController::class, 'update'])->name('api.usecase.report.update');
    Route::delete('/report/{reportData}', [ReportDataController::class, 'destroy'])->name('api.usecase.report.destroy');

    // --- Rute API untuk Database Data ---
    Route::post('/database', [DatabaseDataController::class, 'store'])->name('api.usecase.database.store');
    Route::post('/database/{databaseData}', [DatabaseDataController::class, 'update'])->name('api.usecase.database.update');
    Route::delete('/database/{databaseData}', [DatabaseDataController::class, 'destroy'])->name('api.usecase.database.destroy');
});
