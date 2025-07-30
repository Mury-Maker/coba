
@section('action-buttons')
    {{-- Tombol untuk kembali ke halaman use case utama --}}
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID UAT:</p>
                <p>{{ $uatData->id_uat ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Nama Proses Usecase:</p>
                <p>{{ $uatData->nama_proses_usecase ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Keterangan:</p>
                <p class="prose max-w-none">{!! $uatData->keterangan_uat ?? 'N/A' !!}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Status:</p>
                <p>{{ $uatData->status_uat ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Gambar UAT:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                    @forelse($uatData->images as $image)
                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}" class="w-full h-auto object-cover">
                            <p class="p-2 text-xs text-gray-600 truncate">{{ $image->filename }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Tidak ada gambar UAT.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
