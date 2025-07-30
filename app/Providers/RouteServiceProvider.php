<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Tetap ada untuk logging
use App\Models\Category; // Pastikan ini di-import

class RouteServiceProvider extends ServiceProvider
{
    // ...
    public function boot(): void
    {
        // ...
        $this->routes(function () {
            // ...
        });

        // --- HAPUS ATAU KOMENTARI BLOK INI ---
        /*
        Route::bind('category', function ($value) {
            Log::info('Route Model Binding: Mencari kategori dengan slug: ' . $value);
            DB::enableQueryLog();
            $category = Category::where('slug', $value)->first();
            $queryLog = DB::getQueryLog();
            Log::info('Query SQL yang dieksekusi: ', $queryLog);
            dd([
                'slug_yang_dicari' => $value,
                'kategori_ditemukan' => $category ? true : false,
                'objek_kategori' => $category,
                'query_sql_yang_dijalankan' => $queryLog,
                'pesan' => 'Ini adalah output dari Route Model Binding'
            ]);
            return $category;
        });
        */
        // --- AKHIR HAPUS/KOMENTARI ---
    }
}
