// public/js/core/notificationManager.js

import { domUtils } from './domUtils.js';

// Fungsi internal untuk notifikasi toast
function showNotificationInternal(message, type = 'success', duration = 3000) {
    const container = domUtils.getElement('notification-container');
    if (!container) {
        console.error('Notification container not found!');
        return;
    }

    const notifId = 'notif-' + Date.now();
    const notifDiv = document.createElement('div');
    notifDiv.id = notifId;
    notifDiv.className = `notification-message ${type}`;

    let iconClass = '';
    let timeoutDuration = duration;
    let delayBeforeShow = 0;

    if (type === 'success') {
        iconClass = 'fa-solid fa-check-circle text-green-500';
        timeoutDuration = 3000;
    } else if (type === 'error') {
        iconClass = 'fa-solid fa-times-circle text-red-700';
    } else if (type === 'loading') {
        iconClass = 'fa-solid fa-spinner fa-spin text-blue-700';
        timeoutDuration = 0;
        delayBeforeShow = 500;
    }

    notifDiv.innerHTML = `<i class="notification-icon ${iconClass}"></i> ${message}`;

    const showTimeoutId = setTimeout(() => {
        container.appendChild(notifDiv);
        setTimeout(() => domUtils.toggleClass(notifDiv, 'show', true), 10);
    }, delayBeforeShow);

    if (timeoutDuration > 0) {
        setTimeout(() => {
            domUtils.toggleClass(notifDiv, 'show', false);
            setTimeout(() => notifDiv.remove(), 500);
        }, timeoutDuration + delayBeforeShow);
    }

    return { notifId: notifId, showTimeoutId: showTimeoutId, element: notifDiv };
}

// Fungsi internal untuk menyembunyikan notifikasi toast
function hideNotificationInternal(notifInfo) {
    let notifElement;
    let showTimeoutId;

    if (typeof notifInfo === 'string') {
        notifElement = domUtils.getElement(notifInfo);
    } else if (typeof notifInfo === 'object' && notifInfo !== null) {
        notifElement = notifInfo.element;
        showTimeoutId = notifInfo.showTimeoutId;
    }

    if (showTimeoutId) {
        clearTimeout(showTimeoutId);
    }

    if (notifElement && notifElement.parentNode) {
        domUtils.toggleClass(notifElement, 'show', false);
        setTimeout(() => notifElement.remove(), 500);
    }
}

// Fungsi internal untuk menampilkan pop-up sukses di tengah layar.
function showCentralSuccessPopupInternal(message) {
    const centralSuccessPopup = domUtils.getElement('commonSuccessPopup');
    const centralPopupMessage = domUtils.getElement('common-popup-message');

    if (centralPopupMessage) {
        centralPopupMessage.textContent = message;
    }
    if (centralSuccessPopup) {
        domUtils.toggleModal(centralSuccessPopup, true);
        setTimeout(() => {
            domUtils.toggleModal(centralSuccessPopup, false);
        }, 1000);
    }
}

