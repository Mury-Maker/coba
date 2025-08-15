{{-- resources/views/documentation/use_case_list.blade.php --}}
<h1 id="main-content-title" class="text-3xl font-bold mb-2"> {!! ucfirst(Str::headline($currentPage)) !!}</h1>

<div class="judul-halaman border-b-2 border-gray-300">
    @yield('action-buttons')
</div>

<h2 class="text-2xl font-bold mb-4 text-gray-800 mt-6">Daftar Use Case</h2>

@auth
    @if (auth()->user()->role === 'admin')
        <div class="mb-4">
            <button id="addUseCaseBtn"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white mr-1 text-sm font-medium rounded-md shadow-sm transition"
                data-menu-id="{{ $menu_id }}">
                <i class="fa fa-plus-circle mr-2"></i>Tambah Data
            </button>
            <a href="{{ route('usecase.cetak', $menu_id) }}" target="_blank">
                <button
                    class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                    <i class="fas fa-print mr-2"></i> Cetak Usecase PDF
                </button>
            </a>
            <a href="{{ route('usecase.cetak.lengkap', ['menu_id' => $menu_id]) }}"" target="_blank"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow transition">
                <i class="fas fa-print mr-2"></i> Semua Data (All table)
            </a>
        </div>

        {{-- Kontainer untuk kontrol pagination & search --}}
        <div class="flex items-center justify-between flex-wrap mb-4 gap-2">
            {{-- Per Page Selector --}}
            <form id="perPageForm" data-url="{{ route('docs', ['category' => $currentCategory, 'page' => $currentPage]) }}"
                class="flex items-center gap-2">
                <label for="per_page" class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</label>
                <select name="per_page" id="per_page" class="form-select border border-black rounded-md shadow-sm text-sm">
                    <option value="5" @selected($per_page == 5)>5</option>
                    <option value="10" @selected($per_page == 10)>10</option>
                    <option value="25" @selected($per_page == 25)>25</option>
                    <option value="50" @selected($per_page == 50)>50</option>
                </select>
                <label for="per_page" class="text-sm text-gray-600 whitespace-nowrap">Per halaman</label>
                <input type="hidden" name="search" id="hiddenSearch" value="{{ $search_term }}">
            </form>
            {{-- Search Form --}}
            <form id="searchForm" data-url="{{ route('docs', ['category' => $currentCategory, 'page' => $currentPage]) }}"
                method="GET" class="flex items-center">

                <input type="text" name="search" id="searchInput" placeholder="Cari..." value="{{ $search_term }}"
                    class="form-input border-2 border-gray-500 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition bg-gray-100" />
                <input type="hidden" name="per_page" value="{{ $per_page }}">
            </form>
        </div>
    @endif
@endauth

