<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\NavMenu;
use App\Models\Category; // Tambahkan ini
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $defaultCategorySlug = 'epesantren';

            $defaultCategory = Category::where('slug', $defaultCategorySlug)->first();

            if ($defaultCategory) {
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

                    if ($firstAnyMenu && trim($firstAnyMenu->menu_nama) !== '') {
                        return redirect()->route('docs', [
                            'category' => $defaultCategorySlug,
                            'page' => Str::slug($firstAnyMenu->menu_nama),
                        ]);
                    }
                }
            }

            return redirect()->route('docs.index');
        }

        throw ValidationException::withMessages([
            'email' => ('Email atau password yang Anda masukkan salah.'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
