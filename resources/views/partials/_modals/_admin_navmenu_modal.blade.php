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

                    <!-- Hidden input -->
                    <input type="hidden" id="form_navmenu_icon" name="menu_icon">

                    <!-- Preview box + arrow -->
                    <div id="iconPreviewBox"
                        class="w-full border rounded px-3 py-2 bg-gray-100 flex items-center justify-between cursor-pointer relative">
                        <span class="flex items-center gap-2 text-xxl text-gray-500">
                            Pilih Icon
                        </span>
                        <i id="arrowIcon"
                            class="fa-solid fa-chevron-right text-gray-500 transition-transform duration-300"></i>
                    </div>

                    <!-- Icon list -->
                    <div id="iconPickerList"
                        class="grid grid-cols-5 gap-3 text-2xl mt-2 border rounded p-3 bg-gray-50 overflow-hidden max-h-0 opacity-0 transition-all duration-500 ease-in-out overflow-y-auto">

                        <!-- Baris 1 -->
                        <i class="fa-solid fa-house cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-house"></i>
                        <i class="fa-solid fa-user cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-user"></i>
                        <i class="fa-solid fa-gear cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-gear"></i>
                        <i class="fa-solid fa-bell cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-bell"></i>
                        <i class="fa-solid fa-chart-line cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-chart-line"></i>

                        <!-- Baris 2 -->
                        <i class="fa-solid fa-book cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-book"></i>
                        <i class="fa-solid fa-envelope cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-envelope"></i>
                        <i class="fa-solid fa-folder cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-folder"></i>
                        <i class="fa-solid fa-heart cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-heart"></i>
                        <i class="fa-solid fa-camera cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-camera"></i>

                        <!-- Baris 3 -->
                        <i class="fa-solid fa-calendar cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-calendar"></i>
                        <i class="fa-solid fa-clock cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-clock"></i>
                        <i class="fa-solid fa-cloud cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-cloud"></i>
                        <i class="fa-solid fa-comment cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-comment"></i>
                        <i class="fa-solid fa-database cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-database"></i>

                        <!-- Baris 4 -->
                        <i class="fa-solid fa-download cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-download"></i>
                        <i class="fa-solid fa-upload cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-upload"></i>
                        <i class="fa-solid fa-edit cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-edit"></i>
                        <i class="fa-solid fa-eye cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-eye"></i>
                        <i class="fa-solid fa-file cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-file"></i>

                        <!-- Baris 5 -->
                        <i class="fa-solid fa-film cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-film"></i>
                        <i class="fa-solid fa-flag cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-flag"></i>
                        <i class="fa-solid fa-gift cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-gift"></i>
                        <i class="fa-solid fa-globe cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-globe"></i>
                        <i class="fa-solid fa-graduation-cap cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-graduation-cap"></i>

                        <!-- Baris 6 -->
                        <i class="fa-solid fa-hashtag cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-hashtag"></i>
                        <i class="fa-solid fa-headphones cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-headphones"></i>
                        <i class="fa-solid fa-image cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-image"></i>
                        <i class="fa-solid fa-key cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-key"></i>
                        <i class="fa-solid fa-lightbulb cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-lightbulb"></i>

                        <!-- Baris 7 -->
                        <i class="fa-solid fa-link cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-link"></i>
                        <i class="fa-solid fa-list cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-list"></i>
                        <i class="fa-solid fa-lock cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-map cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-map"></i>
                        <i class="fa-solid fa-map-marker-alt cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-map-marker-alt"></i>

                        <!-- Baris 8 -->
                        <i class="fa-solid fa-microphone cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-microphone"></i>
                        <i class="fa-solid fa-music cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-music"></i>
                        <i class="fa-solid fa-paperclip cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-paperclip"></i>
                        <i class="fa-solid fa-paper-plane cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-paper-plane"></i>
                        <i class="fa-solid fa-pencil cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-pencil"></i>

                        <!-- Baris 9 -->
                        <i class="fa-solid fa-phone cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-phone"></i>
                        <i class="fa-solid fa-play cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-play"></i>
                        <i class="fa-solid fa-plus cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-plus"></i>
                        <i class="fa-solid fa-print cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-print"></i>
                        <i class="fa-solid fa-question cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-question"></i>

                        <!-- Baris 10 -->
                        <i class="fa-solid fa-road cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-road"></i>
                        <i class="fa-solid fa-rss cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-rss"></i>
                        <i class="fa-solid fa-search cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-search"></i>
                        <i class="fa-solid fa-server cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-server"></i>
                        <i class="fa-solid fa-share cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-share"></i>

                        <!-- Baris 11 -->
                        <i class="fa-solid fa-shield cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-shield"></i>
                        <i class="fa-solid fa-shopping-cart cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-shopping-cart"></i>
                        <i class="fa-solid fa-sign-out-alt cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-sign-out-alt"></i>
                        <i class="fa-solid fa-sitemap cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-sitemap"></i>
                        <i class="fa-solid fa-sliders-h cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-sliders-h"></i>

                        <!-- Baris 12 -->
                        <i class="fa-solid fa-star cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-star"></i>
                        <i class="fa-solid fa-sync cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-sync"></i>
                        <i class="fa-solid fa-table cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-table"></i>
                        <i class="fa-solid fa-tag cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-tag"></i>
                        <i class="fa-solid fa-tasks cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-tasks"></i>

                        <!-- Baris 13 -->
                        <i class="fa-solid fa-terminal cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-terminal"></i>
                        <i class="fa-solid fa-thumbs-up cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-thumbs-up"></i>
                        <i class="fa-solid fa-ticket cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-ticket"></i>
                        <i class="fa-solid fa-trash cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-trash"></i>
                        <i class="fa-solid fa-trophy cursor-pointer p-2 border rounded hover:bg-gray-200"
                            data-value="fa-solid fa-trophy"></i>
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
    document.addEventListener("DOMContentLoaded", function() {
        const previewBox = document.getElementById("iconPreviewBox");
        const iconList = document.getElementById("iconPickerList");
        const inputHidden = document.getElementById("form_navmenu_icon");
        const arrowIcon = document.getElementById("arrowIcon");

        // Toggle daftar icon
        previewBox.addEventListener("click", () => {
            const isHidden = iconList.classList.contains("max-h-0");
            if (isHidden) {
                iconList.classList.remove("max-h-0", "opacity-0");
                iconList.classList.add("max-h-60", "opacity-100");
                arrowIcon.style.transform = "rotate(90deg)";
            } else {
                iconList.classList.add("max-h-0", "opacity-0");
                iconList.classList.remove("max-h-60", "opacity-100");
                arrowIcon.style.transform = "rotate(0deg)";
            }
        });

        // Pilih icon
        iconList.querySelectorAll("i").forEach(icon => {
            icon.addEventListener("click", () => {
                iconList.querySelectorAll("i").forEach(i => i.classList.remove(
                    "icon-selected"));
                icon.classList.add("icon-selected");

                inputHidden.value = icon.getAttribute("data-value");
                previewBox.querySelector("span").innerHTML =
                    `<i class="${icon.getAttribute("data-value")}"></i>`;

                // Sembunyikan list
                iconList.classList.add("max-h-0", "opacity-0");
                iconList.classList.remove("max-h-60", "opacity-100");
                arrowIcon.style.transform = "rotate(0deg)";
            });
        });
    });
</script>
