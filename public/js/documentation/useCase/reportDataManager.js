// public/js/documentation/useCase/reportDataManager.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initReportDataManager() {
    const reportDataModal = domUtils.getElement('reportDataModal');
    const reportDataModalTitle = domUtils.getElement('reportDataModalTitle');
    const reportDataForm = domUtils.getElement('reportDataForm');
    const reportDataFormUseCaseId = domUtils.getElement('reportDataFormUseCaseId');
    const reportDataFormId = domUtils.getElement('reportDataFormId');
    const reportDataFormMethod = domUtils.getElement('reportDataFormMethod');
    const cancelReportDataFormBtn = domUtils.getElement('cancelReportDataFormBtn');
    const addReportDataBtn = domUtils.getElement('addReportDataBtn'); // Tombol "Tambah" di halaman detail use case
    const reportDataTableBody = domUtils.getElement('reportDataTableBody'); // Tabel daftar Report di halaman detail use case

    const formReportAktor = domUtils.getElement('form_report_aktor');
    const formReportNama = domUtils.getElement('form_report_nama');
    const formReportKeterangan = domUtils.getElement('form_report_keterangan');

    /**
     * Membuka modal Report Data.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [reportData=null] - Objek Report Data untuk mode edit.
     */
    function openReportDataModal(mode, reportData = null) {
        if (!reportDataForm) {
            notificationManager.showNotification("Elemen 'reportDataForm' tidak ditemukan.", "error");
            return;
        }

        reportDataForm.reset();
        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Report.', 'error');
            return;
        }
        reportDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            reportDataModalTitle.textContent = 'Tambah Data Report Baru';
            reportDataFormMethod.value = 'POST';
            reportDataFormId.value = '';
            formReportAktor.value = '';
            formReportNama.value = '';
            formReportKeterangan.value = '';
        } else if (mode === 'edit' && reportData) {
            reportDataModalTitle.textContent = 'Edit Data Report';
            reportDataFormMethod.value = 'PUT';
            reportDataFormId.value = reportData.id_report;

            formReportAktor.value = reportData.aktor || '';
            formReportNama.value = reportData.nama_report || '';
            formReportKeterangan.value = reportData.keterangan || '';
        }
        domUtils.toggleModal(reportDataModal, true);
    }

    /**
     * Menutup modal Report Data.
     */
    function closeReportDataModal() {
        domUtils.toggleModal(reportDataModal, false);
        reportDataForm.reset();
    }

    domUtils.addEventListener(cancelReportDataFormBtn, 'click', closeReportDataModal);

    // Event listener untuk tombol "Tambah" di halaman detail use case
    if (addReportDataBtn) {
        domUtils.addEventListener(addReportDataBtn, 'click', () => {
            openReportDataModal('create');
        });
    }

    // Delegasi event untuk tombol Edit dan Delete di tabel Report
    if (reportDataTableBody) {
        domUtils.addEventListener(reportDataTableBody, 'click', async (e) => {
            const viewBtn = e.target.closest('.btn-action.bg-blue-500'); // Tombol detail
            const editBtn = e.target.closest('.edit-report-btn');
            const deleteBtn = e.target.closest('.delete-report-btn');

            if (viewBtn) {
                const reportId = parseInt(viewBtn.dataset.id);
                const report = window.APP_BLADE_DATA.singleUseCase.report_data.find(item => item.id_report === reportId);
                if (report) {
                    window.openCommonDetailModal('Detail Data Report', `
                        <div class="detail-item">
                            <label>ID Report:</label><p>${report.id_report}</p>
                        </div>
                        <div class="detail-item">
                            <label>Aktor:</label><p>${report.aktor || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Nama Report:</label><p>${report.nama_report || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label><p class="prose max-w-none">${report.keterangan || 'N/A'}</p>
                        </div>
                    `);
                } else {
                    notificationManager.showNotification('Detail data Report tidak ditemukan.', 'error');
                }
            } else if (editBtn) {
                const reportId = parseInt(editBtn.dataset.id);
                const report = window.APP_BLADE_DATA.singleUseCase.report_data.find(item => item.id_report === reportId);
                if (report) {
                    openReportDataModal('edit', report);
                } else {
                    notificationManager.showNotification('Data Report yang ingin diedit tidak ditemukan.', 'error');
                }
            } else if (deleteBtn) {
                const reportId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data Report ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data Report...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.REPORT_DESTROY}/${reportId}`, { method: 'DELETE' });
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

    domUtils.addEventListener(reportDataForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan data Report...', 'loading');
        const reportId = reportDataFormId.value;
        const method = reportDataFormMethod.value;
        let url = reportId ? `${APP_CONSTANTS.API_ROUTES.USECASE.REPORT_UPDATE}/${reportId}` : APP_CONSTANTS.API_ROUTES.USECASE.REPORT_STORE;
        let httpMethod = 'POST'; // Akan selalu POST untuk FormData

        const formData = new FormData(reportDataForm);

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
            closeReportDataModal();
            window.location.reload();
        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
        }
    });
}
