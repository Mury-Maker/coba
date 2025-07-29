{{-- resources/views/layouts/docs.blade.php --}}
@extends('layouts.app')

@section('content')
    @php
        // Pastikan $userRole selalu terdefinisi
        $loggedInUserRole = Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest';
        $editorMode = ($loggedInUserRole === 'admin'); // Tentukan editorMode di sini
    @endphp

    <div class="flex h-screen" data-user-role="{{ $loggedInUserRole }}"
         data-current-category="{{ $currentCategory ?? '' }}"
         data-current-menu-id="{{ $selectedNavItem->menu_id ?? '' }}">

        {{-- Sidebar Component --}}
        @include('partials._sidebar', [
            'currentCategorySlug' => $currentCategory ?? 'epesantren',
            'selectedNavItemId' => $selectedNavItem->menu_id ?? null,
            'navigation' => $navigation ?? [],
            'categories' => $categories ?? [],
            'editorMode' => $editorMode // Teruskan variabel yang sudah pasti terdefinisi
        ])

        {{-- Wrapper Konten Utama (Header + Konten Utama) --}}
        <div id="content-area-wrapper" class="flex-1 flex flex-col">
            {{-- Header Component --}}
            @include('partials._header', [
                'currentCategory' => $currentCategory ?? 'epesantren',
                'categories' => $categories ?? [],
                'userRole' => $loggedInUserRole // Teruskan variabel yang sudah pasti terdefinisi
            ])

            {{-- ... sisa kode layouts/docs.blade.php ... --}}

            {{-- Load all modular JS files --}}
            <script src="{{ asset('js/app.js') }}" type="module"></script>

            {{-- Data Blade yang diperlukan di JS --}}
            <script>
                // window.APP_BLADE_DATA akan diisi di app.js DOMContentLoaded,
                // tapi kita bisa berikan default yang lebih aman.
                window.APP_BLADE_DATA = window.APP_BLADE_DATA || {};
                window.APP_BLADE_DATA.userRole = "{{ $loggedInUserRole }}";
                window.APP_BLADE_DATA.currentCategorySlug = "{{ $currentCategory ?? '' }}";
                window.APP_BLADE_DATA.currentMenuId = {{ $selectedNavItem->menu_id ?? 'null' }};
                window.APP_BLADE_DATA.singleUseCase = {!! isset($singleUseCase) ? json_encode($singleUseCase) : 'null' !!};
                window.APP_BLADE_DATA.useCases = {!! isset($useCases) ? json_encode($useCases->toArray()) : '[]' !!};
                // Tambahkan data lain yang mungkin dibutuhkan oleh JS di sini
            </script>
        @endsection
