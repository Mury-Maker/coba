// public/js/utils/imagePreviewer.js

import { domUtils } from '../core/domUtils.js';

/**
 * Menginisialisasi fungsionalitas pratinjau gambar untuk input file.
 * @param {HTMLInputElement} fileInput - Elemen input file.
 * @param {HTMLElement} previewContainer - Kontainer untuk menampilkan pratinjau gambar.
 */
export function initImagePreviewer(fileInput, previewContainer) {
    if (!fileInput || !previewContainer) {
        console.error('Image previewer: fileInput atau previewContainer tidak ditemukan.');
        return;
    }

    // Bersihkan listener lama untuk menghindari duplikasi
    fileInput.removeEventListener('change', handleFileSelect);
    previewContainer.removeEventListener('click', handleRemoveButtonClick);

    domUtils.addEventListener(fileInput, 'change', handleFileSelect);
    domUtils.addEventListener(previewContainer, 'click', handleRemoveButtonClick);

    function handleFileSelect(event) {
        // Hapus semua pratinjau gambar baru (tetapi pertahankan gambar lama yang existing)
        const newPreviews = previewContainer.querySelectorAll('.new-image-preview');
        newPreviews.forEach(preview => preview.remove());

        const files = event.target.files;
        if (files.length === 0) {
            // Jika tidak ada file baru dipilih, tampilkan pesan default jika tidak ada gambar lama
            if (previewContainer.children.length === 0) {
                previewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar dipilih atau gambar lama.</span>';
            }
            return;
        }

        // Jika ada file baru, hapus pesan default
        const defaultMessage = previewContainer.querySelector('span.text-gray-500');
        if(defaultMessage) defaultMessage.remove();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgElement = createPreviewImageElement(e.target.result, file.name, null, true); // true = isNew
                    previewContainer.appendChild(imgElement);
                };
                reader.readAsDataURL(file);
            }
        }
    }

    function handleRemoveButtonClick(event) {
        const removeButton = event.target.closest('.remove-image-btn');
        if (removeButton) {
            event.preventDefault();
            const wrapper = removeButton.closest('.image-preview-wrapper');
            if (wrapper) {
                wrapper.remove();
                // Jika input file belum kosong, setel ulang agar 'change' event terpicu jika user pilih file yang sama lagi
                // Namun, untuk multiple files, lebih aman biarkan saja atau implementasi logika yang lebih kompleks.
            }
        }
    }

    // Fungsi untuk membuat elemen pratinjau gambar (digunakan juga di dataManager untuk existing images)
    function createPreviewImageElement(src, filename, imageId = null, isNew = false) {
        const wrapper = document.createElement('div');
        wrapper.className = `relative group border rounded-md p-1 image-preview-wrapper ${isNew ? 'new-image-preview' : 'existing-image-preview'}`;
        wrapper.innerHTML = `
            <img src="${src}" alt="${filename}" class="w-full h-24 object-cover rounded-sm">
            <p class="text-xs text-gray-600 truncate mt-1">${filename}</p>
            <button type="button" class="absolute top-0 right-0 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn" data-image-id="${imageId}" data-image-path="${src}">
                <i class="fas fa-times"></i>
            </button>
        `;
        // Untuk gambar yang sudah ada, tambahkan input hidden untuk melacak yang dipertahankan
        if (!isNew) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = fileInput.name.replace('[]', '_current[]'); // Misal: existing_uat_images_current[]
            hiddenInput.value = src;
            wrapper.appendChild(hiddenInput); // Tambahkan ke dalam wrapper, bukan container terpisah
        }

        return wrapper;
    }

    // Paparkan fungsi createPreviewImageElement agar bisa digunakan di dataManager
    window.createImagePreviewElement = createPreviewImageElement;
}
