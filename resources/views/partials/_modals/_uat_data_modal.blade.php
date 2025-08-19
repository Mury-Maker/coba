@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="uatDataModal" class="modal">
        <div class="modal-content">
            <h3 class="text-2xl font-bold text-gray-800 mb-6" id="uatDataModalTitle">Tambah Data UAT</h3>
            <form id="uatDataForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="uatDataFormUseCaseId" name="use_case_id">
                <input type="hidden" id="uatDataFormId" name="id">
                <input type="hidden" id="uatDataFormMethod" name="_method" value="POST">

                <div class="space-y-4">
                    <div>
                        <label for="form_uat_nama_proses_usecase" class="block text-gray-700 text-sm font-bold mb-2">
                            Nama Proses Usecase:
                        </label>
                        <input type="text" id="form_uat_nama_proses_usecase" 
                               name="nama_proses_usecase" 
                               value="Proses Contoh"
                               readonly
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 cursor-not-allowed leading-tight focus:outline-none">
                    </div>                    
                    <div>
                        <label for="form_uat_keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                        <textarea id="form_uat_keterangan" name="keterangan_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Keterangan Usecase"></textarea>
                    </div>
                    <div>
                        <label for="form_uat_status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                        <select id="form_uat_status" name="status_uat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Pilih Status</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Unggah File:</label>
                    <div class="border-2 border-dashed border-gray-400 p-6 rounded-lg text-center hover:border-blue-500 transition-colors" id="uatDropArea">
                        <p class="text-gray-600 mb-2">Seret & lepas file di sini atau <button type="button" class="text-blue-600 hover:underline" id="uatFileInputBtn">pilih file</button></p>
                        <p class="text-xs text-gray-500">
                            Gambar (max 25 file, @ 5MB): JPG, PNG, GIF. <br>
                            Dokumen (max 5 file, @ 5MB): PDF, DOCX, XLSX.
                        </p>
                        <input type="file" id="uatCombinedFileInput" name="uat_combined_files[]" multiple hidden accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Dokumen:</p>
                        <div id="uatDocumentPreviewContainer" class="space-y-2"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Gambar:</p>
                        <div id="uatImagePreviewContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelUatDataFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Batal</button>
                    <button type="submit" id="submitUatDataFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
