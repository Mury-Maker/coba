<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NavMenuController;
use App\Http\Controllers\UseCaseData\UseCaseController;
use App\Http\Controllers\UseCaseData\UatDataController;
use App\Http\Controllers\UseCaseData\ReportDataController;
use App\Http\Controllers\UseCaseData\DatabaseDataController;
use App\Http\Controllers\SearchController;

// Rute API untuk Pencarian (tidak memerlukan autentikasi spesifik di sini)
Route::get('/search', [SearchController::class, 'search'])->name('api.search');

// --- Rute API untuk Kategori (CRUD) ---
// Autentikasi & Otorisasi akan ditangani di dalam CategoryController
Route::prefix('categories')->group(function () {
    Route::get('/{category}', [CategoryController::class, 'getCategoryData'])->name('api.categories.get');
    Route::post('/', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
});

// --- Rute API untuk Navigasi/Menu (CRUD) ---
// Autentikasi & Otorisasi akan ditangani di dalam NavMenuController
Route::prefix('navmenu')->group(function () {
    Route::get('/all/{categorySlug}', [NavMenuController::class, 'getAllMenusForSidebar'])->name('api.navmenu.all');
    Route::get('/parents/{categorySlug}', [NavMenuController::class, 'getParentMenus'])->name('api.navmenu.parents');
    Route::get('/{navMenu}', [NavMenuController::class, 'getMenuData'])->name('api.navmenu.get');
    Route::post('/', [NavMenuController::class, 'store'])->name('api.navmenu.store');
    Route::put('/{navMenu}', [NavMenuController::class, 'update'])->name('api.navmenu.update');
    Route::delete('/{navMenu}', [NavMenuController::class, 'destroy'])->name('api.navmenu.destroy');
});

// --- Rute API untuk UseCase Utama (CRUD) ---
// Autentikasi & Otorisasi akan ditangani di dalam UseCaseController
Route::prefix('usecase')->group(function () {
    Route::post('/', [UseCaseController::class, 'store'])->name('api.usecase.store');
    Route::put('/{useCase}', [UseCaseController::class, 'update'])->name('api.usecase.update');
    Route::delete('/{useCase}', [UseCaseController::class, 'destroy'])->name('api.usecase.destroy');

    // --- Rute API untuk UAT Data ---
    // Autentikasi & Otorisasi akan ditangani di dalam UatDataController
    Route::post('/uat', [UatDataController::class, 'store'])->name('api.usecase.uat.store');
    Route::post('/uat/{uatData}', [UatDataController::class, 'update'])->name('api.usecase.uat.update'); // Menggunakan POST untuk update file
    Route::delete('/uat/{uatData}', [UatDataController::class, 'destroy'])->name('api.usecase.uat.destroy');

    // --- Rute API untuk Report Data ---
    // Autentikasi & Otorisasi akan ditangani di dalam ReportDataController
    Route::post('/report', [ReportDataController::class, 'store'])->name('api.usecase.report.store');
    Route::put('/report/{reportData}', [ReportDataController::class, 'update'])->name('api.usecase.report.update');
    Route::delete('/report/{reportData}', [ReportDataController::class, 'destroy'])->name('api.usecase.report.destroy');

    // --- Rute API untuk Database Data ---
    // Autentikasi & Otorisasi akan ditangani di dalam DatabaseDataController
    Route::post('/database', [DatabaseDataController::class, 'store'])->name('api.usecase.database.store');
    Route::post('/database/{databaseData}', [DatabaseDataController::class, 'update'])->name('api.usecase.database.update'); // Menggunakan POST untuk update file
    Route::delete('/database/{databaseData}', [DatabaseDataController::class, 'destroy'])->name('api.usecase.database.destroy');
});
