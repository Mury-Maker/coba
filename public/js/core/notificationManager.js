// public/js/core/notificationManager.js

/**
 * Menampilkan notifikasi toast.
 * @param {string} message - Pesan yang akan ditampilkan.
 * @param {'success' | 'error' | 'loading'} type - Tipe notifikasi.
 * @param {number} duration - Durasi tampilan (ms). 0 untuk tidak auto-hide.
 * @returns {{notifId: string, showTimeoutId: number | null, element: HTMLElement}} - Informasi notifikasi.
 */
function showNotification(message, type = 'success', duration = 3000) {
    const container = document.getElementById('notification-container');
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
        iconClass = 'fa-solid fa-check-circle text-green-500'; // Sesuaikan warna
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
        setTimeout(() => notifDiv.classList.add('show'), 10);
    }, delayBeforeShow);

    if (timeoutDuration > 0) {
        setTimeout(() => {
            notifDiv.classList.remove('show');
            setTimeout(() => notifDiv.remove(), 500);
        }, timeoutDuration + delayBeforeShow);
    }

    return { notifId: notifId, showTimeoutId: showTimeoutId, element: notifDiv };
}

/**
 * Menyembunyikan notifikasi.
 * @param {string | object} notifInfo - ID notifikasi atau objek dari showNotification.
 */
function hideNotification(notifInfo) {
    let notifElement;
    let showTimeoutId;

    if (typeof notifInfo === 'string') {
        notifElement = document.getElementById(notifInfo);
    } else if (typeof notifInfo === 'object' && notifInfo !== null) {
        notifElement = notifInfo.element;
        showTimeoutId = notifInfo.showTimeoutId;
    }

    if (showTimeoutId) {
        clearTimeout(showTimeoutId);
    }

    if (notifElement && notifElement.parentNode) {
        notifElement.classList.remove('show');
        setTimeout(() => notifElement.remove(), 500);
    }
}

/**
 * Menampilkan pop-up sukses di tengah layar.
 * @param {string} message - Pesan yang akan ditampilkan.
 */
function showCentralSuccessPopup(message) {
    const centralSuccessPopup = document.getElementById('commonSuccessPopup');
    const centralPopupMessage = document.getElementById('common-popup-message');

    if (centralPopupMessage) {
        centralPopupMessage.textContent = message;
    }
    if (centralSuccessPopup) {
        domUtils.toggleModal(centralSuccessPopup, true); // Gunakan domUtils.toggleModal
        setTimeout(() => {
            domUtils.toggleModal(centralSuccessPopup, false); // Gunakan domUtils.toggleModal
        }, 1000); // Popup bertahan 1 detik
    }
}

export const notificationManager = {
    showNotification,
    hideNotification,
    showCentralSuccessPopup
};
