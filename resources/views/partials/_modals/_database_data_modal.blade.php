@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="databaseDataModal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl font-bold text-gray-800 mb-6" id="databaseDataModalTitle">Tambah Data Database</h3>
            <form id="databaseDataForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="databaseDataFormUseCaseId" name="use_case_id">
                <input type="hidden" id="databaseDataFormId" name="id">
                <input type="hidden" id="databaseDataFormMethod" name="_method" value="POST">

                <div class="space-y-4">
                    <div>
                        <label for="form_database_keterangan" class="block text-gray-700 text-sm font-bold mb-2">
                            Nama Tabel:
                        </label>
                        <select id="form_database_keterangan" 
                                name="keterangan" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-white leading-tight focus:outline-none focus:shadow-outline" 
                                required>
                            <option value="" disabled selected>Pilih tabel</option>
                            @foreach($tablesList as $tb)
                                <option value="{{ $tb->nama_tabel }}">{{ $tb->nama_tabel }}</option>
                            @endforeach
                        </select>
                    </div>                    
                    <div>
                        <label for="form_database_relasi" class="block text-gray-700 text-sm font-bold mb-2">Keterangan Relasi:</label>
                        <textarea id="form_database_relasi" name="relasi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Keterangan Relasi Tabelnya"></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Unggah File:</label>
                    <div class="border-2 border-dashed border-gray-400 p-6 rounded-lg text-center hover:border-blue-500 transition-colors" id="databaseDropArea">
                        <p class="text-gray-600 mb-2">Seret & lepas file di sini atau <button type="button" class="text-blue-600 hover:underline" id="databaseFileInputBtn">pilih file</button></p>
                        <p class="text-xs text-gray-500">
                            Gambar (max 25 file, @ 5MB): JPG, PNG, GIF. <br>
                            Dokumen (max 5 file, @ 5MB): PDF, DOCX, XLSX.
                        </p>
                        <input type="file" id="databaseCombinedFileInput" name="database_combined_files[]" multiple hidden accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Dokumen:</p>
                        <div id="databaseDocumentPreviewContainer" class="space-y-2"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Gambar:</p>
                        <div id="databaseImagePreviewContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelDatabaseDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Batal</button>
                    <button type="submit" id="submitDatabaseDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
