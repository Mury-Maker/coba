// public/js/core/globalModals.js

import { domUtils } from './domUtils.js';

export function initGlobalModals() {
    console.log('initGlobalModals dipanggil.'); // DEBUG
    const commonConfirmModal = domUtils.getElement('commonConfirmModal');
    const commonConfirmMessage = domUtils.getElement('common-confirm-message');
    const commonCancelBtn = domUtils.getElement('common-cancel-btn');
    const commonConfirmBtn = domUtils.getElement('common-confirm-btn');

    const commonDetailModal = domUtils.getElement('commonDetailModal');
    const commonDetailModalTitle = domUtils.getElement('commonDetailModalTitle');
    const detailContentWrapper = commonDetailModal ? commonDetailModal.querySelector('.detail-content-wrapper') : null;
    const closeCommonDetailModalBtn = domUtils.getElement('closeCommonDetailModalBtn');

    let confirmCallback = null;

    /**
     * Membuka modal konfirmasi umum.
     * @param {string} message - Pesan konfirmasi.
     * @param {Function} onConfirm - Callback yang dijalankan saat tombol konfirmasi diklik.
     */
    // INI BARIS KRITISNYA: MENGEKSPOR FUNGSI KE OBJEK WINDOW
    window.openCommonConfirmModal = (message, onConfirm) => {
        console.log('openCommonConfirmModal dipanggil.'); // DEBUG
        if (!commonConfirmModal || !commonConfirmMessage || !commonConfirmBtn || !commonCancelBtn) {
            console.error("Elemen modal konfirmasi tidak ditemukan.");
            return;
        }
        commonConfirmMessage.textContent = message;
        confirmCallback = onConfirm;
        domUtils.toggleModal(commonConfirmModal, true);
    };

    /**
     * Menutup modal konfirmasi umum.
     */
    function closeCommonConfirmModal() {
        domUtils.toggleModal(commonConfirmModal, false);
        confirmCallback = null; // Reset callback
        console.log('commonConfirmModal closed.'); // DEBUG
    }

    // Event listener untuk tombol Batal
    domUtils.addEventListener(commonCancelBtn, 'click', closeCommonConfirmModal);

    // Event listener untuk tombol Hapus (Konfirmasi)
    domUtils.addEventListener(commonConfirmBtn, 'click', () => {
        if (confirmCallback) {
            confirmCallback(); // Jalankan callback
        }
        closeCommonConfirmModal();
    });

    /**
     * Membuka modal detail umum.
     * @param {string} title - Judul modal.
     * @param {string} contentHtml - Konten HTML yang akan ditampilkan.
     */
    // INI BARIS KRITISNYA: MENGEKSPOR FUNGSI KE OBJEK WINDOW
    window.openCommonDetailModal = (title, contentHtml) => {
        console.log('openCommonDetailModal dipanggil.'); // DEBUG
        if (!commonDetailModal || !commonDetailModalTitle || !detailContentWrapper) {
            console.error("Elemen modal detail tidak ditemukan.");
            return;
        }
        commonDetailModalTitle.textContent = title;
        detailContentWrapper.innerHTML = contentHtml;
        domUtils.toggleModal(commonDetailModal, true);
    };

    /**
     * Menutup modal detail umum.
     */
    function closeCommonDetailModal() {
        domUtils.toggleModal(commonDetailModal, false);
        if (detailContentWrapper) {
            detailContentWrapper.innerHTML = ''; // Bersihkan konten
        }
        console.log('commonDetailModal closed.'); // DEBUG
    }

    // Event listener untuk tombol Tutup di modal detail
    domUtils.addEventListener(closeCommonDetailModalBtn, 'click', closeCommonDetailModal);
}
