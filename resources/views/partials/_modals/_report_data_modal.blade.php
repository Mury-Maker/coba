@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="reportDataModal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl font-bold text-gray-800 mb-6" id="reportDataModalTitle">Tambah Data Report</h3>
            <form id="reportDataForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="reportDataFormUseCaseId" name="use_case_id">
                <input type="hidden" id="reportDataFormId" name="id">
                <input type="hidden" id="reportDataFormMethod" name="_method" value="POST">

                <div class="space-y-4">
                    {{-- Form Aktor --}}
                    <div>
                        <label for="form_report_aktor" class="block text-gray-700 text-sm font-bold mb-2">Aktor:</label>
                        <input type="text" id="form_report_aktor" name="aktor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    {{-- Form Nama Report --}}
                    <div>
                        <label for="form_report_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Report:</label>
                        <input type="text" id="form_report_nama" name="nama_report" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    {{-- Form Keterangan --}}
                    <div>
                        <label for="form_report_keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                        <textarea id="form_report_keterangan" name="keterangan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Unggah File (Gambar & Dokumen):</label>
                    {{-- Tambahkan kelas bg-gray-100 sebagai warna default yang lebih terang --}}
                    <div class="border-2 border-dashed border-gray-400 p-6 rounded-lg text-center transition-colors bg-white-100" id="dropArea">
                        <p class="text-gray-600 mb-2">Seret & lepas file di sini atau <button type="button" class="text-blue-600 hover:underline" id="fileInputBtn">pilih file</button></p>
                        <p class="text-xs text-gray-500">
                            Gambar (max 25 file, @ 5MB): JPG, PNG, GIF. <br>
                            Dokumen (max 5 file, @ 5MB): PDF, DOCX, XLSX.
                        </p>
                        <input type="file" id="combinedFileInput" name="combined_files[]" multiple hidden accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                </div>
                
                <style>
                    /* Tambahkan kelas ini untuk warna saat file diseret */
                    .drag-over {
                        background-color: #e0f2fe; /* Warna biru muda */
                        border-color: #2563eb; /* Warna border biru yang lebih kuat */
                    }
                </style>

                {{-- Area Preview Gabungan --}}
                <div class="mt-4 space-y-4">
                    {{-- Preview Dokumen --}}
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Dokumen:</p>
                        <div id="documentPreviewContainer" class="space-y-2"></div>
                    </div>
                    {{-- Preview Gambar --}}
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Gambar:</p>
                        <div id="imagePreviewContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelReportDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Batal</button>
                    <button type="submit" id="submitReportDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif

