// public/js/documentation/tablesListManager.js

import { domUtils } from '../core/domUtils.js';
import { notificationManager } from '../core/notificationManager.js';
import { apiClient } from '../core/apiClient.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initTablesListManager() {
    console.log('initTablesListManager dipanggil.');

    const uploadForm = domUtils.getElement('uploadSqlForm'); // Pastikan Anda menambahkan ID ini pada form di Blade
    const deleteForm = domUtils.getElement('deleteSqlForm'); // Pastikan Anda menambahkan ID ini pada form di Blade
    const generateErdForm = domUtils.getElement('generateErdForm'); // Pastikan Anda menambahkan ID ini pada form di Blade
    const toggleFormBtn = document.querySelector('[onclick="toggleFileUpdate()"]');
    const updateForm = document.querySelector('.form-update-erd form'); // Pastikan form ini ada di Blade

    // Menangani form upload/update file SQL
    if (uploadForm) {
        domUtils.addEventListener(uploadForm, 'submit', async (e) => {
            e.preventDefault();
            const loadingNotif = notificationManager.showNotification('Mengunggah dan memproses file SQL...', 'loading');
            try {
                const formData = new FormData(uploadForm);
                const response = await apiClient.fetchAPI(e.target.action, {
                    method: 'POST',
                    body: formData,
                });
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup('File SQL berhasil diunggah!');
                // Refresh halaman untuk menampilkan data baru
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } catch (error) {
                console.error('Upload SQL GAGAL:', error);
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showNotification('Gagal mengunggah file SQL.', 'error');
            }
        });
    }

    if (updateForm) {
        domUtils.addEventListener(updateForm, 'submit', async (e) => {
            e.preventDefault();
            const loadingNotif = notificationManager.showNotification('Memperbarui dan memproses file SQL...', 'loading');
            try {
                const formData = new FormData(updateForm);
                const response = await apiClient.fetchAPI(e.target.action, {
                    method: 'POST',
                    body: formData,
                });
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup(response.success);
                // Refresh halaman untuk menampilkan data baru
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } catch (error) {
                console.error('Update SQL GAGAL:', error);
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showNotification('Gagal memperbarui file SQL.', 'error');
            }
        });
    }

    // Menangani tombol Hapus SQL
    const deleteSqlBtn = document.querySelector('.deleteSql form button');

    if (deleteSqlBtn) {
        domUtils.addEventListener(deleteSqlBtn, 'click', async (e) => {
            e.preventDefault(); // Mencegah form dikirim secara default
            const form = e.target.closest('form');
            if (!form) return;

            notificationManager.openConfirmModal('Yakin ingin menghapus file dan semua data tabel? Tindakan ini tidak dapat dibatalkan.', async () => {
                const loadingNotif = notificationManager.showNotification('Menghapus data...', 'loading');
                try {
                    const response = await apiClient.fetchAPI(form.action, {
                        method: 'POST',
                        body: {
                            _token: document.querySelector('meta[name="csrf-token"]').content,
                            _method: 'DELETE',
                        }
                    });

                    notificationManager.hideNotification(loadingNotif);
                    notificationManager.showCentralSuccessPopup('Data berhasil dihapus.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    console.error('Hapus SQL GAGAL:', error);
                    notificationManager.hideNotification(loadingNotif);
                    notificationManager.showNotification('Gagal menghapus file SQL.', 'error');
                }
            });
        });
    }

    // Menangani tombol Generate ERD
    const generateErdBtn = document.querySelector('.sql form button[type="submit"]');
    if (generateErdBtn) {
        domUtils.addEventListener(generateErdBtn, 'click', async (e) => {
            e.preventDefault();
            const form = e.target.closest('form');
            if (!form) return;

            const loadingNotif = notificationManager.showNotification('Membuat Diagram ERD...', 'loading');
            try {
                const formData = new FormData(form);
                const response = await apiClient.fetchAPI(form.action, {
                    method: 'POST',
                    body: formData,
                });

                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup('ERD berhasil dibuat.');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } catch (error) {
                console.error('Generate ERD GAGAL:', error);
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showNotification('Gagal membuat diagram ERD.', 'error');
            }
        });
    }
}
