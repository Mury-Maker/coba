// public/js/utils/imagePreviewer.js

import { domUtils } from '../core/domUtils.js';

// Perbarui fungsi agar menerima fileInputForName jika itu yang digunakan untuk menentukan nama input hidden
function createPreviewImageElement(src, filename, imageId = null, isNew = false, fileInputForName = null) {
    const wrapper = document.createElement('div');
    wrapper.className = `relative group border rounded-md p-1 image-preview-wrapper ${isNew ? 'new-image-preview' : 'existing-image-preview'}`;
    wrapper.innerHTML = `
        <img src="${src}" alt="${filename}" class="w-full h-24 object-cover rounded-sm">
        <p class="text-xs text-gray-600 truncate mt-1">${filename}</p>
        <button type="button" class="absolute top-0 right-0 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn" data-image-id="${imageId}" data-image-path="${src}">
            <i class="fas fa-times"></i>
        </button>
    `;
    // Tambahkan input hidden untuk gambar yang sudah ada, hanya jika fileInputForName disediakan
    // Ini penting agar hidden input memiliki nama yang benar seperti 'existing_uat_images[]'
    if (!isNew && fileInputForName) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = fileInputForName.name.replace('[]', '_current[]'); // Gunakan nama input file aslinya
        hiddenInput.value = src;
        wrapper.appendChild(hiddenInput);
    } else if (!isNew) {
        // Fallback jika fileInputForName tidak ada (misal dipanggil dari PHP render),
        // maka kita berasumsi nama input adalah 'existing_images[]' atau sejenisnya
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'existing_images[]'; // Nama default jika tidak ada fileInputForName
        hiddenInput.value = src;
        wrapper.appendChild(hiddenInput);
    }

    return wrapper;
}

/**
 * Menginisialisasi fungsionalitas pratinjau gambar untuk input file.
 * @param {HTMLInputElement} fileInput - Elemen input file.
 * @param {HTMLElement} previewContainer - Kontainer untuk menampilkan pratinjau gambar.
 */
export function initImagePreviewer(fileInput, previewContainer) {
    console.log('initImagePreviewer dipanggil untuk:', fileInput.id); // DEBUG
    if (!fileInput || !previewContainer) {
        console.error('Image previewer: fileInput atau previewContainer tidak ditemukan.'); // DEBUG
        return;
    }

    // Bersihkan listener lama untuk menghindari duplikasi
    fileInput.removeEventListener('change', handleFileSelect);
    previewContainer.removeEventListener('click', handleRemoveButtonClick);

    domUtils.addEventListener(fileInput, 'change', handleFileSelect);
    domUtils.addEventListener(previewContainer, 'click', handleRemoveButtonClick);

    function handleFileSelect(event) {
        console.log('File selected for preview.'); // DEBUG
        const newPreviews = previewContainer.querySelectorAll('.new-image-preview');
        newPreviews.forEach(preview => preview.remove());

        const files = event.target.files;
        if (files.length === 0) {
            if (previewContainer.children.length === 0) {
                previewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar dipilih atau gambar lama.</span>';
            }
            return;
        }

        const defaultMessage = previewContainer.querySelector('span.text-gray-500');
        if(defaultMessage) defaultMessage.remove();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    // Pass fileInput itself as fileInputForName
                    const imgElement = createPreviewImageElement(e.target.result, file.name, null, true, fileInput);
                    previewContainer.appendChild(imgElement);
                };
                reader.readAsDataURL(file);
            } else {
                console.warn('File selected is not an image:', file.name); // DEBUG
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
                console.log('Image preview removed:', removeButton.dataset.imagePath); // DEBUG
                // Jika input hidden terkait dengan gambar lama, itu sudah dihapus bersama wrapper.
            }
        }
    }

    // Paparkan fungsi createPreviewImageElement agar bisa digunakan di dataManager
    // Saat dipanggil dari dataManager untuk gambar EXISTING, fileInputForName perlu diteruskan.
    window.createImagePreviewElement = (src, filename, imageId, isNew, customFileInputForName = null) => {
        // Gunakan customFileInputForName jika disediakan, jika tidak, gunakan fileInput dari scope ini
        return createPreviewImageElement(src, filename, imageId, isNew, customFileInputForName || fileInput);
    };
}
