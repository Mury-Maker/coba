// public/js/documentation/useCase/databaseDataManager.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';
import { initImagePreviewer } from '../../utils/imagePreviewer.js';

export function initDatabaseDataManager() {
    const databaseDataModal = domUtils.getElement('databaseDataModal');
    const databaseDataModalTitle = domUtils.getElement('databaseDataModalTitle');
    const databaseDataForm = domUtils.getElement('databaseDataForm');
    const databaseDataFormUseCaseId = domUtils.getElement('databaseDataFormUseCaseId');
    const databaseDataFormId = domUtils.getElement('databaseDataFormId');
    const databaseDataFormMethod = domUtils.getElement('databaseDataFormMethod');
    const cancelDatabaseDataFormBtn = domUtils.getElement('cancelDatabaseDataFormBtn');
    const addDatabaseDataBtn = domUtils.getElement('addDatabaseDataBtn'); // Tombol "Tambah" di halaman detail use case
    const databaseDataTableBody = domUtils.getElement('databaseDataTableBody'); // Tabel daftar Database di halaman detail use case

    const formDatabaseKeterangan = domUtils.getElement('form_database_keterangan');
    const formDatabaseRelasi = domUtils.getElement('form_database_relasi');
    const formDatabaseImagesInput = domUtils.getElement('form_database_images');
    const formDatabaseImagesPreview = domUtils.getElement('form_database_images_preview');
    const existingDatabaseImagesContainer = domUtils.getElement('existing_database_images_container');

    let currentDatabaseImages = []; // Menyimpan daftar path gambar Database saat ini (untuk edit)

    /**
     * Membuka modal Database Data.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [databaseData=null] - Objek Database Data untuk mode edit.
     */
    function openDatabaseDataModal(mode, databaseData = null) {
        if (!databaseDataForm) {
            notificationManager.showNotification("Elemen 'databaseDataForm' tidak ditemukan.", "error");
            return;
        }

        databaseDataForm.reset();
        formDatabaseImagesPreview.innerHTML = ''; // Bersihkan pratinjau
        existingDatabaseImagesContainer.innerHTML = ''; // Bersihkan input hidden untuk gambar lama
        currentDatabaseImages = []; // Reset daftar gambar saat ini

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Database.', 'error');
            return;
        }
        databaseDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            databaseDataModalTitle.textContent = 'Tambah Data Database Baru';
            databaseDataFormMethod.value = 'POST';
            databaseDataFormId.value = '';
            formDatabaseKeterangan.value = '';
            formDatabaseRelasi.value = '';
        } else if (mode === 'edit' && databaseData) {
            databaseDataModalTitle.textContent = 'Edit Data Database';
            databaseDataFormMethod.value = 'POST'; // Untuk FormData PUT
            databaseDataFormId.value = databaseData.id_database;

            formDatabaseKeterangan.value = databaseData.keterangan || '';
            formDatabaseRelasi.value = databaseData.relasi || '';

            // Tampilkan gambar-gambar Database yang sudah ada
            if (databaseData.images && databaseData.images.length > 0) {
                currentDatabaseImages = databaseData.images.map(img => img.path); // Simpan path gambar saat ini
                databaseData.images.forEach(image => {
                    formDatabaseImagesPreview.appendChild(createImagePreviewElement(image.path, image.filename, image.id));
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'existing_database_images[]';
                    hiddenInput.value = image.path;
                    existingDatabaseImagesContainer.appendChild(hiddenInput);
                });
            }
        }
        domUtils.toggleModal(databaseDataModal, true);
        initImagePreviewer(formDatabaseImagesInput, formDatabaseImagesPreview);
    }

    /**
     * Menutup modal Database Data.
     */
    function closeDatabaseDataModal() {
        domUtils.toggleModal(databaseDataModal, false);
        databaseDataForm.reset();
        formDatabaseImagesPreview.innerHTML = '';
        existingDatabaseImagesContainer.innerHTML = '';
        currentDatabaseImages = [];
        formDatabaseImagesInput.value = '';
    }

    domUtils.addEventListener(cancelDatabaseDataFormBtn, 'click', closeDatabaseDataModal);

    // Event listener untuk tombol "Tambah" di halaman detail use case
    if (addDatabaseDataBtn) {
        domUtils.addEventListener(addDatabaseDataBtn, 'click', () => {
            openDatabaseDataModal('create');
        });
    }

    // Delegasi event untuk tombol Edit dan Delete di tabel Database
    if (databaseDataTableBody) {
        domUtils.addEventListener(databaseDataTableBody, 'click', async (e) => {
            const viewBtn = e.target.closest('.btn-action.bg-blue-500'); // Tombol detail
            const editBtn = e.target.closest('.edit-database-btn');
            const deleteBtn = e.target.closest('.delete-database-btn');

            if (viewBtn) {
                const databaseId = parseInt(viewBtn.dataset.id);
                const database = window.APP_BLADE_DATA.singleUseCase.database_data.find(item => item.id_database === databaseId);
                if (database) {
                    let imagesHtml = '<p class="text-gray-500 italic">Tidak ada gambar Database.</p>';
                    if (database.images && database.images.length > 0) {
                        imagesHtml = `<div class="grid grid-cols-2 gap-2 mt-2">`;
                        database.images.forEach(img => {
                            imagesHtml += `<div class="border rounded-lg overflow-hidden shadow-sm"><img src="${img.path}" alt="${img.filename}" class="w-full h-auto object-cover"><p class="p-1 text-xs text-gray-600 truncate">${img.filename}</p></div>`;
                        });
                        imagesHtml += `</div>`;
                    }
                    window.openCommonDetailModal('Detail Data Database', `
                        <div class="detail-item">
                            <label>ID Database:</label><p>${database.id_database}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label><p class="prose max-w-none">${database.keterangan || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Relasi:</label><p class="prose max-w-none">${database.relasi || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Gambar Database:</label>${imagesHtml}
                        </div>
                    `);
                } else {
                    notificationManager.showNotification('Detail data Database tidak ditemukan.', 'error');
                }
            } else if (editBtn) {
                const databaseId = parseInt(editBtn.dataset.id);
                const database = window.APP_BLADE_DATA.singleUseCase.database_data.find(item => item.id_database === databaseId);
                if (database) {
                    openDatabaseDataModal('edit', database);
                } else {
                    notificationManager.showNotification('Data Database yang ingin diedit tidak ditemukan.', 'error');
                }
            } else if (deleteBtn) {
                const databaseId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data Database ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data Database...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_DESTROY}/${databaseId}`, { method: 'DELETE' });
                        notificationManager.hideNotification(loadingNotif);
                        notificationManager.showCentralSuccessPopup(data.success);
                        window.location.reload();
                    } catch (error) {
                        notificationManager.hideNotification(loadingNotif);
                    }
                });
            }
        });
    }

    domUtils.addEventListener(databaseDataForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan data Database...', 'loading');
        const databaseId = databaseDataFormId.value;
        const method = databaseDataFormMethod.value;
        let url = databaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_UPDATE}/${databaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_STORE;
        let httpMethod = 'POST';

        const formData = new FormData(databaseDataForm);

        existingDatabaseImagesContainer.querySelectorAll('input[type="hidden"]').forEach(input => {
            formData.append(input.name, input.value);
        });

        try {
            const options = {
                method: httpMethod,
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeDatabaseDataModal();
            window.location.reload();
        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
        }
    });

    // Fungsi untuk membuat elemen pratinjau gambar dengan tombol hapus
    function createImagePreviewElement(src, filename, imageId) {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative group border rounded-md p-1';
        wrapper.innerHTML = `
            <img src="${src}" alt="${filename}" class="w-full h-24 object-cover rounded-sm">
            <p class="text-xs text-gray-600 truncate mt-1">${filename}</p>
            <button type="button" class="absolute top-0 right-0 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn" data-image-id="${imageId}" data-image-path="${src}">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="existing_database_images[]" value="${src}">
        `;

        const removeButton = wrapper.querySelector('.remove-image-btn');
        domUtils.addEventListener(removeButton, 'click', () => {
            wrapper.remove();
            currentDatabaseImages = currentDatabaseImages.filter(path => path !== src);
            const hiddenInputToRemove = existingDatabaseImagesContainer.querySelector(`input[value="${src}"]`);
            if(hiddenInputToRemove) hiddenInputToRemove.remove();
        });
        return wrapper;
    }
}
