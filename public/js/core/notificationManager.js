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

// Variabel global untuk menyimpan referensi handler agar bisa dihapus
let currentConfirmCancelHandler = null;
let currentConfirmSubmitHandler = null;
let currentConfirmModalContentClickHandler = null;
let currentConfirmModalOverlayClickHandler = null; // Handler baru untuk klik di overlay

let currentDetailCloseHandler = null;
let currentDetailModalContentClickHandler = null;
let currentDetailModalOverlayClickHandler = null; // Handler baru untuk klik di overlay


// Objek notificationManager yang diekspor
export const notificationManager = {
    showNotification: showNotificationInternal,
    hideNotification: hideNotificationInternal,
    showCentralSuccessPopup: showCentralSuccessPopupInternal,

    openConfirmModal: (message, onConfirm) => {
        const commonConfirmModal = domUtils.getElement('commonConfirmModal');
        const commonConfirmMessage = domUtils.getElement('common-confirm-message');
        const commonCancelBtn = domUtils.getElement('common-cancel-btn');
        const commonConfirmBtn = domUtils.getElement('common-confirm-btn');
        const modalContent = commonConfirmModal ? commonConfirmModal.querySelector('.modal-content') : null;

        if (!commonConfirmModal || !commonConfirmMessage || !commonCancelBtn || !commonConfirmBtn || !modalContent) {
            console.error("Elemen modal konfirmasi tidak ditemukan di notificationManager.openConfirmModal.");
            return;
        }

        // 1. Hapus event listener lama sebelum menambahkan yang baru
        if (currentConfirmCancelHandler) {
            domUtils.removeEventListener(commonCancelBtn, currentConfirmCancelHandler);
            currentConfirmCancelHandler = null; // Reset to null after removing
        }
        if (currentConfirmSubmitHandler) {
            domUtils.removeEventListener(commonConfirmBtn, currentConfirmSubmitHandler);
            currentConfirmSubmitHandler = null; // Reset to null after removing
        }
        if (currentConfirmModalContentClickHandler) {
            domUtils.removeEventListener(modalContent, currentConfirmModalContentClickHandler);
            currentConfirmModalContentClickHandler = null; // Reset to null
        }
        if (currentConfirmModalOverlayClickHandler) {
            domUtils.removeEventListener(commonConfirmModal, currentConfirmModalOverlayClickHandler);
            currentConfirmModalOverlayClickHandler = null; // Reset to null
        }

        commonConfirmMessage.textContent = message;

        // 2. Definisikan handler baru
        currentConfirmCancelHandler = (e) => {
            e.stopPropagation(); // Penting: Hentikan propagasi event
            domUtils.toggleModal(commonConfirmModal, false);
            console.log('Confirm modal cancel clicked.');
        };

        currentConfirmSubmitHandler = (e) => {
            e.stopPropagation(); // Penting: Hentikan propagasi event
            if (onConfirm) {
                onConfirm(); // Jalankan callback yang diberikan
            }
            domUtils.toggleModal(commonConfirmModal, false); // Tutup modal setelah konfirmasi
            console.log('Confirm modal confirm clicked.');
        };

        currentConfirmModalContentClickHandler = (e) => {
            e.stopPropagation(); // Pastikan klik di dalam konten modal tidak menyebar ke overlay
            console.log('Click inside confirm modal content stopped propagation.');
        };

        // Handler untuk klik pada overlay modal itu sendiri (bukan kontennya)
        currentConfirmModalOverlayClickHandler = (e) => {
            // HANYA tutup modal jika target klik adalah elemen modal itu sendiri,
            // dan BUKAN elemen anak-anaknya.
            if (e.target === commonConfirmModal) {
                console.log('Click on confirm modal overlay detected. Closing modal.');
                domUtils.toggleModal(commonConfirmModal, false);
            }
        };

        // 3. Tambahkan event listener baru
        domUtils.addEventListener(commonCancelBtn, 'click', currentConfirmCancelHandler);
        domUtils.addEventListener(commonConfirmBtn, 'click', currentConfirmSubmitHandler);
        domUtils.addEventListener(modalContent, 'click', currentConfirmModalContentClickHandler); // Mencegah klik di konten dari bubbling ke overlay
        domUtils.addEventListener(commonConfirmModal, 'click', currentConfirmModalOverlayClickHandler); // Menangkap klik pada overlay

        // 4. Tampilkan modal
        domUtils.toggleModal(commonConfirmModal, true);
        console.log('commonConfirmModal opened with new handlers.');
    },

    openDetailModal: (title, contentHtml) => {
        const commonDetailModal = domUtils.getElement('commonDetailModal');
        const commonDetailModalTitle = domUtils.getElement('commonDetailModalTitle');
        const detailContentWrapper = commonDetailModal ? commonDetailModal.querySelector('.detail-content-wrapper') : null;
        const closeCommonDetailModalBtn = domUtils.getElement('closeCommonDetailModalBtn');
        const modalContent = commonDetailModal ? commonDetailModal.querySelector('.modal-content') : null;

        if (!commonDetailModal || !commonDetailModalTitle || !detailContentWrapper || !closeCommonDetailModalBtn || !modalContent) {
            console.error("Elemen modal detail tidak ditemukan di notificationManager.openDetailModal.");
            return;
        }

        // Hapus listener lama sebelum menambahkan yang baru
        if (currentDetailCloseHandler) {
            domUtils.removeEventListener(closeCommonDetailModalBtn, currentDetailCloseHandler);
            currentDetailCloseHandler = null;
        }
        if (currentDetailModalContentClickHandler) {
            domUtils.removeEventListener(modalContent, currentDetailModalContentClickHandler);
            currentDetailModalContentClickHandler = null;
        }
        if (currentDetailModalOverlayClickHandler) {
            domUtils.removeEventListener(commonDetailModal, currentDetailModalOverlayClickHandler);
            currentDetailModalOverlayClickHandler = null;
        }

        commonDetailModalTitle.textContent = title;
        detailContentWrapper.innerHTML = contentHtml;
        domUtils.toggleModal(commonDetailModal, true);

        currentDetailCloseHandler = (e) => {
            e.stopPropagation();
            domUtils.toggleModal(commonDetailModal, false);
            if (detailContentWrapper) {
                detailContentWrapper.innerHTML = '';
            }
            console.log('commonDetailModal closed.');
        };

        currentDetailModalContentClickHandler = (e) => {
            e.stopPropagation();
            console.log('Click inside detail modal content stopped propagation.');
        };

        currentDetailModalOverlayClickHandler = (e) => {
            if (e.target === commonDetailModal) {
                console.log('Click on detail modal overlay detected. Closing modal.');
                domUtils.toggleModal(commonDetailModal, false);
            }
        };

        domUtils.addEventListener(closeCommonDetailModalBtn, 'click', currentDetailCloseHandler);
        domUtils.addEventListener(modalContent, 'click', currentDetailModalContentClickHandler);
        domUtils.addEventListener(commonDetailModal, 'click', currentDetailModalOverlayClickHandler); // Tambahkan handler untuk overlay
    },

    // Metode ini memanggil window.openAdminCategoryModal yang diekspos oleh categoryManager.js
    openAdminCategoryModal: (mode, categoryName, categorySlug) => {
        if (typeof window.openAdminCategoryModal === 'function') {
            window.openAdminCategoryModal(mode, categoryName, categorySlug);
        } else {
            console.error("Error: window.openAdminCategoryModal tidak ditemukan. Pastikan admin/categoryManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal kategori: fungsi tidak ditemukan.", "error");
        }
    },

    // Metode ini memanggil window.openAdminNavMenuModal yang diekspos oleh navMenuManager.js
    openAdminNavMenuModal: (mode, menuData, parentId) => {
        if (typeof window.openAdminNavMenuModal === 'function') {
            window.openAdminNavMenuModal(mode, menuData, parentId);
        } else {
            console.error("Error: window.openAdminNavMenuModal tidak ditemukan. Pastikan admin/navMenuManager.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal menu: fungsi tidak ditemukan.", "error");
        }
    },

    // Metode ini memanggil window.openUseCaseModal yang diekspos oleh useCaseFormHandler.js
    openUseCaseModal: (mode, useCase) => {
        if (typeof window.openUseCaseModal === 'function') {
            window.openUseCaseModal(mode, useCase);
        } else {
            console.error("Error: window.openUseCaseModal tidak ditemukan. Pastikan documentation/useCase/useCaseFormHandler.js dimuat dan inisialisasi.");
            notificationManager.showNotification("Gagal membuka modal tindakan: fungsi tidak ditemukan.", "error");
        }
    },

    // Metode ini memanggil window.openUatDataModal yang diekspos oleh uatDataManager.js
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
