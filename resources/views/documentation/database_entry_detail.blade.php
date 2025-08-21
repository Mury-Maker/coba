<div class="prose max-w-none space-y-8">
    {{-- Judul Utama --}}
    <h2 class="text-2xl font-bold">Detail Data Database</h2>

    <hr>

    {{-- Bagian Keterangan dan Relasi --}}
    <div class="space-y-4">
        <h2>Nama Tabel: {{ $tablesData->nama_tabel ?? 'Tidak ada data' }}</h2>
        <div class="min-w-full overflow-hidden rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full bg-white text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 border-b border-gray-300">No</th>
                        <th class="py-3 px-4 border-b border-gray-300">Nama Kolom</th>
                        <th class="py-3 px-4 border-b border-gray-300">Tipe Data</th>
                        <th class="py-3 px-4 border-b border-gray-300">Attribute</th>
                        <th class="py-3 px-4 border-b border-gray-300">Key</th>
                    </tr>
                </thead>
                <tbody id="useCaseListTableBody">
                    @forelse($tablesData->columns as $col)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-2.5 px-4 border-b">{{ $loop->iteration }}</td>
                            <td class="py-2.5 px-4 border-b">{{ $col->nama_kolom }}</td>
                            <td class="py-2.5 px-4 border-b">{{ strtok($col->tipe, ' ') }}</td>
                            <td class="py-2.5 px-4 border-b">{{ strstr($col->tipe, ' ') }}</td>
                            <td class="py-2.5 px-4 border-b">
                                @if ($col->is_primary === 1)
                                    PK
                                @elseif($col->is_foreign === 1)
                                    FK
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                Tidak ada tindakan (use case) yang didokumentasikan untuk menu ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <hr>

    {{-- Bagian Relasi --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Relasi</h2>
        <div class="mt-4 space-y-5">
            @forelse($relations as $rel)
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5 transition hover:shadow-xl">
                    <h4 class="text-lg font-semibold text-indigo-600 mb-4">Relasi {{ $loop->iteration }}</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Tabel Asal --}}
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <div class="text-sm font-semibold text-indigo-800 mb-2">
                                📥 Tabel Asal (Child)
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Nama Tabel:</span> {{ $rel->fromTable->nama_tabel }}
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Kolom:</span> {{ $rel->fromColumn->nama_kolom }}
                                <span class="text-xs text-gray-500">(FK)</span>
                            </div>
                        </div>

                        {{-- Tabel Referensi --}}
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="text-sm font-semibold text-green-800 mb-2">
                                🔗 Referenced (Parent)
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Nama Tabel:</span> {{ $rel->toTable->nama_tabel }}
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Kolom:</span> {{ $rel->toColumn->nama_kolom }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 italic">Tidak ada Relasi ditemukan</p>
            @endforelse
        </div>
    </div>

    <hr>

    {{-- Keterangan Relasi --}}
    <div>
        <h2 class="text-lg font-semibold text-yellow-800">Keterangan Relasi</h2>
        <div class="bg-yellow-50 p-5 rounded-lg border border-yellow-200 shadow-sm mt-2">
            <p class="text-gray-700 leading-relaxed">{{ $databaseData->relasi }}</p>
        </div>
    </div>

    <hr>

    {{-- Syntax Pembuatan Tabel --}}
    <div>
        <h2>Syntax Pembuatan Tabel:</h2>
        <div class="relative overflow-x-auto rounded-lg shadow-sm">
            {{-- Tombol Salin --}}
            <button onclick="copyToClipboard(this, 'copy-syntax')"
                class="absolute top-2 right-2 px-3 py-1 bg-gray-700 text-sm text-white rounded-md hover:bg-gray-600 transition focus:outline-none flex items-center gap-2">
                <i class="fa-solid fa-clipboard fa-lg"></i>
                <span>Salin</span>
            </button>
            <pre class="bg-gray-800 text-white p-4 rounded-lg overflow-x-auto">
                <code id="copy-syntax" class="whitespace-pre-line">
                    {{ $tablesData->syntax }}
                </code>
            </pre>
        </div>
    </div>

    <hr>

    {{-- Bagian Dokumen (Tetap di atas) --}}
    <div class="space-y-4">
        {{-- Dokumen --}}
        <div>
            <p class="font-semibold text-gray-700">Dokumen Pendukung:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                @forelse($databaseData->documents as $document)
                    <li>
                        @php
                            $extension = pathinfo($document->filename, PATHINFO_EXTENSION);
                            $iconClass = 'fas fa-file text-gray-500';
                            if ($extension === 'pdf') {
                                $iconClass = 'fas fa-file-pdf text-red-600';
                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                $iconClass = 'fas fa-file-word text-blue-600';
                            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                $iconClass = 'fas fa-file-excel text-green-600';
                            }
                        @endphp
                        <a href="{{ asset($document->path) }}" target="_blank" class="text-blue-600 hover:underline">
                            <i class="{{ $iconClass }} mr-2"></i> {{ $document->filename }}
                        </a>
                    </li>
                @empty
                    <p class="text-gray-500 italic">Tidak ada dokumen Database.</p>
                @endforelse
            </ul>
        </div>

        {{-- Bagian Gambar (Tetap di bawah) --}}
        <div>
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
</div>

{{-- Script untuk Salin --}}
<script>
    function copyToClipboard(button, elementId) {
        const el = document.getElementById(elementId);
        if (!el) {
            console.error(`Elemen dengan ID "${elementId}" tidak ditemukan.`);
            return;
        }

        const text = el.innerText.trim();
        const originalHtml = button.innerHTML;
        const originalClasses = button.className;

        navigator.clipboard.writeText(text).then(() => {
            // Ubah tombol menjadi ikon checklist dan teks "Disalin"
            button.innerHTML = '<i class="fa-solid fa-check fa-lg"></i><span class="ml-2">Disalin!</span>';
            button.classList.remove("bg-gray-700", "hover:bg-gray-600");
            button.classList.add("bg-green-600", "hover:bg-green-600");

            setTimeout(() => {
                // Kembalikan tombol ke kondisi semula
                button.innerHTML = originalHtml;
                button.className = originalClasses;
            }, 2000);
        }).catch(err => {
            console.error("Gagal menyalin:", err);
            // Tambahkan notifikasi error jika diperlukan
        });
    }
</script>
