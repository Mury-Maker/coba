import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseFormHandler() {
    console.log('initUseCaseFormHandler dipanggil.');
    const useCaseModal = domUtils.getElement('useCaseModal');
    const useCaseModalTitle = domUtils.getElement('useCaseModalTitle');
    const useCaseForm = domUtils.getElement('useCaseForm');
    const useCaseFormMenuId = domUtils.getElement('useCaseFormMenuId');
    const useCaseFormUseCaseId = domUtils.getElement('useCaseFormUseCaseId');
    const useCaseFormMethod = domUtils.getElement('useCaseFormMethod');
    const cancelUseCaseFormBtn = domUtils.getElement('cancelUseCaseFormBtn');

    window.openUseCaseModal = async (mode, useCase = null) => {
        console.log('openUseCaseModal dipanggil. Mode:', mode, 'UseCase:', useCase);
        if (!useCaseModal || !useCaseModalTitle || !useCaseForm) {
            notificationManager.showNotification("Elemen 'useCaseForm' tidak ditemukan.", "error");
            console.error("Use Case modal elements are missing.");
            return;
        }

        useCaseForm.reset();
        useCaseFormMenuId.value = window.APP_BLADE_DATA.currentMenuId;

        if (mode === 'create') {
            useCaseModalTitle.textContent = 'Tambah Tindakan Baru';
            useCaseFormMethod.value = 'POST';
            useCaseFormUseCaseId.value = '';
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
            useCaseFormMethod.value = 'PUT';
            useCaseFormUseCaseId.value = useCase.id;

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

    function closeUseCaseModal() {
        domUtils.toggleModal(useCaseModal, false);
        useCaseForm.reset();
        console.log('Use Case modal closed and form reset.');
    }

    domUtils.addEventListener(cancelUseCaseFormBtn, 'click', closeUseCaseModal);

    // Menambahkan event listener untuk menutup modal saat klik di luar form
    domUtils.addEventListener(document, 'click', (e) => {
        // Memeriksa apakah yang diklik adalah modal itu sendiri, bukan konten di dalamnya
        if (e.target === useCaseModal) {
            closeUseCaseModal();
        }
    });

    domUtils.addEventListener(useCaseForm, 'submit', async (e) => {
        e.preventDefault();
        console.log('Form Use Case disubmit.');

        const loadingNotif = notificationManager.showNotification('Menyimpan tindakan...', 'loading');
        const method = useCaseFormMethod.value;
        const useCaseId = useCaseFormUseCaseId.value;
        let url = useCaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.UPDATE}/${useCaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.STORE;
        let httpMethod = 'POST';

        const formData = new FormData(useCaseForm);

        console.log('Sending API request:', url, 'Method:', httpMethod, 'Data:', Object.fromEntries(formData));

        try {
            const options = {
                method: httpMethod,
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            console.log('API request berhasil. Respons:', data);
            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUseCaseModal();

            const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug || 'epesantren';
            const currentPageSlug = window.APP_BLADE_DATA.currentPage || 'beranda-epesantren';

            let redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}`;

            if (data.use_case_slug) {
                redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${currentPageSlug}/${data.use_case_slug}`;
            }

            console.log('Redirecting to:', redirectUrl);
            window.location.href = redirectUrl;

        } catch (error) {
            console.error('API request GAGAL:', error);
            notificationManager.hideNotification(loadingNotif);
        }
    });

    domUtils.addEventListener(document, 'click', (e) => {
        const addUseCaseBtn = e.target.closest('#addUseCaseBtn');
        if (addUseCaseBtn) {
            console.log('Add Use Case button clicked.');
            window.openUseCaseModal('create');
        }
    });
}