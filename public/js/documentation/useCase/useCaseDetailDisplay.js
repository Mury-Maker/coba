import { domUtils } from '../../core/domUtils.js';
import { notificationManager } from '../../core/notificationManager.js';

function handleEditSingleButton() {
    // Menetapkan modalOrigin sebelum membuka modal
    window.modalOrigin = 'use_case_detail';

    const singleUseCaseData = window.APP_BLADE_DATA.singleUseCase;
    if (singleUseCaseData && singleUseCaseData.id) {
        window.openUseCaseModal('edit', singleUseCaseData);
    } else {
        notificationManager.showNotification('Data use case tidak ditemukan.', 'error');
    }
}

export function initUseCaseDetailDisplay() {
    const editSingleUseCaseBtn = domUtils.getElement('editSingleUseCaseBtn');

    if (editSingleUseCaseBtn) {
        domUtils.addEventListener(editSingleUseCaseBtn, 'click', handleEditSingleButton);
    }
}