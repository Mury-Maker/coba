@extends('layouts.app')

@section('content')
    <div class="flex h-screen" data-user-role="{{ Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest' }}"
         data-current-category="{{ $currentCategory ?? '' }}"
         data-current-menu-id="{{ $selectedNavItem->menu_id ?? '' }}">

        {{-- Sidebar Component --}}
        @include('partials._sidebar', [
            'currentCategorySlug' => $currentCategory ?? 'epesantren',
            'selectedNavItemId' => $selectedNavItem->menu_id ?? null,
            'navigation' => $navigation ?? [],
            'categories' => $categories ?? [],
            'editorMode' => (Auth::check() && (Auth::user()->role ?? '') === 'admin')
        ])

        {{-- Wrapper Konten Utama (Header + Konten Utama) --}}
        <div id="content-area-wrapper" class="flex-1 flex flex-col">
            {{-- Header Component --}}
            @include('partials._header', [
                'currentCategory' => $currentCategory ?? 'epesantren',
                'categories' => $categories ?? [],
                'userRole' => Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest'
            ])

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <nav class="flex items-center text-sm text-gray-600 mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                        {{-- Home (Category Level) --}}
                        <li class="inline-flex items-center">
                            @php
                                $homeCategoryRoute = route('docs', ['category' => $currentCategory]);
                                $isHomeActive = empty($selectedNavItem) && empty($parentUseCase);
                            @endphp
                            <a href="{{ $homeCategoryRoute }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition {{ $isHomeActive ? 'text-gray-800 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2L2 10h3v6h10v-6h3L10 2z" />
                                </svg>
                                Home
                            </a>
                        </li>
                        @if (isset($selectedNavItem) && $selectedNavItem->parent)
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <a href="{{ route('docs', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->parent->menu_nama)]) }}"
                                       class="text-gray-500 hover:text-blue-600 transition">
                                        {{ $selectedNavItem->parent->menu_nama }}
                                    </a>
                                </div>
                            </li>
                        @endif
                        @if (isset($selectedNavItem) && empty($singleUseCase) && empty($parentUseCase))
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        {{ $selectedNavItem->menu_nama }}
                                    </span>
                                </div>
                            </li>
                        @elseif(isset($selectedNavItem) && (isset($singleUseCase) || isset($parentUseCase)))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <a href="{{ route('docs', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama)]) }}"
                                       class="text-gray-500 hover:text-blue-600 transition">
                                        {{ $selectedNavItem->menu_nama }}
                                    </a>
                                </div>
                            </li>
                        @endif
                        @if (!empty($singleUseCase))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <a href="{{ route('docs.use_case_detail', [
                                        'category' => $currentCategory,
                                        'page' => Str::slug($selectedNavItem->menu_nama),
                                        'useCaseSlug' => Str::slug($singleUseCase->nama_proses)
                                    ]) }}" class="text-blue-600 font-semibold">
                                        Detail - {{ $singleUseCase->nama_proses }}
                                    </a>
                                </div>
                            </li>
                        @endif
                        @if (!empty($parentUseCase))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <a href="{{ route('docs.use_case_detail', [
                                        'category' => $currentCategory,
                                        'page' => Str::slug($selectedNavItem->menu_nama),
                                        'useCaseSlug' => Str::slug($parentUseCase->nama_proses)
                                    ]) }}" class="text-gray-500 hover:text-blue-600 transition">
                                        Detail - {{ $parentUseCase->nama_proses }}
                                    </a>
                                </div>
                            </li>
                        @endif
                        @if (isset($databaseData))
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        Database
                                    </span>
                                </div>
                            </li>
                        @elseif (isset($reportData))
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        Report
                                    </span>
                                </div>
                            </li>
                        @elseif (isset($uatData))
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        UAT
                                    </span>
                                </div>
                            </li>
                        @endif

                    </ol>
                </nav>
                <div class="judul-halaman">
                    <h1 id="main-content-title"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>
                    @yield('action-buttons')
                </div>
                {{-- Konten Dinamis Halaman --}}
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Modals Global --}}
    @include('partials._search_modal')
    @include('partials._modals._common_detail_modal')
    @include('partials._modals._common_confirm_modal')
    @include('partials._modals._common_success_popup')

    @if (Auth::check() && Auth::user()->role === 'admin')
        {{-- Modal Admin --}}
        @include('partials._modals._admin_category_modal')
        @include('partials._modals._admin_navmenu_modal')
        @include('partials._modals._use_case_modal')
        @include('partials._modals._uat_data_modal')
        @include('partials._modals._report_data_modal')
        @include('partials._modals._database_data_modal')
    @endif

    {{-- Load all modular JS files --}}
    <script src="{{ asset('js/app.js') }}" type="module"></script>

    {{-- Data Blade yang diperlukan di JS --}}
    <script>
        // window.APP_BLADE_DATA akan diisi di app.js DOMContentLoaded
    </script>
@endsection
