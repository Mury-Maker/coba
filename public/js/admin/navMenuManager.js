// public/js/admin/navMenuManager.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initNavMenuManager() {
    const adminNavMenuModal = domUtils.getElement('adminNavMenuModal');
    const adminNavMenuModalTitle = domUtils.getElement('adminNavMenuModalTitle');
    const adminNavMenuForm = domUtils.getElement('adminNavMenuForm');
    const formNavMenuId = domUtils.getElement('form_navmenu_id');
    const formNavMenuMethod = domUtils.getElement('form_navmenu_method');
    const formNavMenuNama = domUtils.getElement('form_navmenu_nama');
    const formNavMenuIcon = domUtils.getElement('form_navmenu_icon');
    const formNavMenuChild = domUtils.getElement('form_navmenu_child');
    const formNavMenuOrder = domUtils.getElement('form_navmenu_order');
    const formNavMenuStatus = domUtils.getElement('form_navmenu_status');
    const formNavMenuCategoryId = domUtils.getElement('form_navmenu_category_id');
    const cancelAdminNavMenuFormBtn = domUtils.getElement('cancelAdminNavMenuFormBtn');

    /**
     * Membuka modal menu untuk tambah atau edit.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [menuData=null] - Objek data menu untuk mode edit.
     * @param {number} [parentId=0] - ID parent untuk mode create (jika tambah sub-menu).
     */
    window.openAdminNavMenuModal = async (mode, menuData = null, parentId = 0) => {
        if (!adminNavMenuForm || !adminNavMenuModalTitle) {
            notificationManager.showNotification('Elemen form menu tidak ditemukan.', 'error');
            return;
        }

        adminNavMenuForm.reset();
        formNavMenuId.value = '';
        formNavMenuMethod.value = 'POST';
        formNavMenuCategoryId.value = window.APP_DATA.currentCategorySlug; // Pastikan ini diisi

        const currentCategorySlug = window.APP_DATA.currentCategorySlug;

        // Muat daftar parent menu
        formNavMenuChild.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>'; // Reset options
        let parentApiUrl = `${APP_CONSTANTS.API_ROUTES.NAVMENU.PARENTS}/${currentCategorySlug}`;
        if (mode === 'edit' && menuData) {
            parentApiUrl += `?editing_menu_id=${menuData.menu_id}`;
        }

        try {
            const parents = await apiClient.fetchAPI(parentApiUrl);
            parents.forEach(parent => {
                const option = document.createElement('option');
                option.value = parent.menu_id;
                option.textContent = parent.menu_nama;
                formNavMenuChild.appendChild(option);
            });
        } catch (error) {
            notificationManager.showNotification('Gagal memuat daftar parent menu.', 'error');
            return;
        }

        if (mode === 'create') {
            adminNavMenuModalTitle.textContent = 'Tambah Menu Baru';
            formNavMenuNama.value = '';
            formNavMenuIcon.value = '';
            formNavMenuOrder.value = '0';
            formNavMenuChild.value = parentId;
            formNavMenuStatus.checked = false;
        } else if (mode === 'edit' && menuData) {
            adminNavMenuModalTitle.textContent = `Edit Menu: ${menuData.menu_nama}`;
            formNavMenuMethod.value = 'PUT';
            formNavMenuId.value = menuData.menu_id;
            formNavMenuNama.value = menuData.menu_nama || '';
            formNavMenuIcon.value = menuData.menu_icon || '';
            formNavMenuChild.value = menuData.menu_child;
            formNavMenuOrder.value = menuData.menu_order || '0';
            formNavMenuStatus.checked = menuData.menu_status == 1; // Checkbox value is 1 or 0
        }
        domUtils.toggleModal(adminNavMenuModal, true);
    };

    /**
     * Menutup modal menu.
     */
    function closeAdminNavMenuModal() {
        domUtils.toggleModal(adminNavMenuModal, false);
        adminNavMenuForm.reset();
    }

    domUtils.addEventListener(cancelAdminNavMenuFormBtn, 'click', closeAdminNavMenuModal);

    domUtils.addEventListener(adminNavMenuForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan menu...', 'loading');

        const method = formNavMenuMethod.value;
        const menuId = formNavMenuId.value;
        const categoryId = formNavMenuCategoryId.value; // Ambil category_id dari hidden input

        let url = APP_CONSTANTS.API_ROUTES.NAVMENU.STORE;
        let httpMethod = 'POST';

        if (method === 'PUT') {
            url = `${APP_CONSTANTS.API_ROUTES.NAVMENU.UPDATE}/${menuId}`;
            httpMethod = 'POST';
        }

        const formData = {
            category_id: categoryId,
            menu_nama: formNavMenuNama.value,
            menu_icon: formNavMenuIcon.value,
            menu_child: formNavMenuChild.value,
            menu_order: formNavMenuOrder.value,
            menu_status: formNavMenuStatus.checked, // true/false
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
            closeAdminNavMenuModal();

            // Auto-refresh halaman setelah operasi sukses
            const newMenuLink = data.new_menu_link;
            const currentCategorySlug = data.current_category_slug; // Slug kategori yang aktif
            let redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${newMenuLink}`;

            if (data.menu_status === 1) { // Jika menu punya konten, tambahkan query param jika relevan
                // Ini mungkin tidak lagi diperlukan dengan rute showUseCaseDetail terpisah
                // Namun, kita bisa tambahkan untuk konsistensi atau jika ada tab default.
                // redirectUrl += `?content_type=UAT`;
            }
            window.location.href = redirectUrl;

        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
            // API client sudah menangani notifikasi error
        }
    });

    /**
     * Mengkonfirmasi penghapusan menu.
     * @param {number} menuId - ID menu yang akan dihapus.
     * @param {string} menuNama - Nama menu untuk pesan konfirmasi.
     */
    window.confirmDeleteMenu = (menuId, menuNama) => {
        window.openCommonConfirmModal(`Apakah Anda yakin ingin menghapus menu "${menuNama}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua sub-menu terkait, serta seluruh konten (Aksi, UAT, Report, Database) di dalamnya.`, async () => {
            const loadingNotif = notificationManager.showNotification('Menghapus menu...', 'loading');
            try {
                const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.NAVMENU.DESTROY}/${menuId}`, {
                    method: 'DELETE'
                });
                notificationManager.hideNotification(loadingNotif);
                notificationManager.showCentralSuccessPopup(data.success);
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload();
                }
            } catch (error) {
                notificationManager.hideNotification(loadingNotif);
            }
        });
    };

    // Delegasi event untuk tombol add child, edit, delete menu di sidebar
    domUtils.addEventListener(document, 'click', (e) => {
        const addNavMenuChildBtn = e.target.closest('[data-action="add-child-menu"]');
        const editNavMenuBtn = e.target.closest('[data-action="edit-menu"]');
        const deleteNavMenuBtn = e.target.closest('[data-action="delete-menu"]');

        if (addNavMenuChildBtn) {
            const parentId = parseInt(addNavMenuChildBtn.dataset.parentId);
            window.openAdminNavMenuModal('create', null, parentId);
        } else if (editNavMenuBtn) {
            const menuId = parseInt(editNavMenuBtn.dataset.menuId);
            apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.NAVMENU.GET}/${menuId}`)
                .then(menuData => window.openAdminNavMenuModal('edit', menuData))
                .catch(error => notificationManager.showNotification('Gagal memuat data menu.', 'error'));
        } else if (deleteNavMenuBtn) {
            const menuId = parseInt(deleteNavMenuBtn.dataset.menuId);
            const menuNama = deleteNavMenuBtn.dataset.menuNama;
            window.confirmDeleteMenu(menuId, menuNama);
        }
    });
}
