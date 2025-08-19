// public/js/admin/categoryManager.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initCategoryManager() {
    console.log('initCategoryManager dipanggil.'); // DEBUG
    const adminCategoryModal = domUtils.getElement('adminCategoryModal');
    const adminCategoryModalTitle = domUtils.getElement('adminCategoryModalTitle');
    const adminCategoryForm = domUtils.getElement('adminCategoryForm');
    const formCategoryMethod = domUtils.getElement('form_category_method');
    const formCategoryName = domUtils.getElement('form_category_name');
    const formCategoryIdToEdit = domUtils.getElement('form_category_id_to_edit');
    const cancelAdminCategoryFormBtn = domUtils.getElement('cancelAdminCategoryFormBtn');

    /**
     * Membuka modal kategori untuk tambah atau edit.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {string} [categoryName=''] - Nama kategori untuk mode edit.
     * @param {string} [categorySlug=''] - Slug kategori untuk mode edit.
     */
    window.openAdminCategoryModal = async (mode, categoryName = '', categorySlug = '') => {
        console.log('openAdminCategoryModal dipanggil. Mode:', mode, 'Category Slug:', categorySlug); // DEBUG
        if (!adminCategoryModal || !adminCategoryModalTitle || !adminCategoryForm) {
            notificationManager.showNotification('Elemen modal kategori tidak ditemukan.', 'error');
            console.error('Category modal elements are missing.'); // DEBUG
            return;
        }

        adminCategoryForm.reset(); // RESET FORM SEBELUM MENGISI DATA BARU
        formCategoryIdToEdit.value = ''; // Pastikan ID kosong untuk CREATE
        formCategoryMethod.value = 'POST'; // Default untuk 'create'

        if (mode === 'create') {
            adminCategoryModalTitle.textContent = 'Tambah Kategori Baru';
            formCategoryName.value = '';
        } else if (mode === 'edit' && categorySlug) {
            adminCategoryModalTitle.textContent = `Edit Kategori: ${categoryName}`;
            formCategoryMethod.value = 'PUT';
            formCategoryIdToEdit.value = categorySlug;

            try {
                console.log('Fetching category data for edit:', categorySlug); // DEBUG
                const categoryData = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.CATEGORIES.GET}/${categorySlug}`);
                formCategoryName.value = categoryData.name || '';
                console.log('Category data loaded:', categoryData); // DEBUG
            } catch (error) {
                notificationManager.showNotification('Gagal memuat data kategori untuk diedit.', 'error');
                domUtils.toggleModal(adminCategoryModal, false);
                console.error('Failed to fetch category data:', error); // DEBUG
                return;
            }
        }
        domUtils.toggleModal(adminCategoryModal, true);
        console.log('Category modal toggled to show.'); // DEBUG
    };

    /**
     * Menutup modal kategori.
     */
    function closeAdminCategoryModal() {
        domUtils.toggleModal(adminCategoryModal, false);
        adminCategoryForm.reset(); // Bersihkan form
        console.log('Category modal closed and form reset.'); // DEBUG
    }

    domUtils.addEventListener(cancelAdminCategoryFormBtn, 'click', closeAdminCategoryModal);

    // KODE BARU: Tutup modal saat klik di luar form
    domUtils.addEventListener(adminCategoryModal, 'click', (e) => {
        if (e.target === adminCategoryModal) {
            closeAdminCategoryModal();
        }
    });

    domUtils.addEventListener(adminCategoryForm, 'submit', async (e) => {
        e.preventDefault();
        console.log('Form Kategori disubmit.'); // DEBUG

        const loadingNotif = notificationManager.showNotification('Memproses kategori...', 'loading');

        const categoryName = formCategoryName.value;
        const method = formCategoryMethod.value;
        const categoryIdToEdit = formCategoryIdToEdit.value;

        let url = APP_CONSTANTS.API_ROUTES.CATEGORIES.STORE;
        let httpMethod = 'POST';

        if (method === 'PUT') {
            url = `${APP_CONSTANTS.API_ROUTES.CATEGORIES.UPDATE}/${categoryIdToEdit}`;
            httpMethod = 'POST';
        }

        const formData = {
            name: categoryName,
        };

        console.log('Sending API request:', url, 'Method:', httpMethod, 'Data:', formData); // DEBUG

        try {
            const options = {
                method: httpMethod,
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            console.log('API request berhasil. Respons:', data); // DEBUG
            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeAdminCategoryModal();

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.reload();
            }
        } catch (error) {
            console.error('API request GAGAL:', error); // DEBUG
            notificationManager.hideNotification(loadingNotif);
        }
    });

    /**
     * Mengkonfirmasi penghapusan kategori.
     * @param {string} categorySlug - Slug kategori yang akan dihapus.
     * @param {string} categoryName - Nama kategori untuk pesan konfirmasi.
     */
    window.confirmDeleteCategory = (categorySlug, categoryName) => {
        console.log('confirmDeleteCategory dipanggil untuk:', categoryName, 'slug:', categorySlug); // DEBUG
        window.openCommonConfirmModal(`Apakah Anda yakin ingin menghapus kategori "${categoryName}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua menu dan konten terkait.`, async () => {
            console.log('Konfirmasi Hapus disetujui untuk:', categorySlug); // DEBUG
            const loadingNotif = notificationManager.showNotification('Menghapus kategori...', 'loading');
            try {
                const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.CATEGORIES.DESTROY}/${categorySlug}`, {
                    method: 'DELETE'
                });
                console.log('Delete API berhasil. Respons:', data); // DEBUG
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup(data.success);
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Delete API GAGAL:', error); // DEBUG
                notificationManager.hideNotification(loadingNotif);
            }
        });
    };
}