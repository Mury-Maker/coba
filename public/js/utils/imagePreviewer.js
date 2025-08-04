// public/js/utils/imagePreviewer.js

import { domUtils } from '../core/domUtils.js';
import { notificationManager } from '../core/notificationManager.js';

// Deklarasi Map global, hanya satu kali!
export const selectedFilesMap = new Map();

/**
 * Membuat elemen div untuk pratinjau gambar.
 * @param {string} src - URL gambar.
 * @param {string} filename - Nama file.
 * @param {number|null} imageId - ID gambar dari database (untuk gambar lama).
 * @param {boolean} isNew - Apakah ini gambar baru (dari input file).
 * @param {HTMLInputElement|null} fileInputForName - Elemen input file asli untuk mendapatkan nama input hidden.
 * @param {File|null} file - Objek File jika ini adalah file baru.
 * @returns {HTMLDivElement}
 */
function createPreviewImageElement(src, filename, imageId = null, isNew = false, fileInputForName = null, file = null) {
    const wrapper = document.createElement('div');
    wrapper.className = `relative group border rounded-md p-1 image-preview-wrapper ${isNew ? 'new-image-preview' : 'existing-image-preview'}`;

    const fileKey = file ? `${file.name}|${file.size}` : filename;

    let hiddenInputHtml = '';
    if (!isNew && fileInputForName) {
        const inputName = fileInputForName.name.replace('[]', '_current[]');
        hiddenInputHtml = `<input type="hidden" name="${inputName}" value="${src}">`;
    } else if (file) {
        hiddenInputHtml = `<input type="hidden" name="new_files_temp[]" value="${fileKey}">`;
    }

    wrapper.innerHTML = `
        <img src="${src}" alt="${filename}" class="w-full h-24 object-cover rounded-sm">
        <p class="text-xs text-gray-600 truncate mt-1">${filename}</p>
        <button type="button" class="absolute top-0 right-0 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn" data-image-key="${fileKey}">
            <i class="fas fa-times"></i>
        </button>
        ${hiddenInputHtml}
    `;

    return wrapper;
}

/**
 * Menginisialisasi fungsionalitas pratinjau gambar untuk input file.
 * @param {HTMLInputElement} fileInput - Elemen input file.
 * @param {HTMLElement} previewContainer - Kontainer untuk menampilkan pratinjau gambar.
 */
export function initImagePreviewer(fileInput, previewContainer) {
    console.log('initImagePreviewer dipanggil untuk:', fileInput.id);
    if (!fileInput || !previewContainer) {
        console.error('Image previewer: fileInput atau previewContainer tidak ditemukan.');
        return;
    }

    // Mengosongkan map setiap kali modal dibuka
    selectedFilesMap.clear();

    const MAX_FILES_PER_UPLOAD = 25;

    domUtils.addEventListener(fileInput, 'change', (event) => {
        console.log('File selected for preview.');
        const files = event.target.files;
        if (files.length === 0) return;

        let filesToProcess = Array.from(files);

        // --- Perubahan logika di sini: validasi hanya file yang baru dipilih ---
        if (files.length > MAX_FILES_PER_UPLOAD) {
            notificationManager.showNotification(`Anda memilih ${files.length} file. Hanya ${MAX_FILES_PER_UPLOAD} file pertama yang akan diunggah.`, 'warning');
            filesToProcess.length = MAX_FILES_PER_UPLOAD; // Potong array menjadi 25 file pertama
        }

        const defaultMessage = previewContainer.querySelector('span.text-gray-500');
        if (defaultMessage) defaultMessage.remove();

        // Hapus preview file baru yang sudah ada sebelum menambahkan yang baru
        previewContainer.querySelectorAll('.new-image-preview').forEach(el => el.remove());
        selectedFilesMap.clear(); // Hapus file dari map juga

        for (const file of filesToProcess) {
            const fileKey = `${file.name}|${file.size}`;
            if (file.type.startsWith('image/')) {
                selectedFilesMap.set(fileKey, file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgElement = createPreviewImageElement(e.target.result, file.name, null, true, fileInput, file);
                    previewContainer.appendChild(imgElement);
                };
                reader.readAsDataURL(file);
            } else {
                console.warn('File selected is not an image:', file.name);
            }
        }
        event.target.value = '';
    });

    domUtils.addEventListener(previewContainer, 'click', (event) => {
        const removeButton = event.target.closest('.remove-image-btn');
        if (removeButton) {
            event.preventDefault();
            const wrapper = removeButton.closest('.image-preview-wrapper');
            if (wrapper) {
                const fileKey = removeButton.dataset.imageKey;

                // Hapus dari Map jika itu file baru
                selectedFilesMap.delete(fileKey);

                wrapper.remove();
                console.log('Image preview removed:', fileKey);

                if (previewContainer.children.length === 0) {
                    previewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar dipilih atau gambar lama.</span>';
                }
            }
        }
    });

    window.createImagePreviewElement = (src, filename, imageId, isNew, customFileInputForName = null) => {
        const fileInput = customFileInputForName || domUtils.getElement('form_uat_images') || domUtils.getElement('form_database_images');
        const imgElement = createPreviewImageElement(src, filename, imageId, isNew, fileInput);

        const defaultMessage = previewContainer.querySelector('span.text-gray-500');
        if(defaultMessage) defaultMessage.remove();

        return imgElement;
    };
}
