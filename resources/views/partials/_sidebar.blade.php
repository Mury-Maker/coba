{{-- resources/views/partials/_sidebar.blade.php --}}
<aside id="docs-sidebar" class="w-72 flex-shrink-0 overflow-y-auto bg-stone border-r border-gray-200 p-6">
    {{-- Logo dan Judul Utama Sidebar --}}
    <div class="container-judul">
        <a href="{{ route('docs', ['category' => $currentCategorySlug]) }}">
            <img src="{{ asset('img/indoweb.png') }}" alt="Logo" class="h-10 w-auto">
        </a>
        <a href="{{ route('docs', ['category' => $currentCategorySlug]) }}"
            id="main-category-title"
            class="text-2xl font-bold text-blue-600 header-main-category-title"
            title="{!! ucwords(str_replace('-',' ',$currentCategorySlug)) !!}">
            <span class="truncate-text">Dokumentasi</span>
        </a>
        @if($editorMode)
            {{-- PERBAIKAN DI SINI: Gunakan data-action="add-parent-menu" --}}
            <button type="button" data-action="add-parent-menu"
                class="bg-blue-500 text-white h-11 rounded-lg w-full flex items-center justify-center hover:bg-blue-600 transition-colors mb-3"
                title="Tambah Menu Utama Baru">
                <i class="fa fa-plus"></i>
            </button>
        
        
        @endif
    </div>

    {{-- Sidebar Tambahan Mobile (akan tampil saat responsif) --}}
    <div class="block md:hidden space-y-4 mb-6">
        {{-- Kategori Dropdown untuk Mobile --}}
        <div class="relative">
            @auth
                <div class="text-sm">
                    Selamat Datang: <span class="font-semibold">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            @endauth
            <button id="category-dropdown-btn-mobile" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                <span id="category-button-text-mobile">{!! ucwords(str_replace('-',' ',$currentCategorySlug)) !!}</span>
                <i class="ml-2 fa fa-chevron-down text-xs"></i>
            </button>
            <div id="category-dropdown-menu-mobile" class="header-dropdown-menu">
                @foreach ($categories as $name => $slug)
                    @php
                        $isActive = ($currentCategorySlug === $slug);
                    @endphp
                    <div class="flex items-center justify-between">
                        <a href="{{ route('docs', ['category' => $slug]) }}"
                           class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                           data-category-key="{{ $slug }}"
                           data-category-name="{{ $name }}">
                            {!! ucwords(str_replace('-',' ',$name)) !!}
                        </a>
                        @if ($editorMode)
                            <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                <button type="button" data-action="edit-category" data-slug="{{ $slug }}" data-name="{{ $name }}" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($slug !== 'epesantren')
                                    <button type="button" data-action="delete-category" data-slug="{{ $slug }}" data-name="{{ $name }}" title="Hapus Kategori" class="text-red-500 hover:text-red-700 p-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
                @if ($editorMode)
                    <div class="border-t border-gray-200 my-1"></div>
                    <button type="button" data-action="add-category" class="text-blue-600 hover:underline text-sm p-3">
                        + Tambah Kategori
                    </button>
                @endif
            </div>
        </div>

        {{-- Mobile Logout Button (Visible only on mobile) --}}
        <div class="relative lg:hidden header-spacer-right space-x-4 flex items-center">
            @auth
                <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile">
                    @csrf
                    <button type="submit" class="mt-2 w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm text-center">
                    Log In
                </a>
            @endauth
        </div>
    </div>

    {{-- Notifikasi Kontainer --}}
    <div id="notification-container"></div>

    {{-- Navigasi Utama Sidebar --}}
    <nav id="sidebar-navigation">
        <ul>
            {{-- _menu_item akan menangani kondisional untuk tombol edit/delete/add-child --}}
            @include('partials._menu_item', [
                'items' => $navigation,
                'editorMode' => $editorMode,
                'selectedNavItemId' => $selectedNavItemId,
                'currentCategorySlug' => $currentCategorySlug,
                'level' => 0 // Level awal untuk rekursi
            ])
        </ul>
    </nav>
</aside>
