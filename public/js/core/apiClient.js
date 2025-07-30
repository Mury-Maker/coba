// public/js/core/apiClient.js

import { notificationManager } from './notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

/**
 * Melakukan permintaan Fetch API.
 * @param {string} url - URL tujuan.
 * @param {object} options - Opsi untuk Fetch API (method, headers, body, etc.).
 * @returns {Promise<any>} - Promise yang me-resolve ke respons JSON.
 */
export async function fetchAPI(url, options = {}) {
    let headers = new Headers(options.headers || {});

    // Selalu tambahkan CSRF token
    const csrfToken = window.APP_BLADE_DATA.csrfToken; // Ambil dari data global
    if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken);
    }

    // Perhatikan: Jika body adalah FormData (untuk upload file),
    // browser akan otomatis mengatur Content-Type: multipart/form-data.
    // JANGAN set Content-Type secara manual untuk FormData.
    if (!(options.body instanceof FormData) && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    // Pastikan selalu meminta JSON sebagai respons
    if (!headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }

    options.headers = headers;

    // Jika method adalah PUT atau DELETE dan body bukan FormData,
    // kita perlu stringify body. Jika FormData, biarkan apa adanya.
    if (options.body && !(options.body instanceof FormData) && typeof options.body !== 'string') {
        options.body = JSON.stringify(options.body);
    }

    try {
        const response = await fetch(url, options);

        // Jika status adalah 401 (Unauthorized) atau 403 (Forbidden)
        if (response.status === 401 || response.status === 403) {
            let errorData = await response.json().catch(() => ({ message: 'Akses tidak sah atau terlarang.' }));
            notificationManager.showNotification(errorData.message || 'Akses tidak sah atau terlarang.', 'error');
            if (response.status === 401) {
                // Redirect ke halaman login jika tidak sah
                window.location.href = APP_CONSTANTS.ROUTES.LOGIN;
            }
            throw new Error(errorData.message || `HTTP Error! Status: ${response.status}`);
        }

        let data = null;
        const contentType = response.headers.get("content-type");

        if (contentType && contentType.includes("application/json")) {
            data = await response.json();
        } else {
            // Jika respons bukan JSON tapi sukses (misal 200 OK tanpa body, atau 204 No Content)
            if (response.ok) {
                return { success: true, message: "Operasi berhasil." };
            }
            // Jika bukan JSON dan bukan sukses, coba baca sebagai teks error
            const errorText = await response.text();
            throw new Error(`Respons bukan JSON. Status: ${response.status}. Pesan: ${errorText}`);
        }

        if (!response.ok) {
            throw new Error(data?.message || data?.error || `HTTP Error! Status: ${response.status}`);
        }

        return data;

    } catch (error) {
        console.error('API Client Error:', error);
        // Pastikan notificationManager sudah diinisialisasi
        if (error.message && !error.message.includes('Akses Ditolak')) { // Hindari notif duplikat jika sudah ditangani 403
            notificationManager.showNotification(error.message || 'Terjadi kesalahan pada permintaan API.', 'error');
        }
        throw error;
    }
}

export const apiClient = {
    fetchAPI
};
