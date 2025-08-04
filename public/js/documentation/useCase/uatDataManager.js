import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';
// Cukup satu kali import, dan pastikan 'selectedFilesMap' diimpor
import { initImagePreviewer, selectedFilesMap } from '../../utils/imagePreviewer.js';

export function initUatDataManager() {
    const uatDataModal = domUtils.getElement('uatDataModal');
    const uatDataModalTitle = domUtils.getElement('uatDataModalTitle');
    const uatDataForm = domUtils.getElement('uatDataForm');
    const uatDataFormUseCaseId = domUtils.getElement('uatDataFormUseCaseId');
    const uatDataFormId = domUtils.getElement('uatDataFormId');
    const uatDataFormMethod = domUtils.getElement('uatDataFormMethod');
    const cancelUatDataFormBtn = domUtils.getElement('cancelUatDataFormBtn');
    const addUatDataBtn = domUtils.getElement('addUatDataBtn');
    const uatDataTableBody = domUtils.getElement('uatDataTableBody');

    const formUatNamaProsesUsecase = domUtils.getElement('form_uat_nama_proses_usecase');
    const formUatKeterangan = domUtils.getElement('form_uat_keterangan');
    const formUatStatus = domUtils.getElement('form_uat_status');
    const formUatImagesInput = domUtils.getElement('form_uat_images');
    const formUatImagesPreview = domUtils.getElement('form_uat_images_preview');

    function openUatDataModal(mode, uatData = null) {
        if (!uatDataForm) {
            notificationManager.showNotification("Elemen 'uatDataForm' tidak ditemukan.", "error");
            return;
        }

        initImagePreviewer(formUatImagesInput, formUatImagesPreview);

        uatDataForm.reset();
        formUatImagesPreview.innerHTML = '';

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data UAT.', 'error');
            return;
        }
        uatDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            uatDataModalTitle.textContent = 'Tambah Data UAT Baru';
            uatDataFormMethod.value = 'POST';
            uatDataFormId.value = '';
            if (window.APP_BLADE_DATA.singleUseCase) {
                formUatNamaProsesUsecase.value = window.APP_BLADE_DATA.singleUseCase.nama_proses || '';
            }
            formUatKeterangan.value = '';
            formUatStatus.value = '';
            if (formUatImagesInput) formUatImagesInput.value = '';
        } else if (mode === 'edit' && uatData) {
            uatDataModalTitle.textContent = 'Edit Data UAT';
            uatDataFormMethod.value = 'PUT';
            uatDataFormId.value = uatData.id_uat;

            formUatNamaProsesUsecase.value = uatData.nama_proses_usecase || '';
            formUatKeterangan.value = uatData.keterangan_uat || '';
            formUatStatus.value = uatData.status_uat || '';
            if (formUatImagesInput) formUatImagesInput.value = '';

            if (uatData.images && uatData.images.length > 0) {
                uatData.images.forEach(image => {
                    const previewElement = window.createImagePreviewElement(image.path, image.filename, image.id, false, formUatImagesInput);
                    formUatImagesPreview.appendChild(previewElement);
                });
            } else {
                formUatImagesPreview.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar lama.</span>';
            }
        }
        domUtils.toggleModal(uatDataModal, true);
    }

    function closeUatDataModal() {
        domUtils.toggleModal(uatDataModal, false);
        uatDataForm.reset();
        formUatImagesPreview.innerHTML = '';
        if (formUatImagesInput) formUatImagesInput.value = '';
    }

    domUtils.addEventListener(cancelUatDataFormBtn, 'click', closeUatDataModal);

    if (addUatDataBtn) {
        domUtils.addEventListener(addUatDataBtn, 'click', () => {
            openUatDataModal('create');
        });
    }

    if (uatDataTableBody) {
        domUtils.addEventListener(uatDataTableBody, 'click', async (e) => {
            const viewBtn = e.target.closest('.btn-action.bg-blue-500');
            const editBtn = e.target.closest('.edit-uat-btn');
            const deleteBtn = e.target.closest('.delete-uat-btn');

            if (viewBtn) {
                // onclick inline di Blade sudah menangani ini
            } else if (editBtn) {
                const uatId = parseInt(editBtn.dataset.id);
                const uat = (window.APP_BLADE_DATA.singleUseCase?.uat_data || []).find(item => item.id_uat === uatId);
                if (uat) {
                    openUatDataModal('edit', uat);
                } else {
                    notificationManager.showNotification('Data UAT yang ingin diedit tidak ditemukan.', 'error');
                }
            } else if (deleteBtn) {
                const uatId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data UAT ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data UAT...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.UAT_DESTROY}/${uatId}`, { method: 'DELETE' });
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

    domUtils.addEventListener(uatDataForm, 'submit', async (e) => {
        e.preventDefault();

        // Validasi total file dihilangkan. Validasi sekarang hanya ada pada batch upload baru di `imagePreviewer.js`

        const loadingNotif = notificationManager.showNotification('Menyimpan data UAT...', 'loading');
        const uatId = uatDataFormId.value;
        const method = uatDataFormMethod.value;

        let url = (method === 'PUT')
            ? `${APP_CONSTANTS.API_ROUTES.USECASE.UAT_UPDATE}/${uatId}`
            : APP_CONSTANTS.API_ROUTES.USECASE.UAT_STORE;

        const formData = new FormData(uatDataForm);
        formData.append('_method', method);

        // Ambil gambar lama yang dipertahankan
        uatDataForm.querySelectorAll('.existing-image-preview').forEach(wrapper => {
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                formData.append('uat_images_current[]', hiddenInput.value);
            }
        });

        // Tambahkan file dari Map ke FormData (ini hanya berisi file-file baru yang diunggah)
        selectedFilesMap.forEach((file) => {
            formData.append('uat_images[]', file);
        });

        try {
            const options = {
                method: 'POST', // Gunakan POST untuk mengirim FormData dengan file
                body: formData,
            };

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUatDataModal();
            window.location.reload();
        } catch (error) {
            console.error('Gagal menyimpan/memperbarui data UAT: ' + error.message);
            notificationManager.hideNotification(loadingNotif);
        }
    });
}
