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
    const csrfToken = window.APP_BLADE_DATA.csrfToken;
    if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken);
    }

    if (!(options.body instanceof FormData) && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    if (!headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }

    options.headers = headers;

    if (options.body && !(options.body instanceof FormData) && typeof options.body !== 'string') {
        options.body = JSON.stringify(options.body);
    }

    console.log('apiClient.js: Sending request to', url, options);

    try {
        const response = await fetch(url, options);
        console.log('apiClient.js: Received response. Status:', response.status);

        if (response.status === 401 || response.status === 403) {
            let errorData = await response.json().catch(() => ({ message: 'Akses tidak sah atau terlarang.' }));
            notificationManager.showNotification(errorData.message || 'Akses tidak sah atau terlarang.', 'error');
            if (response.status === 401) {
                window.location.href = APP_CONSTANTS.ROUTES.LOGIN;
            }
            throw new Error(errorData.message || `HTTP Error! Status: ${response.status}`);
        }

        let data = null;
        const contentType = response.headers.get("content-type");

        if (contentType && contentType.includes("application/json")) {
            data = await response.json();
            console.log('apiClient.js: JSON response data:', data);
        } else {
            if (response.ok) {
                console.log('apiClient.js: Non-JSON success response.');
                return { success: true, message: "Operasi berhasil." };
            }
            const errorText = await response.text();
            throw new Error(`Respons bukan JSON. Status: ${response.status}. Pesan: ${errorText}`);
        }

        if (!response.ok) {
            throw new Error(data?.message || data?.error || `HTTP Error! Status: ${response.status}`);
        }

        return data;

    } catch (error) {
        console.error('API Client Error:', error);
        if (error.message && !error.message.includes('Akses Ditolak')) {
            notificationManager.showNotification(error.message || 'Terjadi kesalahan pada permintaan API.', 'error');
        }
        throw error;
    }
}

export const apiClient = {
    fetchAPI
};
