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
    <h2 class="text-2xl font-bold mb-4">Detail Data UAT</h2>

    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <tbody>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID UAT:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $uatData->id_uat ?? 'N/A' }}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Nama Proses Usecase:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $uatData->nama_proses_usecase ?? 'N/A' }}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 align-top">Keterangan:</td>
                    <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto">
                        {!! $uatData->keterangan_uat ?? '<span class="text-gray-500 italic">N/A</span>' !!}
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">Status:</td>
                    <td class="py-2 px-4 text-gray-900">
                        @php
                            $statusClass = match($uatData->status_uat) {
                                'Passed' => 'bg-green-100 text-green-800',
                                'Failed' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ $uatData->status_uat ?? 'N/A' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Bagian Dokumen UAT --}}
    <div class="md:col-span-2 mt-8">
        <p class="font-semibold text-gray-700">Dokumen UAT:</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
            @forelse($uatData->documents as $document)
                <li>
                    <a href="{{ asset($document->path) }}" target="_blank" class="text-blue-600 hover:underline">
                        <i class="fas fa-file-alt mr-2"></i> {{ $document->filename }}
                    </a>
                </li>
            @empty
                <p class="text-gray-500 italic">Tidak ada dokumen UAT.</p>
            @endforelse
        </ul>
    </div>

    {{-- Bagian Gambar UAT --}}
    <div class="md:col-span-2 mt-8">
        <p class="font-semibold text-gray-700">Gambar UAT:</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mt-2 uat-image-gallery">
            @forelse($uatData->images as $index => $image)
                <a href="{{ asset($image->path) }}" class="block h-40 overflow-hidden rounded-lg border shadow-sm gallery-item"
                   data-full-src="{{ asset($image->path) }}"
                   data-caption="{{ $image->filename }}"
                   data-gallery-index="{{ $index }}"
                   onclick="event.preventDefault(); window.populateAndOpenImageViewerFromHtml(this);">
                    <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}" class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-gray-500 italic">Tidak ada gambar UAT.</p>
            @endforelse
        </div>
    </div>
</div>
