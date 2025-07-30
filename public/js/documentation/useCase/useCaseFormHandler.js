// public/js/documentation/useCase/useCaseFormHandler.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseFormHandler() {
    const useCaseModal = domUtils.getElement('useCaseModal');
    const useCaseModalTitle = domUtils.getElement('useCaseModalTitle');
    const useCaseForm = domUtils.getElement('useCaseForm');
    const useCaseFormMenuId = domUtils.getElement('useCaseFormMenuId');
    const useCaseFormUseCaseId = domUtils.getElement('useCaseFormUseCaseId');
    const useCaseFormMethod = domUtils.getElement('useCaseFormMethod');
    const cancelUseCaseFormBtn = domUtils.getElement('cancelUseCaseFormBtn');

    /**
     * Membuka modal Use Case (Detail Aksi) untuk tambah atau edit.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [useCase=null] - Objek Use Case untuk mode edit.
     */
    window.openUseCaseModal = (mode, useCase = null) => {
        if (!useCaseForm) {
            notificationManager.showNotification("Elemen 'useCaseForm' tidak ditemukan.", "error");
            return;
        }

        useCaseForm.reset();
        // Gunakan window.APP_BLADE_DATA
        useCaseFormMenuId.value = window.APP_BLADE_DATA.currentMenuId;

        if (mode === 'create') {
            useCaseModalTitle.textContent = 'Tambah Tindakan Baru';
            useCaseFormMethod.value = 'POST';
            useCaseFormUseCaseId.value = '';
            domUtils.getElement('form_usecase_id').value = '';
            // Kosongkan semua textarea
            domUtils.getElement('form_deskripsi_aksi').value = '';
            domUtils.getElement('form_tujuan').value = '';
            domUtils.getElement('form_kondisi_awal').value = '';
            domUtils.getElement('form_kondisi_akhir').value = '';
            domUtils.getElement('form_aksi_reaksi').value = '';
            domUtils.getElement('form_reaksi_sistem').value = '';
        } else if (mode === 'edit' && useCase) {
            useCaseModalTitle.textContent = `Edit Tindakan: ${useCase.nama_proses}`;
            useCaseFormMethod.value = 'PUT';
            useCaseFormUseCaseId.value = useCase.id;

            domUtils.getElement('form_usecase_id').value = useCase.usecase_id || '';
            domUtils.getElement('form_nama_proses').value = useCase.nama_proses || '';
            domUtils.getElement('form_aktor').value = useCase.aktor || '';

            domUtils.getElement('form_deskripsi_aksi').value = useCase.deskripsi_aksi || '';
            domUtils.getElement('form_tujuan').value = useCase.tujuan || '';
            domUtils.getElement('form_kondisi_awal').value = useCase.kondisi_awal || '';
            domUtils.getElement('form_kondisi_akhir').value = useCase.kondisi_akhir || '';
            domUtils.getElement('form_aksi_reaksi').value = useCase.aksi_reaksi || '';
            domUtils.getElement('form_reaksi_sistem').value = useCase.reaksi_sistem || '';
        }
        domUtils.toggleModal(useCaseModal, true);
    };

    /**
     * Menutup modal Use Case.
     */
    function closeUseCaseModal() {
        domUtils.toggleModal(useCaseModal, false);
        useCaseForm.reset();
    }

    domUtils.addEventListener(cancelUseCaseFormBtn, 'click', closeUseCaseModal);

    domUtils.addEventListener(useCaseForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan tindakan...', 'loading');
        const method = useCaseFormMethod.value;
        const useCaseId = useCaseFormUseCaseId.value;
        let url = useCaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.UPDATE}/${useCaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.STORE;
        let httpMethod = 'POST'; // Karena PUT/DELETE API akan menggunakan POST dengan method override

        const formData = new FormData(useCaseForm);

        try {
            const options = {
                method: httpMethod,
                body: formData, // Kirim FormData langsung
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUseCaseModal();

            // Redirect ke halaman detail Use Case yang baru dibuat/diedit
            // Gunakan window.APP_BLADE_DATA
            let redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${window.APP_BLADE_DATA.currentCategorySlug}/${window.APP_BLADE_DATA.currentPage}`;
            if (data.use_case_slug) {
                redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${window.APP_BLADE_DATA.currentCategorySlug}/${window.APP_BLADE_DATA.currentPage}/${data.use_case_slug}`;
            }
            window.location.href = redirectUrl;

        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
            // Error ditangani oleh apiClient
        }
    });

    // Delegasi event untuk tombol "Tambah Data" (di use_case_list.blade.php)
    domUtils.addEventListener(document, 'click', (e) => {
        const addUseCaseBtn = e.target.closest('#addUseCaseBtn');
        if (addUseCaseBtn) {
            window.openUseCaseModal('create');
        }
    });
}