{{-- Kontainer utama yang akan diupdate oleh AJAX --}}
<div id="table-container">
    <div class=" min-h-full pb-4">
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
                        <td class="py-2 px-4 border-r border-b">
                            {{ ($useCases->currentPage() - 1) * $useCases->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 px-4 border-r border-b break-words whitespace-normal max-w-xs">
                            {{ $useCase->nama_proses }}
                        </td>
                        <td class="py-2 px-4 border-r border-b break-words whitespace-normal max-w-xs">
                            {{ $useCase->aktor }}
                        </td>
                        <td class="py-2 px-4 border-r border-b max-w-xs">
                            <div class="two-line-ellipsis">
                                {!! $useCase->kondisi_awal !!}
                            </div>
                        </td>
                        <td class="py-2 px-4 border-r border-b max-w-xs">
                            <div class="two-line-ellipsis">
                                {!! $useCase->kondisi_akhir !!}
                            </div>
                        </td>
                        <td class="py-2 px-4 border-b text-center align-middle w-36 max-w-[9rem]">
                            @auth
                                @if (auth()->user()->role === 'admin')
                                    {{-- Admin: pakai dropdown --}}
                                    <div class="relative inline-block text-left" id="dropdown-wrapper-{{ $useCase->id }}">
                                        <button onclick="toggleDropdown({{ $useCase->id }})"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded-md min-w-[100px] w-full flex items-center justify-between gap-2 overflow-hidden text-ellipsis whitespace-nowrap">
                                            Pilih Aksi
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </button>
                                        <div id="dropdown-menu-{{ $useCase->id }}"
                                            class="hidden absolute z-20 mt-2 w-[105px] origin-top-right rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none text-sm text-left">
                                            <ul class="py-1">
                                                <li>
                                                    <a href="{{ route('docs.use_case_detail', ['category' => $currentCategory, 'page' => Str::slug($selectedNavItem->menu_nama), 'useCaseSlug' => Str::slug($useCase->nama_proses)]) }}"
                                                        class="block px-4 py-2 text-green-600 hover:bg-gray-100">
                                                        <i class="fas fa-eye mr-2"></i> Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <button
                                                        class="block w-full text-left px-4 py-2 text-yellow-500 hover:bg-gray-100 edit-usecase-btn"
                                                        data-id="{{ $useCase->id }}" data-menu-id="{{ $menu_id }}">
                                                        <i class="fas fa-edit mr-2"></i> Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button
                                                        class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 delete-usecase-btn"
                                                        data-id="{{ $useCase->id }}"
                                                        data-nama="{{ $useCase->nama_proses }}">
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
                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">Tidak ada tindakan (use case)
                            yang didokumentasikan untuk menu ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- Kontainer Paginasi --}}
        <div id="pagination-links-container">
            @if ($useCases->lastPage() > 1)
                <div class="mt-4 flex justify-between items-center flex-wrap">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $useCases->firstItem() }} hingga {{ $useCases->lastItem() }} dari
                        {{ $useCases->total() }} hasil
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span class="relative inline-flex shadow-sm rounded-md">
                            {{-- Tombol Previous --}}
                            @if ($useCases->onFirstPage())
                                <span
                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $useCases->previousPageUrl() . '&per_page=' . $per_page . '&search=' . $search_term }}"
                                    rel="prev"
                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:text-gray-700 focus:z-10 focus:outline-none focus:border-blue-300 focus:ring-blue-200 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif

                            {{-- Link Halaman --}}
                            @foreach ($useCases->getUrlRange(1, $useCases->lastPage()) as $page => $url)
                                <a href="{{ $url . '&per_page=' . $per_page . '&search=' . $search_term }}"
                                    class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium {{ $page == $useCases->currentPage() ? 'text-white bg-blue-600 border-blue-600' : 'text-gray-700 bg-white border-gray-300 hover:text-blue-600' }} border">
                                    {{ $page }}
                                </a>
                            @endforeach

                            {{-- Tombol Next --}}
                            @if ($useCases->hasMorePages())
                                <a href="{{ $useCases->nextPageUrl() . '&per_page=' . $per_page . '&search=' . $search_term }}"
                                    rel="next"
                                    class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:text-gray-700 focus:z-10 focus:outline-none focus:border-blue-300 focus:ring-blue-200 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span
                                    class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Dropdown Script --}}
<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById('dropdown-menu-' + id);
        dropdown.classList.toggle('hidden');

        // Tutup dropdown lain jika ada
        document.querySelectorAll('.relative .absolute').forEach(menu => {
            if (menu.id !== 'dropdown-menu-' + id) {
                menu.classList.add('hidden');
            }
        });
    }

    document.addEventListener('click', function(e) {
        document.querySelectorAll('[id^="dropdown-wrapper-"]').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                const menu = wrapper.querySelector('[id^="dropdown-menu-"]');
                if (menu) menu.classList.add('hidden');
            }
        });
    });
</script>

{{-- resources/views/documentation/use_case_list.blade.php --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.getElementById('table-container');
        const perPageSelect = document.getElementById('per_page');
        const searchInput = document.getElementById('searchInput');

        let searchTimeout = null;

        function fetchData(url) {
            tableContainer.style.opacity = '0.5';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableContainer = doc.getElementById('table-container');

                    if (newTableContainer) {
                        tableContainer.innerHTML = newTableContainer.innerHTML;
                    }
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    tableContainer.style.opacity = '1';
                    // Panggil kembali listener untuk elemen baru
                    attachEventListeners();
                });
        }

        function attachEventListeners() {
            // Listener untuk select per_page
            const newPerPageSelect = document.getElementById('per_page');
            if (newPerPageSelect) {
                newPerPageSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.set('search', searchInput.value);
                    fetchData(url.toString());
                });
            }

            // Listener untuk pagination links
            const paginationLinksContainer = document.getElementById('pagination-links-container');
            if (paginationLinksContainer) {
                paginationLinksContainer.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = this.href;
                        fetchData(url);
                    });
                });
            }
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', perPageSelect.value);
                url.searchParams.set('search', searchInput.value);
                fetchData(url.toString());
            }, 500);
        });

        attachEventListeners();
    });
</script>
