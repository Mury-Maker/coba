<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-Docs' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Tetap sertakan auth.css jika ada gaya kustom spesifik untuk login yang tidak pakai kelas Tailwind --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @stack('styles')
</head>
<body class="bg-gray-100">

    @yield('content') {{-- Ini adalah tempat konten dari layout anak (docs.blade.php) akan dimasukkan --}}

    {{-- Script SweetAlert2 sudah ada di head, jadi tidak perlu di sini lagi --}}
    {{-- @stack('scripts') --}}
    {{-- Catatan: app.js akan dimuat di layouts/docs.blade.php, bukan di sini. --}}

</body>
</html>
