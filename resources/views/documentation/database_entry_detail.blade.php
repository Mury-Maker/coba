@extends('layouts.docs')

@section('action-buttons')
    <a href="{{ route('docs.use_case_detail', [
        'category' => $currentCategory,
        'page' => Str::slug($selectedNavItem->menu_nama),
        'useCaseSlug' => Str::slug($parentUseCase->nama_proses)
    ]) }}" class="btn btn-secondary ml-auto">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Aksi
    </a>
@endsection

@section('content')
    <div class="prose max-w-none">
        <h2 class="text-2xl font-bold mb-4">Detail Data Database</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID Database:</p>
                <p>{{ $databaseData->id_database ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Keterangan:</p>
                <p class="prose max-w-none">{!! $databaseData->keterangan ?? 'N/A' !!}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Gambar Database:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                    @forelse($databaseData->images as $image)
                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}" class="w-full h-auto object-cover">
                            <p class="p-2 text-xs text-gray-600 truncate">{{ $image->filename }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Tidak ada gambar Database.</p>
                    @endforelse
                </div>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Relasi:</p>
                <p class="prose max-w-none">{!! $databaseData->relasi ?? 'N/A' !!}</p>
            </div>
        </div>
    </div>
@endsection
