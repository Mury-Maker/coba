@section('action-buttons')
    {{-- Tombol-tombol di sini jika diperlukan --}}
@endsection

@php
    $hasUseCaseData = $singleUseCase && $singleUseCase->id;
@endphp

{{-- Container utama untuk konten detail aksi dan report --}}
<div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-gray-200">
    <div id="use-case-content-area">
        {{-- Bagian Detail Aksi --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Detail Use Case</h2>
            @auth
                @if(auth()->user()->role === 'admin')
                    <button id="editSingleUseCaseBtn"
                        class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-600 text-black text-sm font-medium rounded-md shadow-sm transition"
                        data-usecase-id="{{ $singleUseCase->id }}">
                        <i class="fas fa-edit mr-2"></i> Edit Detail Aksi
                    </button>
                @endif
            @endauth
        </div>

        {{-- Struktur Tabel untuk Detail Aksi --}}
        <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID Usecase:</td>
                        <td class="py-2 px-4 text-gray-900">UC - {{ $singleUseCase->id ?? 'N/A' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Nama Proses:</td>
                        <td class="py-2 px-4 text-gray-900">{{ $singleUseCase->nama_proses ?? 'N/A' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Deskripsi Aksi:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->deskripsi_aksi ?? '<span class="text-gray-500 italic">Tidak ada deskripsi.</span>' !!}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Aktor:</td>
                        <td class="py-2 px-4 text-gray-900">{{ $singleUseCase->aktor ?? 'N/A' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Tujuan:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->tujuan ?? '<span class="text-gray-500 italic">Tidak ada tujuan.</span>' !!}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Kondisi Awal:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->kondisi_awal ?? '<span class="text-gray-500 italic">Tidak ada kondisi awal.</span>' !!}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Kondisi Akhir:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->kondisi_akhir ?? '<span class="text-gray-500 italic">Tidak ada kondisi akhir.</span>' !!}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Aksi Aktor:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->aksi_aktor ?? '<span class="text-gray-500 italic">Tidak ada aksi aktor.</span>' !!}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Reaksi Sistem:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                            {!! $singleUseCase->reaksi_sistem ?? '<span class="text-gray-500 italic">Tidak ada reaksi sistem.</span>' !!}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-start items-center gap-3 mt-4 mb-8">
            <a href="{{ route('usecase.print.single', $singleUseCase->id) }}" target="_blank"
                class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                <i class="fas fa-print mr-2"></i> Only Data Usecase
            </a>
            <button class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow transition">
                <i class="fas fa-print mr-2"></i> Semua Data (All table)
            </button>
        </div>

        @if($hasUseCaseData)
            {{-- Report Data Section --}}
            <hr class="my-6 border-gray-200">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Report Data</h2>
            <div id="content-Report" class="mb-8">
                <div class="flex items-center justify-between flex-wrap mb-4 gap-2">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="flex justify-start items-center gap-x-2">
                                <button id="addReportDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah
                                </button>
                                <a href="{{ route('report.cetak', $singleUseCase->id) }}" target="_blank">
                                    <button class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </a>
                            </div>
                        @endif
                    @endauth
                    <form id="reportSearchForm" class="flex items-center gap-2" method="GET" action="{{ url()->current() }}">
                        <label for="report_per_page" class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</label>
                        <select name="report_per_page" id="report_per_page" class="form-select border border-black rounded-md shadow-sm text-sm">
                            <option value="5" @selected(request('report_per_page', 5) == 5)>5</option>
                            <option value="10" @selected(request('report_per_page', 5) == 10)>10</option>
                            <option value="25" @selected(request('report_per_page', 5) == 25)>25</option>
                        </select>
                        <input type="text"
                               name="report_search"
                               id="reportSearchInput"
                               placeholder="Cari Report..."
                               value="{{ request('report_search') }}"
                               class="form-input border-2 border-gray-500 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition bg-gray-100"/>
                        <input type="hidden" name="database_per_page" value="{{ request('database_per_page', 5) }}">
                        <input type="hidden" name="database_search" value="{{ request('database_search') }}">
                        <input type="hidden" name="uat_per_page" value="{{ request('uat_per_page', 5) }}">
                        <input type="hidden" name="uat_search" value="{{ request('uat_search') }}">
                    </form>
                </div>
                <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm" id="report-table-container">
                    <table class="min-w-full bg-white divide-y divide-gray-200">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktor</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Report</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reportDataTableBody" class="divide-y divide-gray-200">
                            @forelse($reportDataPaginated as $report)
                                <tr data-id="{{ $report->id_report }}" class="hover:bg-gray-50">
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ ($reportDataPaginated->currentPage() - 1) * $reportDataPaginated->perPage() + $loop->iteration }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report->aktor }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report->nama_report }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $report->keterangan !!}</td>
                                    <td class="py-2 px-4 text-center text-sm font-medium w-[150px]">
                                        @auth
                                            @if(auth()->user()->role === 'admin')
                                                <a href="{{ route('docs.use_case_report_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'reportId' => $report->id_report
                                                ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="edit-report-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $report->id_report }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="delete-report-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $report->id_report }}" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('docs.use_case_report_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'reportId' => $report->id_report
                                                ]) }}" class="btn-action btn-action--full bg-green-500 text-white" title="Detail">
                                                    <span>Lihat</span>
                                                    <i class="fas fa-eye ml-2"></i>
                                                </a>
                                            @endif
                                        @endauth
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data Report.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="report-pagination-links-container" class="mt-4">
                    {{ $reportDataPaginated->links() }}
                </div>
            </div>

            {{-- Database Data Section --}}
            <hr class="my-6 border-gray-200">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Database Data</h2>
            <div id="content-Database" class="mb-8">
                <div class="flex items-center justify-between flex-wrap mb-4 gap-2">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="flex justify-start items-center gap-x-2">
                                <button id="addDatabaseDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah
                                </button>
                                <a href="{{ route('database.cetak', $singleUseCase->id) }}" target="_blank">
                                    <button class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </a>
                            </div>
                        @endif
                    @endauth
                    <form id="databaseSearchForm" class="flex items-center gap-2" method="GET" action="{{ url()->current() }}">
                        <label for="database_per_page" class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</label>
                        <select name="database_per_page" id="database_per_page" class="form-select border border-black rounded-md shadow-sm text-sm">
                            <option value="5" @selected(request('database_per_page', 5) == 5)>5</option>
                            <option value="10" @selected(request('database_per_page', 5) == 10)>10</option>
                            <option value="25" @selected(request('database_per_page', 5) == 25)>25</option>
                        </select>
                        <input type="text"
                               name="database_search"
                               id="databaseSearchInput"
                               placeholder="Cari Database..."
                               value="{{ request('database_search') }}"
                               class="form-input border-2 border-gray-500 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition bg-gray-100"/>
                        <input type="hidden" name="report_per_page" value="{{ request('report_per_page', 5) }}">
                        <input type="hidden" name="report_search" value="{{ request('report_search') }}">
                        <input type="hidden" name="uat_per_page" value="{{ request('uat_per_page', 5) }}">
                        <input type="hidden" name="uat_search" value="{{ request('uat_search') }}">
                    </form>
                </div>
                <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm" id="database-table-container">
                    <table class="min-w-full bg-white divide-y divide-gray-200">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relasi</th>
                                <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="databaseDataTableBody" class="divide-y divide-gray-200">
                            @forelse($databaseDataPaginated as $database)
                                <tr data-id="{{ $database->id_database }}" class="hover:bg-gray-50">
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ ($databaseDataPaginated->currentPage() - 1) * $databaseDataPaginated->perPage() + $loop->iteration }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $database->keterangan !!}</td>
                                    <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $database->relasi !!}</td>
                                    <td class="py-2 px-4 text-center text-sm font-medium w-[150px]">
                                        @auth
                                            @if(auth()->user()->role === 'admin')
                                                <a href="{{ route('docs.use_case_database_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'databaseId' => $database->id_database
                                                ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="edit-database-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $database->id_database }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="delete-database-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $database->id_database }}" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('docs.use_case_database_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'databaseId' => $database->id_database
                                                ]) }}" class="btn-action btn-action--full bg-green-500 text-white" title="Detail">
                                                    <span>Lihat</span>
                                                    <i class="fas fa-eye ml-2"></i>
                                                </a>
                                            @endif
                                        @endauth
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data Database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="database-pagination-links-container" class="mt-4">
                    {{ $databaseDataPaginated->links() }}
                </div>
            </div>

            {{-- UAT Data Section --}}
            <hr class="my-6 border-gray-200">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">UAT Data</h2>
            <div id="content-UAT" class="mb-8">
                <div class="flex items-center justify-between flex-wrap mb-4 gap-2">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="flex justify-start items-center gap-x-2">
                                <button id="addUatDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                    <i class="fa fa-plus-circle mr-2"></i>Tambah
                                </button>
                                <a href="{{ route('uat.cetak', $singleUseCase->id) }}" target="_blank">
                                    <button class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                    </button>
                                </a>
                            </div>
                        @endif
                    @endauth
                    <form id="uatSearchForm" class="flex items-center gap-2" method="GET" action="{{ url()->current() }}">
                        <label for="uat_per_page" class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</label>
                        <select name="uat_per_page" id="uat_per_page" class="form-select border border-black rounded-md shadow-sm text-sm">
                            <option value="5" @selected(request('uat_per_page', 5) == 5)>5</option>
                            <option value="10" @selected(request('uat_per_page', 5) == 10)>10</option>
                            <option value="25" @selected(request('uat_per_page', 5) == 25)>25</option>
                        </select>
                        <input type="text"
                               name="uat_search"
                               id="uatSearchInput"
                               placeholder="Cari UAT..."
                               value="{{ request('uat_search') }}"
                               class="form-input border-2 border-gray-500 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition bg-gray-100"/>
                        <input type="hidden" name="report_per_page" value="{{ request('report_per_page', 5) }}">
                        <input type="hidden" name="report_search" value="{{ request('report_search') }}">
                        <input type="hidden" name="database_per_page" value="{{ request('database_per_page', 5) }}">
                        <input type="hidden" name="database_search" value="{{ request('database_search') }}">
                    </form>
                </div>
                <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm" id="uat-table-container">
                    <table class="min-w-full bg-white divide-y divide-gray-200">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Proses Usecase</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="uatDataTableBody" class="divide-y divide-gray-200">
                            @forelse($uatDataPaginated as $uat)
                                <tr data-id="{{ $uat->id_uat }}" class="hover:bg-gray-50">
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ ($uatDataPaginated->currentPage() - 1) * $uatDataPaginated->perPage() + $loop->iteration }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $uat->nama_proses_usecase }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $uat->keterangan_uat !!}</td>
                                    <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">
                                        @php
                                            $badgeClass = match($uat->status_uat) {
                                                'Passed' => 'bg-green-100 text-green-800',
                                                'Failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                            {{ $uat->status_uat }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 text-center text-sm font-medium w-[150px]">
                                        @auth
                                            @if(auth()->user()->role === 'admin')
                                                <a href="{{ route('docs.use_case_uat_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'uatId' => $uat->id_uat
                                                ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="edit-uat-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $uat->id_uat }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="delete-uat-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $uat->id_uat }}" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('docs.use_case_uat_detail_page', [
                                                    'category' => $currentCategory,
                                                    'page' => Str::slug($selectedNavItem->menu_nama),
                                                    'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                    'uatId' => $uat->id_uat
                                                ]) }}" class="btn-action btn-action--full bg-green-500 text-white" title="Detail">
                                                    <span>Lihat</span>
                                                    <i class="fas fa-eye ml-2"></i>
                                                </a>
                                            @endif
                                        @endauth
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-4 text-center text-gray-500 italic">Tidak ada data UAT.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="uat-pagination-links-container" class="mt-4">
                    {{ $uatDataPaginated->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk mengambil dan memuat ulang data tabel tertentu
        function fetchData(containerId, url) {
            const container = document.getElementById(containerId);
            const wrapper = container.parentElement;

            // Tampilkan efek loading
            wrapper.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Ambil elemen tabel dan paginasi baru
                const newTableContainer = doc.getElementById(containerId);
                const newPaginationContainer = doc.getElementById(`${containerId.split('-')[0]}-pagination-links-container`);

                if (newTableContainer) {
                    container.innerHTML = newTableContainer.innerHTML;
                }
                if (newPaginationContainer) {
                    document.getElementById(`${containerId.split('-')[0]}-pagination-links-container`).innerHTML = newPaginationContainer.innerHTML;
                }

                // Pasang kembali event listener ke elemen-elemen yang baru dimuat
                attachEventListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat data: ' + error.message);
            })
            .finally(() => {
                wrapper.style.opacity = '1';
            });
        }

        // Fungsi untuk memasang event listener pada elemen-elemen tabel
        function attachEventListeners() {
            // Report
            const reportPerPageSelect = document.getElementById('report_per_page');
            const reportSearchInput = document.getElementById('reportSearchInput');
            const reportPagination = document.getElementById('report-pagination-links-container');

            if (reportPerPageSelect) {
                reportPerPageSelect.onchange = function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('report_per_page', this.value);
                    url.searchParams.set('report_search', reportSearchInput.value);
                    url.searchParams.delete('report_page');
                    fetchData('report-table-container', url.toString());
                };
            }

            let reportSearchTimeout = null;
            if (reportSearchInput) {
                reportSearchInput.oninput = function() {
                    clearTimeout(reportSearchTimeout);
                    reportSearchTimeout = setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('report_per_page', reportPerPageSelect.value);
                        url.searchParams.set('report_search', this.value);
                        url.searchParams.delete('report_page');
                        fetchData('report-table-container', url.toString());
                    }, 500);
                };
            }

            if (reportPagination) {
                reportPagination.querySelectorAll('a').forEach(link => {
                    link.onclick = function(e) {
                        e.preventDefault();
                        fetchData('report-table-container', this.href);
                    };
                });
            }

            // Database
            const databasePerPageSelect = document.getElementById('database_per_page');
            const databaseSearchInput = document.getElementById('databaseSearchInput');
            const databasePagination = document.getElementById('database-pagination-links-container');

            if (databasePerPageSelect) {
                databasePerPageSelect.onchange = function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('database_per_page', this.value);
                    url.searchParams.set('database_search', databaseSearchInput.value);
                    url.searchParams.delete('database_page');
                    fetchData('database-table-container', url.toString());
                };
            }

            let databaseSearchTimeout = null;
            if (databaseSearchInput) {
                databaseSearchInput.oninput = function() {
                    clearTimeout(databaseSearchTimeout);
                    databaseSearchTimeout = setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('database_per_page', databasePerPageSelect.value);
                        url.searchParams.set('database_search', this.value);
                        url.searchParams.delete('database_page');
                        fetchData('database-table-container', url.toString());
                    }, 500);
                };
            }

            if (databasePagination) {
                databasePagination.querySelectorAll('a').forEach(link => {
                    link.onclick = function(e) {
                        e.preventDefault();
                        fetchData('database-table-container', this.href);
                    };
                });
            }

            // UAT
            const uatPerPageSelect = document.getElementById('uat_per_page');
            const uatSearchInput = document.getElementById('uatSearchInput');
            const uatPagination = document.getElementById('uat-pagination-links-container');

            if (uatPerPageSelect) {
                uatPerPageSelect.onchange = function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('uat_per_page', this.value);
                    url.searchParams.set('uat_search', uatSearchInput.value);
                    url.searchParams.delete('uat_page');
                    fetchData('uat-table-container', url.toString());
                };
            }

            let uatSearchTimeout = null;
            if (uatSearchInput) {
                uatSearchInput.oninput = function() {
                    clearTimeout(uatSearchTimeout);
                    uatSearchTimeout = setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('uat_per_page', uatPerPageSelect.value);
                        url.searchParams.set('uat_search', this.value);
                        url.searchParams.delete('uat_page');
                        fetchData('uat-table-container', url.toString());
                    }, 500);
                };
            }

            if (uatPagination) {
                uatPagination.querySelectorAll('a').forEach(link => {
                    link.onclick = function(e) {
                        e.preventDefault();
                        fetchData('uat-table-container', this.href);
                    };
                });
            }
        }

        // Panggil saat DOM selesai dimuat untuk pertama kalinya
        attachEventListeners();
    });
</script>
