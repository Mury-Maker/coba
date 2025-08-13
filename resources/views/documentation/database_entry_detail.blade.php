<div class="prose max-w-none">
    <h2 class="text-2xl font-bold mb-4">Detail Data Database</h2>

    {{-- Tabel Informasi Umum --}}
    <div class="overflow-x-auto rounded-lg shadow-sm mb-6">
            <p class="py-2 px-4 whitespace-nowrap font-semibold text-gray-700"><strong>ID Database:</strong> {{ $databaseData->id_database ?? 'N/A' }}</p>
    </div>

    {{-- Bagian Keterangan dan Relasi --}}
        <h2>Nama Tabel: {{ $tablesData->nama_tabel ?? 'Tidak ada data' }}</h2>
        <div class="kolom-tabels">
        <div class="min-w-full m-2">
            <table class="min-w-full bg-white border border-gray-300 text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-r border-b">No</th>
                        <th class="py-2 px-4 border-r border-b">Nama Kolom</th>
                        <th class="py-2 px-4 border-r border-b">Tipe Data</th>
                        <th class="py-2 px-4 border-r border-b">Attribute</th>
                        <th class="py-2 px-4 border-r border-b">Key</th>
                        
                        
                    </tr>
                </thead>
                <tbody id="useCaseListTableBody">
                    @forelse($tablesData->columns as $col)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-2 px-4 border-r border-b">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4 border-r border-b">{{ $col->nama_kolom }}</td>
                            <td class="py-2 px-4 border-r border-b">{{ strtok($col->tipe, " ")  }}</td>
                            <td class="py-2 px-4 border-r border-b">{{ strstr($col->tipe, " ") }}</td>
                            <td class="py-2 px-4 border-r border-b">
                                @if($col->is_primary === 1)
                                PK
                                @elseif($col->is_foreign === 1)
                                FK
                                @else

                                @endif
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
        <div class="relasi">
            <h2>Relasi:</h2>
            <div class="card-relasi">

                @forelse($relations as $rel)
                <div class="table-card-body">
                    <h4>Relasi {{$loop->iteration}}</h4>
                <div class="from-table">
                    <div class="keterangan-tabel">Tabel Asal (Child)</div>
                    <div class="nama-tabel">Nama Tabel: {{ $rel->fromTable->nama_tabel }}</div>
                    <div class="nama-kolom">Kolom: {{ $rel->fromColumn->nama_kolom }} (FK)</div>
                </div>

                <div class="to-table">
                    <div class="keterangan-tabel">Referenced (Parent)</div>
                    <div class="nama-tabel">Nama Tabel: {{ $rel->toTable->nama_tabel }}</div>
                    <div class="nama-kolom">Kolom: {{ $rel->toColumn->nama_kolom }}</div>
                </div>
                </div>


                @empty

                <p>Tidak ada Relasi ditemukan</p>

                @endforelse

            </div>

            <div class="keterangan-relasi">
                <h2>Keterangan Relasi</h2>
                <p>{{$databaseData->relasi}}</p>
            </div>
        </div>
        <h2>Syntax Pembuatan Tabel:</h2>
        <div class="relative overflow-x-auto rounded-lg shadow-sm mb-6">
            {{-- Tombol Salin --}}
            <button onclick="copyToClipboard('copy-syntax')"
                    class="absolute top-2 right-2 text-sm text-white hover:text-blue-600 focus:outline-none">
                <i class="fas fa-copy mr-1"></i> Salin
            </button>
            <pre class="whitespace-pre-line">
                <code id="copy-syntax">
                {{ $tablesData->syntax }}
                </code>
            </pre>
        </div>

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
