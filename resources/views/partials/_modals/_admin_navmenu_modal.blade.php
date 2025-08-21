@if (Auth::check() && Auth::user()->role === 'admin')
    <div id="adminNavMenuModal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="adminNavMenuModalTitle">Tambah Menu Baru</h3>
            <form id="adminNavMenuForm">
                @csrf
                <input type="hidden" id="form_navmenu_id" name="menu_id">
                <input type="hidden" id="form_navmenu_method" name="_method" value="POST">
                <input type="hidden" id="form_navmenu_category_id" name="category_id"
                    value="{{ $selectedNavItem->category_id ?? '' }}">
                <div class="mb-4">
                    <label for="form_navmenu_nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Menu:</label>
                    <input type="text" id="form_navmenu_nama" name="menu_nama" placeholder="Masukkan Nama Menu"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label for="form_navmenu_icon" class="block text-gray-700 text-sm font-bold mb-2">
                        Ikon (kelas Font Awesome):
                        <!-- Ikon Info -->
                        <span class="relative group inline-block">
                            <i class="fa-solid fa-circle-info text-blue-600 cursor-pointer ml-1"></i>
                            <!-- Tooltip -->
                            <div
                                class="absolute left-6 top-1/2 -translate-y-1/2 hidden group-hover:block w-72 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 shadow-lg z-10">
                                <p class="mb-1 font-semibold">Cara mendapatkan kode ikon:</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Buka <a href="https://fontawesome.com/icons" target="_blank"
                                            class="text-blue-400 underline">Font Awesome</a> di website</li>
                                    <li>Cari dan klik ikon yang diinginkan</li>
                                    <li>Salin kelas ikon di bagian atas halaman (contoh: <code>fa-solid fa-house</code>)
                                    </li>
                                    <li>Tempelkan di kolom input</li>
                                </ol>
                            </div>
                        </span>
                    </label>

                    <div class="flex items-center gap-2">
                        <input type="text" id="form_navmenu_icon" name="menu_icon" placeholder="fa-solid fa-house"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <i id="iconPreview" class="text-2xl text-gray-500"></i>
                    </div>
                </div>
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
    document.addEventListener("DOMContentLoaded", function() {
        const submitButton = document.getElementById("submitAdminNavMenuFormBtn");
        const menuNamaInput = document.getElementById("form_navmenu_nama");
        const adminNavMenuModal = document.getElementById("adminNavMenuModal");
        const iconInput = document.getElementById("form_navmenu_icon");
        const iconPreview = document.getElementById("iconPreview");
        const cancelButton = document.getElementById("cancelAdminNavMenuFormBtn");

        // Fungsi untuk mengontrol status tombol
        function checkFormValidity() {
            if (menuNamaInput.value.trim() !== "") {
                submitButton.disabled = false;
                submitButton.classList.remove("opacity-50", "cursor-not-allowed");
            } else {
                submitButton.disabled = true;
                submitButton.classList.add("opacity-50", "cursor-not-allowed");
            }
        }

        // Fungsi baru untuk mereset formulir
        function resetForm() {
            document.getElementById("adminNavMenuForm").reset(); // Reset semua input form
            document.getElementById("form_navmenu_id").value = "";
            document.getElementById("form_navmenu_method").value = "POST";
            document.getElementById("adminNavMenuModalTitle").innerText = "Tambah Menu Baru";

            // Reset pratinjau ikon
            iconInput.value = ""; // Bersihkan nilai input teks ikon
            iconPreview.className = 'text-2xl text-gray-500'; // Reset kelas ikon pratinjau

            // Atur ulang status tombol
            checkFormValidity();
        }

        // Event listener untuk menutup modal dan mereset form
        cancelButton.addEventListener('click', function() {
            adminNavMenuModal.classList.remove('active'); // Asumsi ini adalah cara Anda menutup modal
            resetForm();
        });

        // Event listener saat form ditutup
        adminNavMenuModal.addEventListener('modal:closed', function() {
            resetForm();
        });

        adminNavMenuModal.addEventListener('modal:opened', function() {
            checkFormValidity();
        });

        menuNamaInput.addEventListener("input", checkFormValidity);

        iconInput.addEventListener('input', function() {
            const iconClass = this.value.trim();
            iconPreview.className = 'text-2xl text-gray-500';
            if (iconClass) {
                iconPreview.classList.add(...iconClass.split(' '));
            }
        });

        // Fungsi untuk mengisi form saat edit
        window.loadEditData = function(menu) {
            resetForm(); // Panggil resetForm() terlebih dahulu
            document.getElementById("form_navmenu_id").value = menu.id;
            document.getElementById("form_navmenu_nama").value = menu.menu_nama;
            document.getElementById("form_navmenu_child").value = menu.menu_child;
            document.getElementById("form_navmenu_order").value = menu.menu_order;
            document.getElementById("form_navmenu_status").checked = menu.menu_status;

            const menuIcon = menu.menu_icon || '';
            iconInput.value = menuIcon;
            iconPreview.className = 'text-2xl text-gray-500';
            if (menuIcon) {
                iconPreview.classList.add(...menuIcon.split(' '));
            }
        };
    });
</script>