// --- Objek notificationManager yang diekspor ---
// Ini adalah objek yang akan digunakan oleh modul lain untuk memanggil fungsi notifikasi/modal.
export const notificationManager = {
    showNotification: showNotificationInternal,
    hideNotification: hideNotificationInternal,
    showCentralSuccessPopup: showCentralSuccessPopupInternal,

    openConfirmModal: (message, onConfirm) => {
        const commonConfirmModal = domUtils.getElement('commonConfirmModal');
        const commonConfirmMessage = domUtils.getElement('common-confirm-message');
        const commonCancelBtn = domUtils.getElement('common-cancel-btn');
        const commonConfirmBtn = domUtils.getElement('common-confirm-btn');
        let confirmCallback = onConfirm;

        console.log('notificationManager.openConfirmModal dipanggil.');
        if (!commonConfirmModal || !commonConfirmMessage || !commonCancelBtn || !commonConfirmBtn) {
            console.error("Elemen modal konfirmasi tidak ditemukan di notificationManager.openConfirmModal.");
            return;
        }
        commonConfirmMessage.textContent = message;
        domUtils.toggleModal(commonConfirmModal, true);

        // --- PERBAIKAN KRITIS DI SINI ---
        // Tambahkan event listener untuk menghentikan propagasi pada konten modal itu sendiri
        const modalContent = commonConfirmModal.querySelector('.modal-content');
        const handleModalContentClick = (e) => {
            e.stopPropagation(); // Pastikan klik di dalam konten modal tidak menyebar
            console.log('Click inside confirm modal content stopped propagation.'); // DEBUG
        };

        // Hapus listener sebelumnya jika ada (untuk mencegah duplikasi)
        // Ini perlu fungsi referensi yang sama untuk remove, jadi kita harus define di luar.
        // Untuk kesederhanaan, mari kita tambahkan kembali `domUtils.removeEventListener` secara konsisten.
        if (modalContent) {
            domUtils.removeEventListener(modalContent, handleModalContentClick); // Remove previous
            domUtils.addEventListener(modalContent, handleModalContentClick);    // Add new
        }

        const handleCancelClick = (e) => { // Pastikan menerima event
            e.stopPropagation(); // <<< PASTIKAN INI ADA
            closeCommonConfirmModal();
            console.log('Confirm modal cancel clicked.');
        };

        const handleConfirmClick = (e) => { // Pastikan menerima event
            e.stopPropagation(); // <<< PASTIKAN INI ADA
            if (confirmCallback) {
                confirmCallback();
            }
            closeCommonConfirmModal();
            console.log('Confirm modal confirm clicked.');
        };

        domUtils.removeEventListener(commonCancelBtn, handleCancelClick);
        domUtils.removeEventListener(commonConfirmBtn, handleConfirmClick);
        domUtils.addEventListener(commonCancelBtn, handleCancelClick);
        domUtils.addEventListener(commonConfirmBtn, handleConfirmClick);

        function closeCommonConfirmModal() {
            domUtils.toggleModal(commonConfirmModal, false);
            confirmCallback = null;
            domUtils.removeEventListener(commonCancelBtn, handleCancelClick);
            domUtils.removeEventListener(commonConfirmBtn, handleConfirmClick);
            if (modalContent) {
                domUtils.removeEventListener(modalContent, handleModalContentClick); // Remove on close
            }
            console.log('commonConfirmModal closed.');
        }
    },

    openDetailModal: (title, contentHtml) => {
        const commonDetailModal = domUtils.getElement('commonDetailModal');
        const commonDetailModalTitle = domUtils.getElement('commonDetailModalTitle');
        const detailContentWrapper = commonDetailModal ? commonDetailModal.querySelector('.detail-content-wrapper') : null;
        const closeCommonDetailModalBtn = domUtils.getElement('closeCommonDetailModalBtn');

        console.log('notificationManager.openDetailModal dipanggil.');
        if (!commonDetailModal || !commonDetailModalTitle || !detailContentWrapper) {
            console.error("Elemen modal detail tidak ditemukan di notificationManager.openDetailModal.");
            return;
        }
        commonDetailModalTitle.textContent = title;
        detailContentWrapper.innerHTML = contentHtml;
        domUtils.toggleModal(commonDetailModal, true);

        const modalContent = commonDetailModal.querySelector('.modal-content');
        const handleDetailModalContentClick = (e) => {
            e.stopPropagation();
            console.log('Click inside detail modal content stopped propagation.');
        };
        if (modalContent) {
            domUtils.removeEventListener(modalContent, handleDetailModalContentClick);
            domUtils.addEventListener(modalContent, handleDetailModalContentClick);
        }

        const closeDetailClick = (e) => {
            e.stopPropagation();
            domUtils.toggleModal(commonDetailModal, false);
            if (detailContentWrapper) {
                detailContentWrapper.innerHTML = '';
            }
            domUtils.removeEventListener(closeCommonDetailModalBtn, closeDetailClick);
            if (modalContent) {
                domUtils.removeEventListener(modalContent, handleDetailModalContentClick); // Remove on close
            }
            console.log('commonDetailModal closed.');
        };

        domUtils.removeEventListener(closeCommonDetailModalBtn, closeDetailClick);
        domUtils.addEventListener(closeCommonDetailModalBtn, closeDetailClick);
    },

    openAdminCategoryModal: (mode, categoryName, categorySlug) => {
        if (typeof window.openAdminCategoryModal === 'function') {
            window.openAdminCategoryModal(mode, categoryName, categorySlug);
        } else {
            console.error("Error: window.openAdminCategoryModal tidak ditemukan. Pastikan admin/categoryManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal kategori: fungsi tidak ditemukan.", "error");
        }
    },

    openAdminNavMenuModal: (mode, menuData, parentId) => {
        if (typeof window.openAdminNavMenuModal === 'function') {
            window.openAdminNavMenuModal(mode, menuData, parentId);
        } else {
            console.error("Error: window.openAdminNavMenuModal tidak ditemukan. Pastikan admin/navMenuManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal menu: fungsi tidak ditemukan.", "error");
        }
    },

    openUseCaseModal: (mode, useCase) => {
        if (typeof window.openUseCaseModal === 'function') {
            window.openUseCaseModal(mode, useCase);
        } else {
            console.error("Error: window.openUseCaseModal tidak ditemukan. Pastikan documentation/useCase/useCaseFormHandler.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal tindakan: fungsi tidak ditemukan.", "error");
        }
    },

    openUatDataModal: (mode, uatData) => {
        if (typeof window.openUatDataModal === 'function') {
            window.openUatDataModal(mode, uatData);
        } else {
            console.error("Error: window.openUatDataModal tidak ditemukan. Pastikan documentation/useCase/uatDataManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal UAT: fungsi tidak ditemukan.", "error");
        }
    },

    openReportDataModal: (mode, reportData) => {
        if (typeof window.openReportDataModal === 'function') {
            window.openReportDataModal(mode, reportData);
        } else {
            console.error("Error: window.openReportDataModal tidak ditemukan. Pastikan documentation/useCase/reportDataManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal laporan: fungsi tidak ditemukan.", "error");
        }
    },

    openDatabaseDataModal: (mode, databaseData) => {
        if (typeof window.openDatabaseDataModal === 'function') {
            window.openDatabaseDataModal(mode, databaseData);
        } else {
            console.error("Error: window.openDatabaseDataModal tidak ditemukan. Pastikan documentation/useCase/databaseDataManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal database: fungsi tidak ditemukan.", "error");
        }
    }
};
