@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="useCaseModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="useCaseModalTitle">Detail Aksi</h3>
            <form id="useCaseForm">
                @csrf
                <input type="hidden" id="useCaseFormMenuId" name="menu_id" value="{{ $menu_id ?? '' }}">
                <input type="hidden" id="useCaseFormUseCaseId" name="id"> {{-- ID use_cases record --}}
                <input type="hidden" id="useCaseFormMethod" name="_method" value="POST"> {{-- Default for create --}}

                {{-- Grid Kontainer Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="mb-4 md:col-span-2">
                        <label for="form_nama_proses" class="block text-gray-700 text-sm font-bold mb-2">Nama Proses:</label>
                        <input type="text" id="form_nama_proses" name="nama_proses" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>

                    {{-- Deskripsi Aksi (Mengambil 2 kolom) --}}
                    <div class="mb-4 md:col-span-2">
                        <label for="form_deskripsi_aksi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Aksi:</label>
                        <textarea id="form_deskripsi_aksi" name="deskripsi_aksi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-32"></textarea>
                    </div>

                    {{-- Aktor dan Tujuan (Berbagi 2 kolom) --}}
                    <div class="mb-4">
                        <label for="form_aktor" class="block text-gray-700 text-sm font-bold mb-2">Aktor:</label>
                        <input type="text" id="form_aktor" name="aktor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                    <div class="mb-4">
                        <label for="form_tujuan" class="block text-gray-700 text-sm font-bold mb-2">Tujuan:</label>
                        <textarea id="form_tujuan" name="tujuan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                    </div>

                    {{-- Kondisi Awal (Mengambil 2 kolom) --}}
                    <div class="mb-4 md:col-span-2">
                        <label for="form_kondisi_awal" class="block text-gray-700 text-sm font-bold mb-2">Kondisi Awal:</label>
                        <textarea id="form_kondisi_awal" name="kondisi_awal" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                    </div>
                    {{-- Kondisi Akhir (Mengambil 2 kolom) --}}
                    <div class="mb-4 md:col-span-2">
                        <label for="form_kondisi_akhir" class="block text-gray-700 text-sm font-bold mb-2">Kondisi Akhir:</label>
                        <textarea id="form_kondisi_akhir" name="kondisi_akhir" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                    </div>
                    {{-- Aksi Reaksi (Mengambil 2 kolom) --}}
                    <div class="mb-4 md:col-span-2">
                        <label for="form_aksi_aktor" class="block text-gray-700 text-sm font-bold mb-2">Aksi Aktor:</label>
                        <textarea id="form_aksi_aktor" name="aksi_aktor" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                    </div>
                    {{-- Reaksi Sistem (Mengambil 2 kolom) --}}
                    <div class="mb-4 md:col-span-2">
                        <label for="form_reaksi_sistem" class="block text-gray-700 text-sm font-bold mb-2">Reaksi Sistem:</label>
                        <textarea id="form_reaksi_sistem" name="reaksi_sistem" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-24"></textarea>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" id="cancelUseCaseFormBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="submit" id="submitUseCaseFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
