import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';
// Import 'selectedFilesMap' di sini juga, karena file ini juga menggunakan fitur upload
import { initImagePreviewer, selectedFilesMap } from '../../utils/imagePreviewer.js';
// Perlu dipastikan nama aliasnya berbeda jika ada inisialisasi yang berbeda
import { initImagePreviewer as initImagePreviewerForDatabase } from '../../utils/imagePreviewer.js';

export function initDatabaseDataManager() {
    const databaseDataModal = domUtils.getElement('databaseDataModal');
    const databaseDataModalTitle = domUtils.getElement('databaseDataModalTitle');
    const databaseDataForm = domUtils.getElement('databaseDataForm');
    const databaseDataFormUseCaseId = domUtils.getElement('databaseDataFormUseCaseId');
    const databaseDataFormId = domUtils.getElement('databaseDataFormId');
    const databaseDataFormMethod = domUtils.getElement('databaseDataFormMethod');
    const cancelDatabaseDataFormBtn = domUtils.getElement('cancelDatabaseDataFormBtn');
    const addDatabaseDataBtn = domUtils.getElement('addDatabaseDataBtn');
    const databaseDataTableBody = domUtils.getElement('databaseDataTableBody');

    const formDatabaseKeterangan = domUtils.getElement('form_database_keterangan');
    const formDatabaseRelasi = domUtils.getElement('form_database_relasi');
    const formDatabaseImagesInput = domUtils.getElement('form_database_images');
    const formDatabaseImagesPreview = domUtils.getElement('form_database_images_preview');

    function openDatabaseDataModal(mode, databaseData = null) {
        if (!databaseDataForm) {
            notificationManager.showNotification("Elemen 'databaseDataForm' tidak ditemukan.", "error");
            return;
        }

        initImagePreviewerForDatabase(formDatabaseImagesInput, formDatabaseImagesPreview);

        databaseDataForm.reset();
        formDatabaseImagesPreview.innerHTML = '';

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Database.', "error");
            return;
        }
        databaseDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            databaseDataModalTitle.textContent = 'Tambah Data Database Baru';
            databaseDataFormMethod.value = 'POST';
            databaseDataFormId.value = '';
            formDatabaseKeterangan.value = '';
            formDatabaseRelasi.value = '';
            if (formDatabaseImagesInput) formDatabaseImagesInput.value = '';
        } else if (mode === 'edit' && databaseData) {
            databaseDataModalTitle.textContent = 'Edit Data Database';
            databaseDataFormMethod.value = 'PUT';
            databaseDataFormId.value = databaseData.id_database;

            formDatabaseKeterangan.value = databaseData.keterangan || '';
            formDatabaseRelasi.value = databaseData.relasi || '';
            if (formDatabaseImagesInput) formDatabaseImagesInput.value = '';

            if (databaseData.images && databaseData.images.length > 0) {
                databaseData.images.forEach(image => {
                    const previewElement = window.createImagePreviewElement(image.path, image.filename, image.id, false, formDatabaseImagesInput);
                    formDatabaseImagesPreview.appendChild(previewElement);
                });
            } else {
                formDatabaseImagesPreview.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar lama.</span>';
            }
        }
        domUtils.toggleModal(databaseDataModal, true);
    }

    function closeDatabaseDataModal() {
        domUtils.toggleModal(databaseDataModal, false);
        databaseDataForm.reset();
        formDatabaseImagesPreview.innerHTML = '';
        if (formDatabaseImagesInput) formDatabaseImagesInput.value = '';
    }

    domUtils.addEventListener(cancelDatabaseDataFormBtn, 'click', closeDatabaseDataModal);

    if (addDatabaseDataBtn) {
        domUtils.addEventListener(addDatabaseDataBtn, 'click', () => {
            openDatabaseDataModal('create');
        });
    }

    if (databaseDataTableBody) {
        domUtils.addEventListener(databaseDataTableBody, 'click', async (e) => {
            const viewBtn = e.target.closest('.btn-action.bg-blue-500');
            const editBtn = e.target.closest('.edit-database-btn');
            const deleteBtn = e.target.closest('.delete-database-btn');

            if (viewBtn) {
                // ...
            } else if (editBtn) {
                const databaseId = parseInt(editBtn.dataset.id);
                const database = (window.APP_BLADE_DATA.singleUseCase?.database_data || []).find(item => item.id_database === databaseId);
                if (database) {
                    openDatabaseDataModal('edit', database);
                } else {
                    notificationManager.showNotification('Data Database yang ingin diedit tidak ditemukan di cache.', 'error');
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

        // Validasi total file dihilangkan. Validasi sekarang hanya ada pada batch upload baru di `imagePreviewer.js`

        const loadingNotif = notificationManager.showNotification('Menyimpan data Database...', 'loading');
        const databaseId = databaseDataFormId.value;
        const method = databaseDataFormMethod.value;

        let url = databaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_UPDATE}/${databaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_STORE;

        const formData = new FormData(databaseDataForm);
        formData.append('_method', method);

        databaseDataForm.querySelectorAll('.existing-image-preview').forEach(wrapper => {
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                formData.append('database_images_current[]', hiddenInput.value);
            }
        });

        selectedFilesMap.forEach((file) => {
            formData.append('database_images[]', file);
        });

        try {
            const options = {
                method: 'POST',
                body: formData,
            };

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeDatabaseDataModal();
            window.location.reload();
        } catch (error) {
            console.error('Gagal menyimpan/memperbarui data Database: ' + error.message);
            notificationManager.hideNotification(loadingNotif);
        }
    });
}
