@section('action-buttons')
    <a href="{{ route('docs.use_case_detail', [
        'category' => $currentCategory,
        'page' => Str::slug($selectedNavItem->menu_nama),
        'useCaseSlug' => Str::slug($parentUseCase->nama_proses)
    ]) }}" class="btn btn-secondary ml-auto">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Aksi
    </a>
@endsection

<div class="prose max-w-none">
    <h2 class="text-2xl font-bold mb-4">Detail Data Report</h2>
    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <tbody>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID Report:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $reportData->id_report ?? 'N/A' }}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Aktor:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $reportData->aktor ?? 'N/A' }}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Nama Report:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $reportData->nama_report ?? 'N/A' }}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Keterangan:</td>
                    <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">{!! $reportData->keterangan ?? '<span class="text-gray-500 italic">N/A</span>' !!}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Bagian Dokumen Report --}}
    <div class="md:col-span-2 mt-8">
        <p class="font-semibold text-gray-700">Dokumen Report:</p>
        {{-- PERUBAHAN UTAMA: Ganti ul dengan div untuk menghindari masalah list-style --}}
        <div class="mt-2 space-y-2">
            @forelse($reportData->documents as $document)
                @php
                    // Dapatkan ekstensi file dari nama file
                    $extension = pathinfo($document->filename, PATHINFO_EXTENSION);
                    $iconClass = 'fas fa-file-alt text-gray-400'; // Ikon default

                    // Tentukan ikon dan warna berdasarkan ekstensi
                    switch (strtolower($extension)) {
                        case 'pdf':
                            $iconClass = 'fas fa-file-pdf text-red-500';
                            break;
                        case 'doc':
                        case 'docx':
                            $iconClass = 'fas fa-file-word text-blue-500';
                            break;
                        case 'xls':
                        case 'xlsx':
                            $iconClass = 'fas fa-file-excel text-green-500';
                            break;
                        default:
                            $iconClass = 'fas fa-file-alt text-gray-400';
                            break;
                    }
                @endphp
                {{-- Ganti li dengan div --}}
                <div>
                    <a href="{{ asset($document->path) }}" target="_blank"
                    class="flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200">
                        
                        {{-- Bungkus ikon di dalam div untuk memastikan perataan --}}
                        <div class="w-5 flex-shrink-0 text-center">
                            <i class="{{ $iconClass }}"></i>
                        </div>

                        {{-- Nama file di dalam span --}}
                        <span class="ml-2 truncate">{{ $document->filename }}</span>
                    </a>
                </div>
            @empty
                <p class="text-gray-500 italic">Tidak ada dokumen Report.</p>
            @endforelse
        </div>
    </div>

    {{-- Bagian Gambar Report --}}
    <div class="md:col-span-2 mt-8">
        <p class="font-semibold text-gray-700">Gambar Report:</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mt-2 uat-image-gallery">
            @forelse($reportData->images as $index => $image)
                <a href="{{ asset($image->path) }}" class="block h-40 overflow-hidden rounded-lg border shadow-sm gallery-item"
                   data-full-src="{{ asset($image->path) }}"
                   data-caption="{{ $image->filename }}"
                   data-gallery-index="{{ $index }}"
                   onclick="event.preventDefault(); window.populateAndOpenImageViewerFromHtml(this);">
                    <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}" class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-gray-500 italic">Tidak ada gambar Report.</p>
            @endforelse
        </div>
    </div>
</div>
