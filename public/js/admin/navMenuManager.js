// public/js/admin/navMenuManager.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initNavMenuManager() {
    console.log('initNavMenuManager dipanggil.'); // DEBUG: Confirm initialization
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
    const formNavMenuCategoryId = domUtils.getElement('form_navmenu_category_id'); // Hidden input untuk category_id
    const cancelAdminNavMenuFormBtn = domUtils.getElement('cancelAdminNavMenuFormBtn');

    /**
     * Membuka modal menu untuk tambah atau edit.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [menuData=null] - Objek data menu untuk mode edit.
     * @param {number} [parentId=0] - ID parent untuk mode create (jika tambah sub-menu).
     */
    window.openAdminNavMenuModal = async (mode, menuData = null, parentId = 0) => {
        console.log('openAdminNavMenuModal dipanggil. Mode:', mode, 'Menu Data Awal:', menuData, 'Parent ID:', parentId); // DEBUG
        if (!adminNavMenuForm || !adminNavMenuModalTitle || !formNavMenuCategoryId) { // Pastikan formNavMenuCategoryId ada
            notificationManager.showNotification('Elemen modal menu tidak ditemukan.', 'error');
            console.error('NavMenu modal elements are missing.'); // DEBUG
            return;
        }

        adminNavMenuForm.reset();
        formNavMenuId.value = '';
        formNavMenuMethod.value = 'POST';
        // Saat membuat, category_id diambil dari kategori aktif di Blade (numerik)
        formNavMenuCategoryId.value = window.APP_BLADE_DATA.currentCategoryId;

        const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug; // Slug tetap dibutuhkan untuk API parents

        // Muat daftar parent menu
        formNavMenuChild.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>'; // Reset options
        let parentApiUrl = `${APP_CONSTANTS.API_ROUTES.NAVMENU.PARENTS}/${currentCategorySlug}`;
        if (mode === 'edit' && menuData) {
            parentApiUrl += `?editing_menu_id=${menuData.menu_id}`;
        }
        console.log('Fetching parent menus from:', parentApiUrl); // DEBUG

        try {
            const parents = await apiClient.fetchAPI(parentApiUrl);
            parents.forEach(parent => {
                const option = document.createElement('option');
                option.value = parent.menu_id;
                option.textContent = parent.menu_nama;
                formNavMenuChild.appendChild(option);
            });
            console.log('Parent menus loaded:', parents); // DEBUG
        } catch (error) {
            notificationManager.showNotification('Gagal memuat daftar parent menu.', 'error');
            console.error('Failed to load parent menus:', error); // DEBUG
            domUtils.toggleModal(adminNavMenuModal, false);
            return;
        }

        if (mode === 'create') {
            adminNavMenuModalTitle.textContent = 'Tambah Menu Baru';
            formNavMenuNama.value = '';
            formNavMenuIcon.value = '';
            formNavMenuOrder.value = '0';
            formNavMenuChild.value = parentId; // Set parentId jika ini sub-menu
            formNavMenuStatus.checked = false;
        } else if (mode === 'edit' && menuData) {
            adminNavMenuModalTitle.textContent = `Edit Menu: ${menuData.menu_nama}`;
            formNavMenuMethod.value = 'PUT';
            formNavMenuId.value = menuData.menu_id;
            formNavMenuNama.value = menuData.menu_nama || '';
            formNavMenuIcon.value = menuData.menu_icon || '';
            formNavMenuChild.value = menuData.menu_child;
            formNavMenuOrder.value = menuData.menu_order || '0';
            formNavMenuStatus.checked = menuData.menu_status == 1;

            formNavMenuCategoryId.value = menuData.category_id;
            console.log('Edit mode: Setting form values. menu_id:', menuData.menu_id, 'category_id (from menuData):', menuData.category_id); // DEBUG
        }
        domUtils.toggleModal(adminNavMenuModal, true);
        console.log('NavMenu modal toggled to show.'); // DEBUG
    };

    /**
     * Menutup modal menu.
     */
    function closeAdminNavMenuModal() {
        domUtils.toggleModal(adminNavMenuModal, false);
        adminNavMenuForm.reset();
        console.log('NavMenu modal closed and form reset.'); // DEBUG
    }

    domUtils.addEventListener(cancelAdminNavMenuFormBtn, 'click', closeAdminNavMenuModal);

    domUtils.addEventListener(adminNavMenuForm, 'submit', async (e) => {
        e.preventDefault();
        console.log('Form NavMenu disubmit.'); // DEBUG

        const loadingNotif = notificationManager.showNotification('Menyimpan menu...', 'loading');

        const method = formNavMenuMethod.value;
        const menuId = formNavMenuId.value;
        const categoryId = formNavMenuCategoryId.value; // Ambil category_id dari hidden input (ini harus numerik)

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
            menu_status: formNavMenuStatus.checked,
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
            closeAdminNavMenuModal();

            // Auto-refresh halaman setelah operasi sukses
            const newMenuLink = data.new_menu_link;
            const currentCategorySlug = data.current_category_slug;
            let redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${currentCategorySlug}/${newMenuLink}`;

            window.location.href = redirectUrl;

        } catch (error) {
            console.error('API request GAGAL:', error); // DEBUG
            notificationManager.hideNotification(loadingNotif);
        }
    });

    /**
     * Mengkonfirmasi penghapusan menu.
     * @param {number} menuId - ID menu yang akan dihapus.
     * @param {string} menuNama - Nama menu untuk pesan konfirmasi.
     */
    window.confirmDeleteMenu = (menuId, menuNama) => {
        console.log('confirmDeleteMenu dipanggil untuk:', menuNama, 'ID:', menuId); // DEBUG
        window.openCommonConfirmModal(`Apakah Anda yakin ingin menghapus menu "${menuNama}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua sub-menu terkait, serta seluruh konten (Aksi, UAT, Report, Database) di dalamnya.`, async () => {
            console.log('Konfirmasi Hapus disetujui untuk:', menuId); // DEBUG
            const loadingNotif = notificationManager.showNotification('Menghapus menu...', 'loading');
            try {
                const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.NAVMENU.DESTROY}/${menuId}`, {
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

    // Delegasi event untuk tombol add child, edit, delete menu di sidebar
    domUtils.addEventListener(document, 'click', (e) => {
        // Tombol "+" di sidebar utama (Tambah Menu Utama Baru)
        const addParentMenuBtn = e.target.closest('[data-action="add-parent-menu"]');

        // Tombol "+" di samping item menu (Tambah Sub Menu)
        const addChildMenuBtn = e.target.closest('[data-action="add-child-menu"]');

        const editNavMenuBtn = e.target.closest('[data-action="edit-menu"]');
        const deleteNavMenuBtn = e.target.closest('[data-action="delete-menu"]');
        const toggleSubmenuBtn = e.target.closest('[data-toggle^="submenu-"]');

        if (addParentMenuBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('--- Tombol "Tambah Menu Utama Baru" di Sidebar diklik. ---'); // DEBUG
            const parentId = parseInt(addParentMenuBtn.dataset.parentId || '0');
            window.openAdminNavMenuModal('create', null, parentId);
        } else if (addChildMenuBtn) { // <<< BLOK INI SEHARUSNYA MENANGANI TOMBOL PLUS DI SAMPING MENU
            e.preventDefault();
            e.stopPropagation();
            console.log('--- Tombol "Tambah Sub Menu" diklik. ---'); // DEBUG
            const parentId = parseInt(addChildMenuBtn.dataset.parentId || '0');
            window.openAdminNavMenuModal('create', null, parentId);
        } else if (editNavMenuBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Edit NavMenu button clicked.'); // DEBUG
            const menuId = parseInt(editNavMenuBtn.dataset.menuId);
            apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.NAVMENU.GET}/${menuId}`)
                .then(menuData => window.openAdminNavMenuModal('edit', menuData))
                .catch(error => {
                    console.error('Error fetching NavMenu data for edit:', error); // DEBUG
                    notificationManager.showNotification('Gagal memuat data menu.', 'error');
                });
        } else if (deleteNavMenuBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Delete NavMenu button clicked.'); // DEBUG
            const menuId = parseInt(deleteNavMenuBtn.dataset.menuId);
            const menuNama = deleteNavMenuBtn.dataset.menuNama;
            window.confirmDeleteMenu(menuId, menuNama);
        } else if (toggleSubmenuBtn) {
            // Ini adalah penanganan untuk panah toggle submenu, yang sudah ditangani oleh handleSubmenuToggle
            // Tidak perlu memanggil fungsi lain di sini karena event sudah ditangkap oleh listener lain
        }
    });
}
