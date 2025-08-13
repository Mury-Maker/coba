// public/js/documentation/useCase/useCaseFormHandler.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

let modalOrigin = null;

function handleFormSubmit(e) {
    e.preventDefault();
    console.log('Form Use Case disubmit.');

    const loadingNotif = notificationManager.showNotification('Menyimpan tindakan...', 'loading');
    const useCaseForm = domUtils.getElement('useCaseForm');
    const useCaseId = domUtils.getElement('useCaseFormUseCaseId').value;
    const method = domUtils.getElement('useCaseFormMethod').value;

    let url;
    if (method === 'PUT' && useCaseId) {
        url = `${APP_CONSTANTS.API_ROUTES.USECASE.UPDATE}/${useCaseId}`;
    } else {
        url = APP_CONSTANTS.API_ROUTES.USECASE.STORE;
    }

    const formData = new FormData(useCaseForm);

    apiClient.fetchAPI(url, {
        method: method === 'PUT' ? 'POST' : method,
        body: formData,
        headers: method === 'PUT' ? { 'X-HTTP-Method-Override': 'PUT' } : undefined,
    }).then(data => {
        notificationManager.hideNotification(loadingNotif);
        notificationManager.showCentralSuccessPopup(data.success);
        domUtils.toggleModal(domUtils.getElement('useCaseModal'), false);

        const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug || 'epesantren';
        const currentPageSlug = window.APP_BLADE_DATA.currentPage || 'beranda-epesantren';

        let redirectUrl;
        if (modalOrigin === 'use_case_list' || !useCaseId) {
            redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}`;
        } else {
            redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}/${data.use_case_slug}`;
        }

        window.location.href = redirectUrl;

    }).catch(error => {
        notificationManager.hideNotification(loadingNotif);
        console.error('API request GAGAL:', error);
    });
}

function handleEditListButton(e) {
    const editBtn = e.target.closest('.edit-usecase-btn');
    if (editBtn) {
        modalOrigin = 'use_case_list';
        const useCaseId = parseInt(editBtn.dataset.id);

        // Perbaikan: Akses data dari properti 'data' pada objek paginasi
        const useCasesArray = window.APP_BLADE_DATA.useCases.data || [];
        const useCase = useCasesArray.find(uc => uc.id === useCaseId);

        if (useCase) {
            window.openUseCaseModal('edit', useCase);
        } else {
            notificationManager.showNotification('Data tindakan tidak ditemukan.', 'error');
        }
    }
}

function handleDeleteListButton(e) {
    const deleteBtn = e.target.closest('.delete-usecase-btn');
    if (deleteBtn) {
        const useCaseId = parseInt(deleteBtn.dataset.id);
        const useCaseNama = deleteBtn.dataset.nama;
        window.openCommonConfirmModal(`Yakin ingin menghapus tindakan "${useCaseNama}"? Semua data UAT, Report, dan Database terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan!`, async () => {
            const loadingNotif = notificationManager.showNotification('Menghapus tindakan...', 'loading');
            try {
                await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.DESTROY}/${useCaseId}`, { method: 'DELETE' });
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup('Tindakan berhasil dihapus.');

                const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug || 'epesantren';
                const currentPageSlug = window.APP_BLADE_DATA.currentPage || 'beranda-epesantren';
                window.location.href = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}`;
            } catch (error) {
                notificationManager.hideNotification(loadingNotif);
            }
        });
    }
}

export function initUseCaseFormHandler() {
    console.log('initUseCaseFormHandler dipanggil.');
    const useCaseModal = domUtils.getElement('useCaseModal');
    const useCaseModalTitle = domUtils.getElement('useCaseModalTitle');
    const useCaseForm = domUtils.getElement('useCaseForm');
    const cancelUseCaseFormBtn = domUtils.getElement('cancelUseCaseFormBtn');

    window.openUseCaseModal = async (mode, useCase = null) => {
        console.log('openUseCaseModal dipanggil. Mode:', mode, 'UseCase:', useCase);
        if (!useCaseModal || !useCaseModalTitle || !useCaseForm) {
            notificationManager.showNotification("Elemen 'useCaseForm' tidak ditemukan.", "error");
            console.error("Use Case modal elements are missing.");
            return;
        }

        useCaseForm.reset();
        domUtils.getElement('useCaseFormMenuId').value = window.APP_BLADE_DATA.currentMenuId;

        if (mode === 'create') {
            useCaseModalTitle.textContent = 'Tambah Tindakan Baru';
            domUtils.getElement('useCaseFormMethod').value = 'POST';
            domUtils.getElement('useCaseFormUseCaseId').value = '';
            domUtils.getElement('form_nama_proses').value = '';
            domUtils.getElement('form_aktor').value = '';
            domUtils.getElement('form_deskripsi_aksi').value = '';
            domUtils.getElement('form_tujuan').value = '';
            domUtils.getElement('form_kondisi_awal').value = '';
            domUtils.getElement('form_kondisi_akhir').value = '';
            domUtils.getElement('form_aksi_aktor').value = '';
            domUtils.getElement('form_reaksi_sistem').value = '';
        } else if (mode === 'edit' && useCase) {
            useCaseModalTitle.textContent = `Edit Tindakan: ${useCase.nama_proses}`;
            domUtils.getElement('useCaseFormMethod').value = 'PUT';
            domUtils.getElement('useCaseFormUseCaseId').value = useCase.id;

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
        console.log('Use Case modal toggled to show.');
    };

    domUtils.addEventListener(cancelUseCaseFormBtn, 'click', () => domUtils.toggleModal(useCaseModal, false));
    domUtils.addEventListener(document, 'click', (e) => {
        if (e.target === useCaseModal) domUtils.toggleModal(useCaseModal, false);
    });

    domUtils.addEventListener(useCaseForm, 'submit', handleFormSubmit);

    // Menambahkan event listener ke tombol-tombol yang relevan
    const addUseCaseBtn = domUtils.getElement('addUseCaseBtn');
    if (addUseCaseBtn) {
        domUtils.addEventListener(addUseCaseBtn, 'click', () => {
            modalOrigin = 'use_case_list';
            window.openUseCaseModal('create');
        });
    }

    domUtils.addEventListener(document, 'click', handleEditListButton);
    domUtils.addEventListener(document, 'click', handleDeleteListButton);
}
