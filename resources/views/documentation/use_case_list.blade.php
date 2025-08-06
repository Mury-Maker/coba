{{-- resources/views/documentation/use_case_list.blade.php --}}
<h1 id="main-content-title" class="text-2xl font-bold"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>
<div class="max-w-7xl mx-auto px-2 sm:px-2 lg:px-2">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="judul-halaman">
            @yield('action-buttons')
        </div>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Daftar Use Case</h2>

        @auth
            @if(auth()->user()->role === 'admin')
                <div class="mb-4">
                    <button id="addUseCaseBtn"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white mr-1 text-sm font-medium rounded-md shadow-sm transition"
                        data-menu-id="{{ $menu_id }}">
                        <i class="fa fa-plus-circle mr-2"></i>Tambah Data
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white mr-1 text-sm font-medium rounded-md shadow transition">
                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-600 text-white text-sm font-medium rounded-md shadow transition">
                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF only Usecase
                    </button>
                </div>
            @endif
        @endauth

        <div class="overflow-x-auto min-h-[500px] max-h-[80vh]">
            <table class="min-w-full bg-white border border-gray-300 text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-r border-b">No</th>
                        <th class="py-2 px-4 border-r border-b">Nama Proses</th>
                        <th class="py-2 px-4 border-r border-b">Aktor</th>
                        <th class="py-2 px-4 border-r border-b">Kondisi Awal</th>
                        <th class="py-2 px-4 border-r border-b">Kondisi Akhir</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody id="useCaseListTableBody">
                    @forelse($useCases as $useCase)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-2 px-4 border-r border-b">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4 border-r border-b">{{ $useCase->nama_proses }}</td>
                            <td class="py-2 px-4 border-r border-b">{{ $useCase->aktor }}</td>
                            <td class="py-2 px-4 border-r border-b">{!! $useCase->kondisi_awal !!}</td>
                            <td class="py-2 px-4 border-r border-b">{!! $useCase->kondisi_akhir !!}</td>
                            <td class="py-2 px-4 border-b text-center align-middle w-36 max-w-[9rem]">
                                @auth
                                    @if(auth()->user()->role === 'admin')
                                        {{-- Admin: pakai dropdown --}}
                                        <div class="relative inline-block text-left" id="dropdown-wrapper-{{ $useCase->id }}">
                                            <button onclick="toggleDropdown({{ $useCase->id }})"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded-md min-w-[100px] w-full flex items-center justify-between gap-2 overflow-hidden text-ellipsis whitespace-nowrap">
                                                Pilih Aksi
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            </button>
                                            <div id="dropdown-menu-{{ $useCase->id }}"
                                                class="hidden absolute z-10 mt-2 w-[105px] origin-top-right rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none text-sm text-left">
                                                <ul class="py-1">
                                                    <li>
                                                        <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}"
                                                            class="block px-4 py-2 text-green-600 hover:bg-gray-100">
                                                            <i class="fas fa-eye mr-2"></i> Detail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="block w-full text-left px-4 py-2 text-yellow-500 hover:bg-gray-100 edit-usecase-btn"
                                                            data-id="{{ $useCase->id }}" data-menu-id="{{ $menu_id }}">
                                                            <i class="fas fa-edit mr-2"></i> Edit
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 delete-usecase-btn"
                                                            data-id="{{ $useCase->id }}" data-nama="{{ $useCase->nama_proses }}">
                                                            <i class="fas fa-trash-alt mr-2"></i> Hapus
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Anggota: hanya icon detail --}}
                                        <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}"
                                            class="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm"
                                            title="Lihat Detail">
                                            <span>Lihat</span>
                                            <i class="fas fa-eye ml-2"></i>
                                        </a>
                                    @endif
                                @endauth
                            </td>                                                                                                                         
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">Tidak ada tindakan (use case) yang didokumentasikan untuk menu ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Fungsi JS sederhana untuk dropdown di tabel
    function toggleDropdown(id) {
        const dropdown = document.getElementById('dropdown-menu-' + id);
        dropdown.classList.toggle('hidden');

        // Tutup dropdown lain jika ada
        document.querySelectorAll('.relative .absolute').forEach(menu => { // Target semua absolute dropdowns
            if (menu.id !== 'dropdown-menu-' + id) {
                menu.classList.add('hidden');
            }
        });
    }

    // Klik di luar dropdown menutupnya
    document.addEventListener('click', function (e) {
        document.querySelectorAll('[id^="dropdown-wrapper-"]').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                const menu = wrapper.querySelector('[id^="dropdown-menu-"]');
                if (menu) menu.classList.add('hidden');
            }
        });
    });
</script>
