@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="adminNavMenuModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="adminNavMenuModalTitle">Tambah Menu Baru</h3>
            <form id="adminNavMenuForm">
                @csrf
                <input type="hidden" id="form_navmenu_id" name="menu_id">
                <input type="hidden" id="form_navmenu_method" name="_method" value="POST">
                <input type="hidden" id="form_navmenu_category_id" name="category_id"
                    value="{{ $selectedNavItem->category_id ?? '' }}"> {{-- Ambil dari selectedNavItem atau default --}}

                <div class="mb-4">
                    <label for="form_navmenu_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Menu:</label>
                    <input type="text" id="form_navmenu_nama" name="menu_nama"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Ikon:</label>
                
                    <!-- Hidden input untuk nilai asli -->
                    <input type="hidden" id="form_navmenu_icon" name="menu_icon">
                
                    <!-- "Input" untuk preview icon + tombol -->
                    <div class="flex">
                        <div 
                            id="iconPreviewBox" 
                            class="w-full border rounded-l px-3 py-2 bg-gray-100 flex items-center text-xl text-gray-500"
                        >
                            Pilih Icon
                        </div>
                        <button 
                            type="button" 
                            id="openIconPickerBtn" 
                            class="px-3 py-2 bg-gray-200 border-l rounded-r hover:bg-gray-300 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-icons"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Icon Picker -->
                <div id="iconPickerModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center hidden z-50">
                    <div class="bg-white p-5 rounded-lg max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">Pilih Icon</h3>
                        <div class="grid grid-cols-5 gap-3 text-2xl" id="iconPickerGrid">
                            <i class="fa-solid fa-house cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-house"></i>
                            <i class="fa-solid fa-user cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-user"></i>
                            <i class="fa-solid fa-gear cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-gear"></i>
                            <i class="fa-solid fa-bell cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-bell"></i>
                            <i class="fa-solid fa-chart-line cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-chart-line"></i>
                            <i class="fa-solid fa-book cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-book"></i>
                            <i class="fa-solid fa-envelope cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-envelope"></i>
                            <i class="fa-solid fa-folder cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-folder"></i>
                            <i class="fa-solid fa-heart cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-heart"></i>
                            <i class="fa-solid fa-camera cursor-pointer p-2 border rounded hover:bg-gray-200" data-value="fa-solid fa-camera"></i>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="button" id="closeIconPickerBtn" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Tutup</button>
                        </div>
                    </div>
                </div>
                
                <style>
                    .icon-selected {
                        background-color: #2563eb;
                        color: white;
                    }
                </style>
                <div class="mb-4">
                    <label for="form_navmenu_child" class="block text-gray-700 text-sm font-bold mb-2">Parent
                        Menu:</label>
                    <select id="form_navmenu_child" name="menu_child"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <option value="0">Tidak Ada (Menu Utama)</option>
                        {{-- Opsi parent akan diisi oleh JavaScript saat modal dibuka --}}
                    </select>
                </div>
                <div class="mb-4">
                    <label for="form_navmenu_order" class="block text-gray-700 text-sm font-bold mb-2">Urutan:</label>
                    <input type="number" id="form_navmenu_order" name="menu_order"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="0"
                        required>
                </div>
                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="form_navmenu_status" name="menu_status" value="1"
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Centang Jika Ingin Menu Ini Memiliki Konten (Daftar
                            Aksi)</span>
                    </label>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" id="cancelAdminNavMenuFormBtn"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="submit" id="submitAdminNavMenuFormBtn"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const openBtn = document.getElementById("openIconPickerBtn");
        const closeBtn = document.getElementById("closeIconPickerBtn");
        const modal = document.getElementById("iconPickerModal");
        const iconGrid = document.getElementById("iconPickerGrid");
        const inputHidden = document.getElementById("form_navmenu_icon");
        const previewBox = document.getElementById("iconPreviewBox");

        // Buka modal
        openBtn.addEventListener("click", () => {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        });

        // Tutup modal
        closeBtn.addEventListener("click", () => {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
        });

        // Pilih icon
        iconGrid.querySelectorAll("i").forEach(icon => {
            icon.addEventListener("click", () => {
                iconGrid.querySelectorAll("i").forEach(i => i.classList.remove("icon-selected"));
                icon.classList.add("icon-selected");

                // Simpan class FA ke hidden input
                inputHidden.value = icon.getAttribute("data-value");

                // Tampilkan icon di box
                previewBox.innerHTML = `<i class="${icon.getAttribute("data-value")}"></i>`;

                // Tutup modal
                modal.classList.remove("flex");
                modal.classList.add("hidden");
            });
        });
    });
</script>
