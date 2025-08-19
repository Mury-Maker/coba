@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="adminCategoryModal" class="modal">
        <div class="modal-content">
            <h2 id="adminCategoryModalTitle" class="text-lg font-semibold mb-4">Tambah Kategori</h2>

            <form id="adminCategoryForm">
                @csrf
                <input type="hidden" id="form_category_method" name="_method" value="POST">
                <input type="hidden" id="form_category_id_to_edit" name="id_to_edit" value=""> {{-- ID kategori saat edit --}}

                <div class="mb-4">
                    <label for="form_category_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                    <input type="text" id="form_category_name" name="name" required placeholder="Masukkan Nama Kategori"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelAdminCategoryFormBtn"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</button>
                    <button type="submit" id="submitAdminCategoryFormBtn"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
