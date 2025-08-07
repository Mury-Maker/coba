import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

export function initUseCaseDetailDisplay() {
    const editSingleUseCaseBtn = domUtils.getElement('editSingleUseCaseBtn');
    const useCaseListTableBody = domUtils.getElement('useCaseListTableBody');

    // 1. Halaman use_case_list → buka modal
        if (useCaseListTableBody) {
            domUtils.addEventListener(useCaseListTableBody, 'click', (e) => {
                const editBtn = e.target.closest('.edit-usecase-btn-list'); // pastikan class khusus list
                if (editBtn) {
                    const useCaseId = parseInt(editBtn.dataset.id);
                    const useCase = window.APP_BLADE_DATA.useCases.find(uc => uc.id === useCaseId);
                    if (useCase) {
                        window.openUseCaseModal('edit', useCase);
                    }
                }
            });
        }

    // 2. Halaman use_case_detail → buka modal
    if (editSingleUseCaseBtn) {
        domUtils.addEventListener(editSingleUseCaseBtn, 'click', () => {
            const singleUseCaseData = window.APP_BLADE_DATA.singleUseCase;
            if (singleUseCaseData && singleUseCaseData.id) {
                window.openUseCaseModal('edit', singleUseCaseData);
            }
        });
    }

    // 3. Global redirect tombol .edit-usecase-btn (selain detail)
    domUtils.addEventListener(document, 'click', (e) => {
        const editBtn = e.target.closest('.edit-usecase-btn');

        // Jangan eksekusi jika tombol di halaman use_case_detail (ditandai oleh ID editSingleUseCaseBtn)
        if (editBtn && !document.getElementById('editSingleUseCaseBtn')) {
            const useCaseId = parseInt(editBtn.dataset.id);
            const category = editBtn.dataset.category;
            const page = editBtn.dataset.page;

            if (useCaseId && category && page) {
                const redirectUrl = `/docs/${category}/${page}?editUseCaseId=${useCaseId}`;
                window.location.href = redirectUrl;
            }
        }
    });
}

