// public/js/documentation/useCase/useCaseFormHandler.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseFormHandler() {
    console.log('initUseCaseFormHandler dipanggil.'); // DEBUG
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
    window.openUseCaseModal = async (mode, useCase = null) => {
        console.log('openUseCaseModal dipanggil. Mode:', mode, 'UseCase:', useCase); // DEBUG
        if (!useCaseModal || !useCaseModalTitle || !useCaseForm) {
            notificationManager.showNotification("Elemen 'useCaseForm' tidak ditemukan.", "error");
            console.error("Use Case modal elements are missing."); // DEBUG
            return;
        }

        useCaseForm.reset();
        // Gunakan window.APP_BLADE_DATA untuk mengisi menu_id
        useCaseFormMenuId.value = window.APP_BLADE_DATA.currentMenuId;

        if (mode === 'create') {
            useCaseModalTitle.textContent = 'Tambah Tindakan Baru';
            useCaseFormMethod.value = 'POST';
            useCaseFormUseCaseId.value = '';
            domUtils.getElement('form_usecase_id').value = '';
            // Kosongkan semua textarea (CKEditor tidak digunakan, jadi langsung textarea)
            domUtils.getElement('form_deskripsi_aksi').value = '';
            domUtils.getElement('form_tujuan').value = '';
            domUtils.getElement('form_kondisi_awal').value = '';
            domUtils.getElement('form_kondisi_akhir').value = '';
            domUtils.getElement('form_aksi_aktor').value = '';
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
            domUtils.getElement('form_aksi_aktor').value = useCase.aksi_aktor || '';
            domUtils.getElement('form_reaksi_sistem').value = useCase.reaksi_sistem || '';
        }
        domUtils.toggleModal(useCaseModal, true);
        console.log('Use Case modal toggled to show.'); // DEBUG
    };

    /**
     * Menutup modal Use Case.
     */
    function closeUseCaseModal() {
        domUtils.toggleModal(useCaseModal, false);
        useCaseForm.reset();
        console.log('Use Case modal closed and form reset.'); // DEBUG
    }

    domUtils.addEventListener(cancelUseCaseFormBtn, 'click', closeUseCaseModal);

    domUtils.addEventListener(useCaseForm, 'submit', async (e) => {
        e.preventDefault();
        console.log('Form Use Case disubmit.'); // DEBUG

        const loadingNotif = notificationManager.showNotification('Menyimpan tindakan...', 'loading');
        const method = useCaseFormMethod.value;
        const useCaseId = useCaseFormUseCaseId.value;
        let url = useCaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.UPDATE}/${useCaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.STORE;
        let httpMethod = 'POST';

        const formData = new FormData(useCaseForm);

        console.log('Sending API request:', url, 'Method:', httpMethod, 'Data:', Object.fromEntries(formData)); // DEBUG

        try {
            const options = {
                method: httpMethod,
                body: formData, // Kirim FormData langsung
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            console.log('API request berhasil. Respons:', data); // DEBUG
            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUseCaseModal();

            // === PERBAIKAN KRITIS UNTUK REDIRECT ===
            // Pastikan APP_BLADE_DATA terdefinisi, dengan fallback yang aman
            const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug || 'epesantren';
            const currentPageSlug = window.APP_BLADE_DATA.currentPage || 'beranda-epesantren'; // Gunakan nama halaman default yang ada kontennya

            let redirectUrl;
            if (data.use_case_slug) {
                // Jika use_case_slug ada di respons, redirect ke halaman detail use case yang baru dibuat/diedit
                redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}`;
            } else {
                // Jika tidak ada use_case_slug (misalnya untuk update yang tidak mengubah slug, atau hanya kembali ke daftar)
                // Redirect ke halaman daftar use case (menu saat ini)
                redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}`;
            }

            console.log('Redirecting to:', redirectUrl); // DEBUG
            window.location.href = redirectUrl;

        } catch (error) {
            console.error('API request GAGAL:', error); // DEBUG
            notificationManager.hideNotification(loadingNotif);
            // Error ditangani oleh apiClient
        }
    });

    // Delegasi event untuk tombol "Tambah Data" (di use_case_list.blade.php)
    domUtils.addEventListener(document, 'click', (e) => {
        // Gunakan e.target.closest untuk mencari elemen terdekat dengan ID/selector
        const addUseCaseBtn = e.target.closest('#addUseCaseBtn');
        if (addUseCaseBtn) { // Pastikan tombol ditemukan
            console.log('Add Use Case button clicked.'); // DEBUG
            window.openUseCaseModal('create');
        }
    });
}
