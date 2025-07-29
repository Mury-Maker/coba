// public/js/admin/categoryManager.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';

export function initCategoryManager() {
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
        if (!adminCategoryForm || !adminCategoryModalTitle) {
            notificationManager.showNotification('Elemen form kategori tidak ditemukan.', 'error');
            return;
        }

        adminCategoryForm.reset();
        formCategoryIdToEdit.value = ''; // Reset ID
        formCategoryMethod.value = 'POST'; // Default untuk create

        if (mode === 'create') {
            adminCategoryModalTitle.textContent = 'Tambah Kategori Baru';
            formCategoryName.value = '';
        } else if (mode === 'edit' && categorySlug) {
            adminCategoryModalTitle.textContent = `Edit Kategori: ${categoryName}`;
            formCategoryMethod.value = 'PUT';
            formCategoryIdToEdit.value = categorySlug; // Menggunakan slug untuk identifikasi di route

            // Isi form dengan data kategori yang ada
            try {
                const categoryData = await apiClient.fetchAPI(`/api/categories/${categorySlug}`);
                formCategoryName.value = categoryData.name || '';
            } catch (error) {
                notificationManager.showNotification('Gagal memuat data kategori untuk diedit.', 'error');
                return;
            }
        }
        domUtils.toggleModal(adminCategoryModal, true);
    };

    /**
     * Menutup modal kategori.
     */
    function closeAdminCategoryModal() {
        domUtils.toggleModal(adminCategoryModal, false);
        adminCategoryForm.reset(); // Bersihkan form
    }

    domUtils.addEventListener(cancelAdminCategoryFormBtn, 'click', closeAdminCategoryModal);

    domUtils.addEventListener(adminCategoryForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Memproses kategori...', 'loading');

        const categoryName = formCategoryName.value;
        const method = formCategoryMethod.value;
        const categoryIdToEdit = formCategoryIdToEdit.value; // Ini adalah slug saat edit

        let url = APP_CONSTANTS.API_ROUTES.CATEGORIES.STORE; // Default untuk POST
        let httpMethod = 'POST';

        if (method === 'PUT') {
            url = `${APP_CONSTANTS.API_ROUTES.CATEGORIES.UPDATE}/${categoryIdToEdit}`; // Gunakan slug di URL
            httpMethod = 'POST'; // Untuk method override
        }

        const formData = {
            name: categoryName,
        };

        try {
            const options = {
                method: httpMethod,
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeAdminCategoryModal();

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.reload(); // Fallback reload
            }
        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
            // Error handling sudah ada di apiClient, jadi cukup tampilkan pesan umum jika perlu
            // atau Laravel akan menampilkan error validasi otomatis.
        }
    });

    /**
     * Mengkonfirmasi penghapusan kategori.
     * @param {string} categorySlug - Slug kategori yang akan dihapus.
     * @param {string} categoryName - Nama kategori untuk pesan konfirmasi.
     */
    window.confirmDeleteCategory = (categorySlug, categoryName) => {
        window.openCommonConfirmModal(`Apakah Anda yakin ingin menghapus kategori "${categoryName}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua menu dan konten terkait.`, async () => {
            const loadingNotif = notificationManager.showNotification('Menghapus kategori...', 'loading');
            try {
                const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.CATEGORIES.DESTROY}/${categorySlug}`, {
                    method: 'DELETE'
                });
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup(data.success);
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload(); // Fallback reload
                }
            } catch (error) {
                notificationManager.hideNotification(loadingNotif);
            }
        });
    };
}
