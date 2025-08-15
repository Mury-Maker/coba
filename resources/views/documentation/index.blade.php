<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-Docs' }}</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome untuk ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    {{-- SweetAlert2 untuk konfirmasi dan pop-up yang indah --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Tetap sertakan auth.css jika Anda punya gaya kustom spesifik untuk login yang tidak pakai kelas Tailwind --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    {{-- Ini untuk gaya kustom global yang tidak ada di CDN Tailwind --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- simplelightbox --}}
    <link rel="stylesheet" href="{{ asset('css/imageviewer.css') }}">

    @stack('styles')
</head>
<body class="bg-gray-100">

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
            'editorMode' => $editorMode
        ])

        {{-- Wrapper Konten Utama (Header + Konten Utama) --}}
        <div id="content-area-wrapper" class="flex-1 flex flex-col">
            {{-- Header Component --}}
            @include('partials._header', [
                'currentCategorySlug' => $currentCategory ?? 'epesantren',
                'categories' => $categories ?? [],
                'userRole' => $loggedInUserRole
            ])


            {{-- Main Content Area --}}
            <main class="flex-1 overflow-y-auto p-8 lg:p-12 relative" style="background-color: white">
                <nav class="flex items-center text-sm text-gray-600 mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                        {{-- Link Home --}}
                        <li class="inline-flex items-center">
                            @php
                                $homeCategoryRoute = route('docs', ['category' => $currentCategory]);
                                $isHomeActive = empty($selectedNavItem) && empty($parentUseCase) && empty($singleUseCase);
                            @endphp
                            <a href="{{ $homeCategoryRoute }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition {{ $isHomeActive ? 'text-gray-800 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2L2 10h3v6h10v-6h3L10 2z" />
                                </svg>
                                Home
                            </a>
                        </li>
                
                        {{-- Link Navigasi Induk (Parent Nav Item) --}}
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
                
                        {{-- Link Navigasi Terpilih (Selected Nav Item) --}}
                        @if (isset($selectedNavItem))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    @php
                                        // Tautan ini hanya perlu aktif jika tidak ada detail use case yang ditampilkan
                                        $isUseCaseActive = empty($singleUseCase) && empty($parentUseCase) && empty($databaseData) && empty($reportData) && empty($uatData);
                                    @endphp
                                    <a href="{{ route('docs', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama)]) }}"
                                       class="{{ $isUseCaseActive ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600' }} transition">
                                        {{ $selectedNavItem->menu_nama }}
                                    </a>
                                </div>
                            </li>
                        @endif
                
                        {{-- Link Detail UseCase (jika ada) --}}
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
                
                        {{-- Link Detail Single UseCase (jika ada) --}}
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
                
                        {{-- Link Database --}}
                        @if (isset($databaseData))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        Detail - Database
                                    </span>
                                </div>
                            </li>
                        @endif
                        
                        {{-- Link Report --}}
                        @if (isset($reportData))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                       Detail - Report
                                    </span>
                                </div>
                            </li>
                        @endif
                
                        {{-- Link UAT --}}
                        @if (isset($uatData))
                            <li>
                                <div class="flex items-center">
                                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.05 4.05a1 1 0 011.414 0L14 9.586l-5.536 5.535a1 1 0 01-1.414-1.414L11.172 10 7.05 5.879a1 1 0 010-1.414z" />
                                    </svg>
                                    <span class="text-blue-600 font-semibold">
                                        Detail - UAT
                                    </span>
                                </div>
                            </li>
                        @endif
                    </ol>
                </nav>

                {{-- Kondisi untuk menampilkan konten spesifik halaman --}}
                @if(isset($contentView) && $contentView === 'documentation.use_case_list')
                    @include('documentation.use_case_list')
                @elseif(isset($contentView) && $contentView === 'documentation.use_case_detail')
                    @include('documentation.use_case_detail')
                @elseif(isset($contentView) && $contentView === 'documentation.tables_list')
                    @include('documentation.tables_list')
                @elseif(isset($contentView) && $contentView === 'documentation.homepage')
                    @include('documentation.homepage')
                @elseif(isset($contentView) && $contentView === 'documentation.uat_entry_detail')
                    @include('documentation.uat_entry_detail')
                @elseif(isset($contentView) && $contentView === 'documentation.report_entry_detail')
                    @include('documentation.report_entry_detail')
                @elseif(isset($contentView) && $contentView === 'documentation.database_entry_detail')
                    @include('documentation.database_entry_detail')
                @else
                    {{-- Default jika $contentView tidak diset atau tidak cocok --}}
                    <div class="text-center p-8 bg-gray-50 border border-gray-200 rounded-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Konten Tidak Ditemukan</h3>
                        <p class="text-gray-600">Mohon periksa URL atau definisi konten Anda.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>

    {{-- Modals Global --}}
    @include('partials._search_modal')
    @include('partials._modals._common_detail_modal')
    @include('partials._modals._common_confirm_modal')
    @include('partials._modals._common_success_popup')
    @include('partials._modals._image_viewer_manual_modal')

    {{-- Jika ada kategori admin, tampilkan modal admin --}}

    @if (Auth::check() && Auth::user()->role === 'admin')
        {{-- Modal Admin --}}
        @include('partials._modals._admin_category_modal')
        @include('partials._modals._admin_navmenu_modal')
        @include('partials._modals._use_case_modal')
        @include('partials._modals._uat_data_modal')
        @include('partials._modals._report_data_modal')
        @include('partials._modals._database_data_modal')
    @endif

    {{-- Data Blade yang diperlukan di JS --}}
    <script>
        // Inisialisasi window.APP_BLADE_DATA di sini, SEBELUM app.js dimuat
        window.APP_BLADE_DATA = {
            csrfToken: "{{ csrf_token() }}",
            userRole: "{{ Auth::check() ? (Auth::user()->role ?? 'guest') : 'guest' }}",
            currentCategorySlug: "{{ $currentCategory ?? '' }}",
            // Tambahkan ID numerik kategori di sini:
            currentCategoryId: {{ isset($selectedNavItem) && $selectedNavItem->category ? $selectedNavItem->category->id : ($currentCategoryObject->id ?? 'null') }},
            // Logika fallback untuk currentCategoryId jika $selectedNavItem tidak ada atau category tidak ada.
            // Anda perlu memastikan $currentCategoryObject tersedia di controller jika index() yang dipanggil langsung.
            // Atau, ambil ID-nya dari kategori yang slug-nya sedang aktif di controller.

            currentPage: "{{ $currentPage ?? '' }}", // <<< BARIS TAMBAHAN
            currentMenuId: {{ $selectedNavItem->menu_id ?? 'null' }},
            singleUseCase: {!! isset($singleUseCase) ? json_encode($singleUseCase->append('slug_nama_proses')) : 'null' !!}, // <<< PERUBAHAN json_encode
            useCases: {!! isset($useCases) ? json_encode($useCases->toArray()) : '[]' !!},
        };
    </script>
    {{-- Load all modular JS files --}}
    <script src="{{ asset('js/app.js') }}" type="module"></script>
    {{-- <script src="{{ asset('js/utils/imageViewerManual.js') }}"></script> --}}
    {{-- Or if it needs defer: --}}
    {{-- <script src="{{ asset('js/imageViewerManual.js') }}" defer></script> --}}

</body>
</html>
