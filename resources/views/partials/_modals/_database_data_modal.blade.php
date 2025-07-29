@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="databaseDataModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="databaseDataModalTitle">Tambah Data Database</h3>
            <form id="databaseDataForm" enctype="multipart/form-data"> {{-- Penting: enctype untuk upload file --}}
                @csrf
                <input type="hidden" id="databaseDataFormUseCaseId" name="use_case_id">
                <input type="hidden" id="databaseDataFormId" name="id"> {{-- ID Database Data record (id_database) --}}
                <input type="hidden" id="databaseDataFormMethod" name="_method" value="POST">

                <div class="mb-4">
                    <label for="form_database_keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                    <textarea id="form_database_keterangan" name="keterangan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                </div>
                <div class="mb-4">
                    <label for="form_database_images" class="block text-gray-700 text-sm font-bold mb-2">Gambar Database:</label>
                    <input type="file" id="form_database_images" name="database_images[]" accept="image/*" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB per gambar. Format: JPG, PNG, GIF.</p>
                    <div id="form_database_images_preview" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        {{-- Gambar-gambar akan ditampilkan di sini oleh JavaScript --}}
                    </div>
                    {{-- Input tersembunyi untuk melacak gambar lama yang tetap ada --}}
                    <div id="existing_database_images_container"></div>
                </div>
                <div class="mb-4">
                    <label for="form_database_relasi" class="block text-gray-700 text-sm font-bold mb-2">Relasi:</label>
                    <textarea id="form_database_relasi" name="relasi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" id="cancelDatabaseDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="submit" id="submitDatabaseDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
