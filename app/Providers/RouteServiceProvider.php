<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Category; // Pastikan ini di-import

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // HAPUS BLOK INI:
            /*
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            */

            // BIARKAN BLOK INI:
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // Pastikan Route Model Binding ini tetap ada
        Route::bind('category', function ($value) {
            return Category::where('slug', $value)->firstOrFail();
        });
    }
}
