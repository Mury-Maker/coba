@foreach($items as $item)
    <li class="my-0.5 group">
        {{-- Container utama item menu --}}
        <div class="
            flex items-center justify-between py-1.5 rounded-md
            transition-all duration-200 ease-in-out
            relative group-hover:z-10
            sidebar-menu-item-wrapper

            {{-- Efek hover kotak yang baru --}}
            hover:bg-gray-100
            hover:shadow-md
            hover:scale-[1.02]
            transform-gpu

            {{-- Tambahkan shadow dan scale saat terpilih --}}
            @if(isset($selectedNavItemId) && $selectedNavItemId == $item->menu_id)
                bg-blue-100 font-semibold shadow-md scale-[1.02]
            @endif
        ">

            @php
                $linkClasses = 'flex items-center space-x-2 flex-grow min-w-0';

                // Penyesuaian indentasi berdasarkan level
                $indentation = ($level + 1) * 3;
                $linkClasses .= ' pl-' . $indentation;

                // Penyesuaian ukuran dan ketebalan font berdasarkan level
                if ($level == 0) { // Parent Menu
                    $linkClasses .= ' text-sm font-semibold';
                } elseif ($level == 1) { // Child Menu
                    $linkClasses .= ' text-xs font-normal';
                } else { // Grand-child Menu ke bawah
                    $linkClasses .= ' text-[0.7rem] font-normal';
                }

                $isFolder = $item->menu_status == 0;
                $menuUrl = route('docs', ['category' => $currentCategorySlug, 'page' => $item->menu_link]);
            @endphp

            @if($isFolder)
                {{-- Jika ini adalah parent/folder menu, gunakan div yang bisa diklik untuk toggle submenu --}}
                <div class="{{ $linkClasses }} cursor-pointer" data-toggle="submenu-{{ $item->menu_id }}" aria-expanded="false" aria-controls="submenu-{{ $item->menu_id }}">
                    {{-- Ikon --}}
                    <div class="w-4 flex-shrink-0 text-center">
                        @if($item->menu_icon)
                            <i class="{{ $item->menu_icon }}"></i>
                        @else
                            <span class="w-1"></span> {{-- Placeholder jika tidak ada ikon --}}
                        @endif
                    </div>
                    {{-- Nama Menu --}}
                    <span class="ml-2 flex-grow min-w-0 truncate">{{ $item->menu_nama }}</span>
                </div>
            @else
                {{-- Jika ini menu dengan konten (use case list), gunakan a tag biasa --}}
                <a href="{{ $menuUrl }}" class="{{ $linkClasses }}" style="min-width: 0;">
                    {{-- Ikon --}}
                    <div class="w-4 flex-shrink-0 text-center">
                        @if($item->menu_icon)
                            <i class="{{ $item->menu_icon }}"></i>
                        @endif
                    </div>
                    {{-- Nama Menu --}}
                    <span class="ml-2 flex-grow min-w-0 truncate">{{ $item->menu_nama }}</span>
                </a>
            @endif

            {{-- Kontainer KANAN: Tombol Admin dan Panah Dropdown --}}
            <div class="flex items-center flex-shrink-0">

                {{-- Tombol Admin (Add Child, Edit, Delete) --}}
                @if($editorMode)
                    <div class="flex items-center space-x-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 mr-1">
                        @if ($item->menu_status == 0) {{-- Hanya folder yang bisa punya sub-menu --}}
                            {{-- PERBAIKAN DI SINI: Tambah Sub Menu --}}
                            <button
                                type="button"
                                data-action="add-child-menu"
                                data-parent-id="{{ $item->menu_id }}"
                                class="text-blue-500 hover:text-blue-700 p-1"
                                title="Tambah Sub Menu"
                                aria-label="Tambah Sub Menu">
                                <i class="fa-solid fa-plus-circle"></i>
                            </button>
                        @endif
                        @if (str_contains($item->menu_link, 'daftar-tabel') != true )
                        <button
                            data-action="edit-menu"
                            data-menu-id="{{ $item->menu_id }}"
                            class="text-yellow-500 hover:text-yellow-700 p-1"
                            title="Edit Menu"
                            aria-label="Edit Menu">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button
                            data-action="delete-menu"
                            data-menu-id="{{ $item->menu_id }}"
                            data-menu-nama="{{ $item->menu_nama }}"
                            class="text-red-500 hover:text-red-700 p-1"
                            title="Hapus Menu"
                            aria-label="Hapus Menu {{ $item->menu_nama }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        @endif
                    </div>
                @endif

                {{-- Panah Dropdown --}}
                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center pr-3 {{ !$editorMode ? 'ml-auto' : '' }}">
                    @if($isFolder)
                        <button
                            type="button"
                            class="menu-arrow-icon text-gray-500 p-2"
                            data-toggle="submenu-{{ $item->menu_id }}"
                            aria-expanded="false"
                            aria-controls="submenu-{{ $item->menu_id }}"
                            aria-label="Toggle submenu for {{ $item->menu_nama }}">
                            <i class="fas fa-chevron-left transition-transform duration-200"></i>
                        </button>
                    @else
                        <span class="p-2"></span> {{-- Placeholder kosong agar tinggi tetap sama --}}
                    @endif
                </div>
            </div>
        </div>

        @if($isFolder)
            {{-- Submenu Container (akan berisi item anak) --}}
            <div id="submenu-{{ $item->menu_id }}" class="submenu-container mt-1 border-l border-gray-200" role="region" aria-label="Submenu for {{ $item->menu_nama }}">
                <ul>
                    @include('partials._menu_item', [
                        'items' => $item->children,
                        'editorMode' => $editorMode,
                        'selectedNavItemId' => $selectedNavItemId,
                        'currentCategorySlug' => $currentCategorySlug,
                        'level' => $level + 1
                    ])
                </ul>
            </div>
        @endif
    </li>
@endforeach
