@section('action-buttons')

@endsection

    @php
        $hasUseCaseData = $singleUseCase && $singleUseCase->id;
    @endphp

    {{-- Container utama untuk konten detail aksi dan report --}}
    <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-gray-200">
        <div id="use-case-content-area">
            {{-- Bagian Detail Aksi --}}
            {{-- Gabungkan judul dan tombol edit dalam satu div flex --}}
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Detail UseCase</h2>
                {{-- Tombol Edit Detail Aksi (di kanan judul halaman) --}}
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
                {{-- Tombol pertama: Abu-abu --}}
                <a href="{{ route('usecase.print.single', $singleUseCase->id) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                     <i class="fas fa-print mr-2"></i> Only Data Usecase
                 </a>                                               
                {{-- Tombol kedua: Ungu --}}
                <button class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow transition">
                    <i class="fas fa-print mr-2"></i> Semua Data (All table)
                </button>
            </div>

            {{-- Bagian Report, Database, UAT (berurutan) --}}
            @if($hasUseCaseData) {{-- Hanya tampilkan jika ada data use case --}}

                {{-- Report Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Report Data</h2>
                <div id="content-Report" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2">
                                    <button id="addReportDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <a href="{{ route('report.cetak', $singleUseCase->id) }}" target="_blank">
                                        <button
                                            class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                            <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                        </button>
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
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
                                @forelse($singleUseCase->reportData as $report)
                                    <tr data-id="{{ $report->id_report }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report->aktor }}</td>
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $report->nama_report }}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $report->keterangan !!}</td>
                                        <td class="py-2 px-4 text-center text-sm font-medium w-[150px]">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_report_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem->menu_nama),
                                                        'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                        'reportId' => $report->id_report
                                                    ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-report-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $report->id_report }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-report-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $report->id_report }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
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
                </div>

                {{-- Database Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Database Data</h2>
                <div id="content-Database" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2">
                                    <button id="addDatabaseDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <a href="{{ route('database.cetak', $singleUseCase->id) }}" target="_blank">
                                        <button
                                            class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                            <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                        </button>
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
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
                                @forelse($singleUseCase->databaseData as $database)
                                    <tr data-id="{{ $database->id_database }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $database->keterangan !!}</td>
                                        <td class="py-2 px-4 text-sm text-gray-900 prose max-w-prose">{!! $database->relasi !!}</td>
                                        <td class="py-2 px-4 text-center text-sm font-medium w-[150px]">
                                            @auth
                                                @if(auth()->user()->role === 'admin')
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_database_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem->menu_nama),
                                                        'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                        'databaseId' => $database->id_database
                                                    ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-database-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $database->id_database }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-database-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $database->id_database }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
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
                </div>

                {{-- UAT Data Section --}}
                <hr class="my-6 border-gray-200">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">UAT Data</h2>
                <div id="content-UAT" class="mb-8">
                    <div class="flex flex-col items-start mb-4">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="flex justify-start items-center gap-x-2 mt-1 mb-2">
                                    <button id="addUatDataBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow transition">
                                        <i class="fa fa-plus-circle mr-2"></i>Tambah
                                    </button>
                                    <a href="{{ route('uat.cetak', $singleUseCase->id) }}" target="_blank">
                                        <button
                                            class="inline-flex items-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded-md shadow transition">
                                            <i class="fas fa-print mr-2"></i> Print / Cetak PDF
                                        </button>
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm">
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
                                @forelse($singleUseCase->uatData as $uat)
                                    <tr data-id="{{ $uat->id_uat }}" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
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
                                                    {{-- Detail (Mata): Biru dengan ikon putih --}}
                                                    <a href="{{ route('docs.use_case_uat_detail_page', [
                                                        'category' => $currentCategory,
                                                        'page' => Str::slug($selectedNavItem->menu_nama),
                                                        'useCaseSlug' => Str::slug($singleUseCase->nama_proses),
                                                        'uatId' => $uat->id_uat
                                                    ]) }}" class="btn-action btn-action--icon bg-green-500 text-white" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Edit (Pensil): Kuning dengan ikon hitam --}}
                                                    <button class="edit-uat-btn btn-action btn-action--icon bg-yellow-400 text-black" data-id="{{ $uat->id_uat }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    {{-- Hapus (Sampah): Merah dengan ikon putih --}}
                                                    <button class="delete-uat-btn btn-action btn-action--icon bg-red-600 text-white" data-id="{{ $uat->id_uat }}" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    {{-- Non-admin hanya tombol detail --}}
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
                </div>
            @endif
        </div>
    </div>