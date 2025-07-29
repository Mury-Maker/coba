@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="uatDataModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="uatDataModalTitle">Tambah Data UAT</h3>
            <form id="uatDataForm" enctype="multipart/form-data"> {{-- Penting: enctype untuk upload file --}}
                @csrf
                <input type="hidden" id="uatDataFormUseCaseId" name="use_case_id">
                <input type="hidden" id="uatDataFormId" name="id"> {{-- ID UAT Data record (id_uat) --}}
                <input type="hidden" id="uatDataFormMethod" name="_method" value="POST">

                <div class="mb-4">
                    <label for="form_uat_nama_proses_usecase" class="block text-gray-700 text-sm font-bold mb-2">Nama Proses Usecase:</label>
                    <input type="text" id="form_uat_nama_proses_usecase" name="nama_proses_usecase" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label for="form_uat_keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                    <textarea id="form_uat_keterangan" name="keterangan_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                </div>
                <div class="mb-4">
                    <label for="form_uat_status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select id="form_uat_status" name="status_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <option value="">Pilih Status</option>
                        <option value="Passed">Passed</option>
                        <option value="Failed">Failed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="form_uat_images" class="block text-gray-700 text-sm font-bold mb-2">Gambar UAT:</label>
                    <input type="file" id="form_uat_images" name="uat_images[]" accept="image/*" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB per gambar. Format: JPG, PNG, GIF.</p>
                    <div id="form_uat_images_preview" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        {{-- Gambar-gambar akan ditampilkan di sini oleh JavaScript --}}
                    </div>
                    {{-- Input tersembunyi untuk melacak gambar lama yang tetap ada --}}
                    <div id="existing_uat_images_container"></div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" id="cancelUatDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="submit" id="submitUatDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
