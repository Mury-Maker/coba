<div class="prose max-w-none">
    <h2 class="text-2xl font-bold mb-4">Detail Data Database</h2>

    {{-- Tabel Informasi Umum --}}
    <div class="overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <tbody>
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700 w-1/4">ID Database:</td>
                    <td class="py-2 px-4 text-gray-900">{{ $databaseData->id_database ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Bagian Keterangan dan Relasi --}}
    @php
        $sections = [
            'Keterangan' => $databaseData->keterangan ?? '<span class="text-gray-500 italic">N/A</span>',
            'Relasi' => $databaseData->relasi ?? '<span class="text-gray-500 italic">N/A</span>',
        ];
    @endphp

    @foreach($sections as $label => $content)
        <div class="relative overflow-x-auto rounded-lg border-2 border-gray-400 shadow-sm mb-6">
            {{-- Tombol Salin --}}
            <button onclick="copyToClipboard('copy-{{ Str::slug($label) }}')"
                    class="absolute top-2 right-2 text-sm text-gray-600 hover:text-black focus:outline-none">
                <i class="fas fa-copy mr-1"></i> Salin
            </button>

            <table class="min-w-full bg-white divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left font-bold text-lg text-gray-700 bg-gray-100" colspan="2">
                            {{ $label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 text-gray-900 prose max-w-none overflow-auto" colspan="2">
                            <div id="copy-{{ Str::slug($label) }}">{!! $content !!}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- Bagian Dokumen --}}
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

    {{-- Bagian Gambar --}}
    <div class="md:col-span-2 mt-8">
        <p class="font-semibold text-gray-700">Gambar Database:</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mt-2 uat-image-gallery">
            @forelse($databaseData->images as $index => $image)
                <a href="{{ asset($image->path) }}"
                   class="block h-40 overflow-hidden rounded-lg border shadow-sm gallery-item"
                   data-full-src="{{ asset($image->path) }}" data-caption="{{ $image->filename }}"
                   data-gallery-index="{{ $index }}"
                   onclick="event.preventDefault(); window.populateAndOpenImageViewerFromHtml(this);">
                    <img src="{{ asset($image->path) }}" alt="{{ $image->filename }}"
                         class="w-full h-full object-cover">
                </a>
            @empty
                <p class="text-gray-500 italic">Tidak ada gambar Database.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Script untuk Salin --}}
<script>
    function copyToClipboard(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;

        const range = document.createRange();
        range.selectNodeContents(el);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        try {
            document.execCommand('copy');
            selection.removeAllRanges();
            alert("Konten berhasil disalin!");
        } catch (err) {
            console.error('Gagal menyalin:', err);
        }
    }
</script>
