// public/js/documentation/useCase/useCaseDetailDisplay.js

import { domUtils } from '../../core/domUtils.js';
import { notificationManager } from '../../core/notificationManager.js';

function handleEditSingleButton() {
    // Menetapkan modalOrigin sebelum membuka modal
    window.modalOrigin = 'use_case_detail';

    // Memuat ulang data use case terbaru dari server sebelum membuka modal
    const useCaseId = window.APP_BLADE_DATA.singleUseCase.id;
    if (useCaseId) {
        // Ambil data use case dari API endpoint yang sesuai (jika ada) atau dari DOM jika tidak ada
        // Untuk saat ini, kita akan langsung menggunakan data dari blade
        const singleUseCaseData = window.APP_BLADE_DATA.singleUseCase;
        if (singleUseCaseData) {
            window.openUseCaseModal('edit', singleUseCaseData);
        } else {
            notificationManager.showNotification('Data use case tidak ditemukan.', 'error');
        }
    } else {
        notificationManager.showNotification('Data use case tidak ditemukan.', 'error');
    }
}

export function initUseCaseDetailDisplay() {
    console.log('initUseCaseDetailDisplay dipanggil.');
    const editSingleUseCaseBtn = domUtils.getElement('editSingleUseCaseBtn');

    if (editSingleUseCaseBtn) {
        domUtils.addEventListener(editSingleUseCaseBtn, 'click', handleEditSingleButton);
    }
}
