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
        <h2 class="text-2xl font-bold mb-4">Detail Data Database</h2>

        <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID Database:</td>
                        <td class="py-2 px-4 text-gray-900">{{ $databaseData->id_database ?? 'N/A' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Keterangan:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">{!! $databaseData->keterangan ?? '<span class="text-gray-500 italic">N/A</span>' !!}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Relasi:</td>
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">{!! $databaseData->relasi ?? '<span class="text-gray-500 italic">N/A</span>' !!}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Bagian Dokumen Database --}}
        <div class="md:col-span-2 mt-8">
            <p class="font-semibold text-gray-700">Dokumen Database:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                @forelse($databaseData->documents as $document)
                    <li>
                        <a href="{{ asset($document->path) }}" target="_blank" class="text-blue-600 hover:underline">
                            <i class="fas fa-file-alt mr-2"></i> {{ $document->filename }}
                        </a>
                    </li>
                @empty
                    <p class="text-gray-500 italic">Tidak ada dokumen Database.</p>
                @endforelse
            </ul>
        </div>

        {{-- Bagian Gambar Database --}}
        <div class="md:col-span-2 mt-8">
            <p class="font-semibold text-gray-700">Gambar Database:</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mt-2 uat-image-gallery">
                @forelse($databaseData->images as $index => $image)
                    <a href="{{ asset($image->path) }}" class="block h-40 overflow-hidden rounded-lg border shadow-sm gallery-item"
                       data-full-src="{{ asset($image->path) }}"
                       data-caption="{{ $image->filename }}"
                       data-gallery-index="{{ $index }}"
                       onclick="event.preventDefault(); window.populateAndOpenImageViewerFromHtml(this);">
                        <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}" class="w-full h-full object-cover">
                    </a>
                @empty
                    <p class="text-gray-500 italic">Tidak ada gambar Database.</p>
                @endforelse
            </div>
        </div>
    </div>
