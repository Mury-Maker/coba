// public/js/utils/imagePreviewer.js
import { domUtils } from '../core/domUtils.js';

// Perbarui fungsi agar menerima fileInput
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
    if (!isNew && fileInputForName) { // Hanya tambahkan hidden input jika ini gambar lama
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        // Gunakan fileInputForName untuk mendapatkan nama input yang benar
        hiddenInput.name = fileInputForName.name.replace('[]', '_current[]');
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
    if (!fileInput || !previewContainer) {
        console.error('Image previewer: fileInput atau previewContainer tidak ditemukan.');
        return;
    }

    fileInput.removeEventListener('change', handleFileSelect);
    previewContainer.removeEventListener('click', handleRemoveButtonClick);

    domUtils.addEventListener(fileInput, 'change', handleFileSelect);
    domUtils.addEventListener(previewContainer, 'click', handleRemoveButtonClick);

    function handleFileSelect(event) {
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
                    // Pass the original fileInput to createPreviewImageElement
                    const imgElement = createPreviewImageElement(e.target.result, file.name, null, true, fileInput);
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
                // Untuk input hidden dari gambar lama, pastikan itu juga dihapus dari parentnya
                const hiddenInputToRemove = wrapper.querySelector('input[type="hidden"]');
                if(hiddenInputToRemove) hiddenInputToRemove.remove();
            }
        }
    }

    // Paparkan fungsi createPreviewImageElement dengan parameter yang benar
    window.createImagePreviewElement = (src, filename, imageId, isNew) => {
        // Saat dipanggil dari luar (misal dataManager), fileInputForName tidak di-pass karena itu untuk gambar baru.
        // Untuk gambar lama yang di-render dari PHP/JS, kita hanya perlu path dan ID
        return createPreviewImageElement(src, filename, imageId, isNew, fileInput);
    };
}
