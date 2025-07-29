// public/js/documentation/useCase/useCaseDetailDisplay.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseDetailDisplay() {
    const editSingleUseCaseBtn = domUtils.getElement('editSingleUseCaseBtn');
    const useCaseListTableBody = domUtils.getElement('useCaseListTableBody'); // Untuk halaman daftar use case

    // Inisialisasi dropdown di tabel use case (use_case_list.blade.php)
    if (useCaseListTableBody) {
        domUtils.addEventListener(useCaseListTableBody, 'click', (e) => {
            // Handle Edit Use Case dari daftar
            const editBtn = e.target.closest('.edit-usecase-btn');
            if (editBtn) {
                const useCaseId = parseInt(editBtn.dataset.id);
                // Cari data use case dari window.APP_BLADE_DATA.useCases
                const useCase = window.APP_BLADE_DATA.useCases.find(uc => uc.id === useCaseId);
                if (useCase) {
                    window.openUseCaseModal('edit', useCase); // Memanggil fungsi dari useCaseFormHandler.js
                } else {
                    notificationManager.showNotification('Data tindakan tidak ditemukan.', 'error');
                }
            }

            // Handle Delete Use Case dari daftar
            const deleteBtn = e.target.closest('.delete-usecase-btn');
            if (deleteBtn) {
                const useCaseId = parseInt(deleteBtn.dataset.id);
                const useCaseNama = deleteBtn.dataset.nama;
                window.openCommonConfirmModal(`Yakin ingin menghapus tindakan "${useCaseNama}"? Semua data UAT, Report, dan Database terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan!`, async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus tindakan...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.DESTROY}/${useCaseId}`, { method: 'DELETE' });
                        notificationManager.hideNotification(loadingNotif);
                        notificationManager.showCentralSuccessPopup(data.success);
                        window.location.reload(); // Reload halaman untuk memperbarui daftar
                    } catch (error) {
                        notificationManager.hideNotification(loadingNotif);
                        // Error ditangani oleh apiClient
                    }
                });
            }
        });
    }

    // Handle Edit Use Case dari halaman detail (use_case_detail.blade.php)
    if (editSingleUseCaseBtn) {
        domUtils.addEventListener(editSingleUseCaseBtn, 'click', () => {
            const singleUseCaseData = window.APP_BLADE_DATA.singleUseCase;
            if (singleUseCaseData && singleUseCaseData.id) {
                window.openUseCaseModal('edit', singleUseCaseData); // Memanggil fungsi dari useCaseFormHandler.js
            } else {
                notificationManager.showNotification('Data use case tidak ditemukan.', 'error');
            }
        });
    }
}
