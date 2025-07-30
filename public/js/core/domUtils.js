// public/js/core/domUtils.js

/**
 * Mendapatkan elemen DOM berdasarkan ID.
 * @param {string} id - ID elemen.
 * @returns {HTMLElement | null}
 */
function getElement(id) {
    return document.getElementById(id);
}

/**
 * Menyembunyikan elemen dengan menambahkan kelas 'hidden'.
 * @param {HTMLElement} element - Elemen DOM.
 */
function hideElement(element) {
    if (element) {
        element.classList.add('hidden');
    }
}

/**
 * Menampilkan elemen dengan menghapus kelas 'hidden'.
 * @param {HTMLElement} element - Elemen DOM.
 */
function showElement(element) {
    if (element) {
        element.classList.remove('hidden');
    }
}

/**
 * Menambahkan atau menghapus kelas pada elemen.
 * @param {HTMLElement} element - Elemen DOM.
 * @param {string} className - Nama kelas.
 * @param {boolean} add - true untuk menambah, false untuk menghapus.
 */
function toggleClass(element, className, add) {
    if (element) {
        if (add) {
            element.classList.add(className);
        } else {
            element.classList.remove(className);
        }
    }
}

/**
 * Menambahkan event listener pada elemen.
 * @param {HTMLElement} element - Elemen DOM.
 * @param {string} event - Nama event (misal 'click').
 * @param {Function} handler - Fungsi handler.
 */
function addEventListener(element, event, handler) {
    if (element) {
        element.addEventListener(event, handler);
    }
}

/**
 * Menghapus event listener dari elemen.
 * @param {HTMLElement} element - Elemen DOM.
 * @param {string} event - Nama event (misal 'click').
 * @param {Function} handler - Fungsi handler yang akan dihapus.
 */
function removeEventListener(element, event, handler) {
    if (element) {
        element.removeEventListener(event, handler);
    }
}


/**
 * Mengatur tampilan modal (menambah/menghapus kelas 'show').
 * @param {HTMLElement} modalElement - Elemen modal.
 * @param {boolean} show - true untuk menampilkan, false untuk menyembunyikan.
 */
function toggleModal(modalElement, show) {
    if (modalElement) {
        toggleClass(modalElement, 'show', show);
    }
}

export const domUtils = {
    getElement,
    hideElement,
    showElement,
    toggleClass,
    addEventListener,
    removeEventListener, // <<< Pastikan ini diekspor
    toggleModal
};
