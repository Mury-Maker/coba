<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Docs</title>
    {{-- Jika Anda tidak menggunakan kompilasi aset (npm), Anda bisa menggunakan CDN --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> {{-- Jika ada styling global --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="flex items-center justify-center min-h-screen">

    <div id="login-card" class="relative w-full max-w-md mx-auto bg-white rounded-2xl shadow-lg p-8 m-4">

        <div class="text-center mb-8">
            <div class="logo">
                <img src="{{ asset('img/indoweb.png')}}" alt="Logo E-Docs">
            </div>
            <h1 class="text-3xl font-bold text-gray-800">E - Docs</h1>
            <p class="text-gray-500 mt-2">Silakan masukkan email dan password Anda</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            @error('email')
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded-lg" role="alert">
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Alamat Email:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-4 focus:ring-blue-200 focus:border-blue-500 transition duration-300"
                    value="{{ old('email') }}"
                >
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        Lupa password?
                    </a>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-4 focus:ring-blue-200 focus:border-blue-500 transition duration-300"
                >
            </div>

            <div class="flex items-center mb-6">
                <input
                    id="remember-me"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                >
                <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                    Remember me
                </label>
            </div>

            <div>
                <button
                    id="login-button"
                    type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-transform transform hover:scale-105 duration-300"
                >
                    Log In
                </button>
            </div>

        </form>
    </div>

</body>
</html>
