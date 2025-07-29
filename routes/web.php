<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentationController; // Untuk menampilkan halaman
use App\Http\Controllers\Admin\CategoryController; // Untuk API admin (akan dipanggil dari JS)
use App\Http\Controllers\Admin\NavMenuController; // Untuk API admin (akan dipanggil dari JS)
use App\Http\Controllers\UseCaseData\UseCaseController; // Untuk API data use case (akan dipanggil dari JS)
use App\Http\Controllers\UseCaseData\UatDataController; // Untuk API data UAT (akan dipanggil dari JS)
use App\Http\Controllers\UseCaseData\ReportDataController; // Untuk API data Report (akan dipanggil dari JS)
use App\Http\Controllers\UseCaseData\DatabaseDataController; // Untuk API data Database (akan dipanggil dari JS)
use App\Http\Controllers\SearchController; // Untuk API search (akan dipanggil dari JS)
use Illuminate\Support\Facades\Auth;

// Mengatur rute root '/'
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('docs.index'); // Redirect ke halaman docs jika sudah login
    }
    return redirect()->route('login'); // Redirect ke halaman login jika belum login
})->name('home');

// Rute Login/Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post'); // Ubah nama rute untuk post
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute Dokumentasi Utama
Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');

// Rute untuk menampilkan menu (daftar use case atau halaman folder)
Route::get('/docs/{category}/{page?}', [DocumentationController::class, 'show'])->name('docs');

// Rute Detail Use Case
Route::get('/docs/{category}/{page}/{useCaseSlug}', [DocumentationController::class, 'showUseCaseDetail'])
     ->name('docs.use_case_detail');

// Rute Detail Halaman UAT Data
Route::get('/docs/{category}/{page}/{useCaseSlug}/uat/{uatId}', [DocumentationController::class, 'showUatDetailPage'])
     ->name('docs.use_case_uat_detail_page');

// Rute Detail Halaman Report Data
Route::get('/docs/{category}/{page}/{useCaseSlug}/report/{reportId}', [DocumentationController::class, 'showReportDetailPage'])
     ->name('docs.use_case_report_detail_page');

// Rute Detail Halaman Database Data
Route::get('/docs/{category}/{page}/{useCaseSlug}/database/{databaseId}', [DocumentationController::class, 'showDatabaseDetailPage'])
     ->name('docs.use_case_database_detail_page');

// Catatan: Rute API akan tetap ada di api.php dan akan dipanggil via AJAX.
// Autentikasi untuk API akan ditangani di dalam Controller methods masing-masing.
