import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseDetailDisplay() {
    const editSingleUseCaseBtn = domUtils.getElement('editSingleUseCaseBtn');
    const useCaseListTableBody = domUtils.getElement('useCaseListTableBody');

    // Inisialisasi dropdown di tabel use case (use_case_list.blade.php)
    if (useCaseListTableBody) {
        domUtils.addEventListener(useCaseListTableBody, 'click', (e) => {
            const editBtn = e.target.closest('.edit-usecase-btn');
            if (editBtn) {
                const useCaseId = parseInt(editBtn.dataset.id);
                const useCase = window.APP_BLADE_DATA.useCases.find(uc => uc.id === useCaseId);
                if (useCase) {
                    window.openUseCaseModal('edit', useCase);
                } else {
                    notificationManager.showNotification('Data tindakan tidak ditemukan.', 'error');
                }
            }

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
                        window.location.reload();
                    } catch (error) {
                        notificationManager.hideNotification(loadingNotif);
                    }
                });
            }
        });
    }

    // Handle Edit Use Case dari halaman detail (use_case_detail.blade.php)
    if (editSingleUseCaseBtn) {
        domUtils.addEventListener(editSingleUseCaseBtn, 'click', () => {
            // PERBAIKAN: Pastikan data yang dikirim sudah lengkap dengan relasi yang dimuat dari backend.
            // Data `window.APP_BLADE_DATA.singleUseCase` seharusnya sudah dimuat relasinya di controller.
            const singleUseCaseData = window.APP_BLADE_DATA.singleUseCase;
            if (singleUseCaseData && singleUseCaseData.id) {
                window.openUseCaseModal('edit', singleUseCaseData);
            } else {
                notificationManager.showNotification('Data use case tidak ditemukan.', 'error');
            }
        });
    }
}
