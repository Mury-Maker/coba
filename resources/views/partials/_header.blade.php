{{-- resources/views/partials/_header.blade.php --}}
<header id="main-header" class="bg-white shadow-sm w-full border-b border-gray-200 z-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center header-content-wrapper">

            {{-- Bagian Kiri Header (Mobile Menu Toggle + Desktop Sidebar Toggle + Category Dropdown) --}}
            <div class="header-spacer-left space-x-4 flex items-center">

                {{-- Mobile Menu Toggle (Hanya tampil di mobile) --}}
                <button id="mobile-menu-toggle" class="md:hidden text-gray-600 focus:outline-none focus:text-gray-900">
                    <i class="fas fa-bars fa-lg"></i>
                </button>

                {{-- Tombol Toggle Sidebar untuk Desktop --}}
                <button id="desktop-sidebar-toggle" class="hidden md:block text-gray-600 focus:outline-none focus:text-gray-900">
                    <i class="fas fa-bars fa-xl"></i>
                </button>

                {{-- Category Dropdown (untuk Desktop dan Mobile Sidebar) --}}
                <div class="relative hidden md:block">
                    <button id="category-dropdown-btn" class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors focus:outline-none">
                        <span id="category-button-text" class="truncate-text" title="{!! ucwords(str_replace('-',' ',$currentCategorySlug)) !!}">{!! ucwords(str_replace('-',' ',$currentCategorySlug)) !!}</span>
                        <i class="ml-2 fa fa-chevron-down text-xs"></i>
                    </button>

                    <div id="category-dropdown-menu" class="header-dropdown-menu">
                        {{-- Iterasi kategori untuk dropdown --}}
                        @foreach ($categories as $name => $slug)
                            @php
                                $isActive = ($currentCategorySlug === $slug);
                            @endphp
                            <div class="flex items-center justify-between">
                                <a href="{{ route('docs', ['category' => $slug]) }}"
                                   class="flex-grow px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-gray-100 text-gray-800' : 'text-gray-700 hover:bg-gray-50' }}"
                                   data-category-key="{{ $slug }}"
                                   data-category-name="{{ $name }}"
                                   title="{!! ucwords(str_replace('-',' ',$name)) !!}">
                                    <span class="truncate-text">{!! ucwords(str_replace('-',' ',$name)) !!}</span>
                                </a>
                                @if ($userRole === 'admin')
                                    <div class="flex-shrink-0 flex items-center space-x-1 pr-2">
                                        <button type="button" data-action="edit-category" data-slug="{{ $slug }}" data-name="{{ $name }}" title="Edit Kategori" class="text-blue-500 hover:text-blue-700 p-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($slug !== 'epesantren')
                                            <button type="button" data-action="delete-category" data-slug="{{ $slug }}" data-name="{{ $name }}" title="Hapus Kategori" class="text-red-500 hover:text-red-700 p-1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        @if ($userRole === 'admin')
                            <div class="border-t border-gray-200 my-1"></div>
                            <button type="button" data-action="add-category" class="text-blue-600 hover:underline text-sm p-3">
                                + Tambah Kategori
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bagian Tengah Header (untuk Search Button) --}}
            <div class="search-button-wrapper">
                <button id="open-search-modal-btn-header" class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                    <span class="flex items-center space-x-2">
                        <i id="search-icon" class="fa fa-search text-gray-400"></i>
                        <span class="text-search">Cari menu & konten...</span>
                    </span>
                </button>
            </div>

            {{-- Bagian Kanan Header (Login/Logout) --}}
            <div class="relative hidden md:flex header-spacer-right space-x-4 items-center">
                @auth
                    <span class="user-role-badge">
                        Selamat Datang:
                        <span class="role-text">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn" id="logout-btn">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition">Log In</a>
                @endauth
            </div>
        </div>
    </header>
