// public/js/admin/navMenuManager.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initNavMenuManager() {
    if (window.__navMenuManagerInitialized) return;
    window.__navMenuManagerInitialized = true;
    console.log('initNavMenuManager dipanggil.');

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
    
    // Ambil elemen iconPreview
    const iconPreview = document.getElementById("iconPreview");

    // Fungsi untuk mereset tampilan form, termasuk ikon
    function resetFormState() {
        adminNavMenuForm.reset();
        formNavMenuId.value = '';
        formNavMenuMethod.value = 'POST';
        adminNavMenuModalTitle.textContent = 'Tambah Menu Baru';
        // Reset tampilan ikon
        if (iconPreview) {
            iconPreview.className = 'text-2xl text-gray-500';
        }
        formNavMenuIcon.value = ''; // Pastikan input teks icon juga direset
        console.log('[Modal] Form dan ikon direset.');
    }

    window.openAdminNavMenuModal = async (mode, menuData = null, parentId = 0) => {
        console.log('[Modal] openAdminNavMenuModal:', { mode, menuData, parentId });
        if (!adminNavMenuForm || !adminNavMenuModalTitle || !formNavMenuCategoryId) {
            notificationManager.showNotification('Elemen modal menu tidak ditemukan.', 'error');
            console.error('[Modal] Elemen tidak ditemukan.');
            return;
        }

        // Reset form sebelum mengisi data baru
        resetFormState();

        const currentCategorySlug = window.APP_BLADE_DATA.currentCategorySlug;
        formNavMenuChild.innerHTML = '<option value="0">Tidak Ada (Menu Utama)</option>';
        let parentApiUrl = `${APP_CONSTANTS.API_ROUTES.NAVMENU.PARENTS}/${currentCategorySlug}`;
        if (mode === 'edit' && menuData) {
            parentApiUrl += `?editing_menu_id=${menuData.menu_id}`;
        }

        try {
            const parents = await apiClient.fetchAPI(parentApiUrl);
            const existingIds = new Set();
            parents.forEach(parent => {
                if (!existingIds.has(parent.menu_id)) {
                    const option = document.createElement('option');
                    option.value = parent.menu_id;
                    option.textContent = parent.menu_nama;
                    formNavMenuChild.appendChild(option);
                    existingIds.add(parent.menu_id);
                }
            });
        } catch (error) {
            notificationManager.showNotification('Gagal memuat daftar parent menu.', 'error');
            console.error('[Modal] Gagal muat parent menu:', error);
            domUtils.toggleModal(adminNavMenuModal, false);
            return;
        }

        if (mode === 'create') {
            formNavMenuChild.value = parentId;
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
            console.log('[Modal] Edit mode data set:', menuData);
            
            // Set pratinjau ikon saat edit
            if (menuData.menu_icon && iconPreview) {
                iconPreview.className = `text-2xl text-gray-500 ${menuData.menu_icon}`;
            }
        }

        domUtils.toggleModal(adminNavMenuModal, true);
    };

    function closeAdminNavMenuModal() {
        domUtils.toggleModal(adminNavMenuModal, false);
        resetFormState(); // Panggil fungsi reset form saat modal ditutup
        console.log('[Modal] Ditutup dan form direset');
    }

    domUtils.addEventListener(cancelAdminNavMenuFormBtn, 'click', closeAdminNavMenuModal);

    // Tutup modal jika klik di luar form/modal-content
    document.addEventListener('click', function (e) {
        if (adminNavMenuModal.classList.contains('show')) {
            const isClickInside = adminNavMenuModal.querySelector('.modal-content')?.contains(e.target);
            if (!isClickInside) {
                closeAdminNavMenuModal();
            }
        }
    });

    // Event listener untuk memperbarui pratinjau ikon saat mengetik
    domUtils.addEventListener(formNavMenuIcon, 'input', function() {
        const iconClass = this.value.trim();
        if (iconPreview) {
            iconPreview.className = 'text-2xl text-gray-500';
            if (iconClass) {
                iconPreview.classList.add(...iconClass.split(' '));
            }
        }
    });

    domUtils.addEventListener(adminNavMenuForm, 'submit', async (e) => {
        e.preventDefault();
        console.log('[Form] Submit form nav menu');

        const loadingNotif = notificationManager.showNotification('Menyimpan menu...', 'loading');

        const method = formNavMenuMethod.value;
        const menuId = formNavMenuId.value;
        const categoryId = formNavMenuCategoryId.value;

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

        try {
            const options = { method: httpMethod, body: formData };
            if (method === 'PUT') options.headers = { 'X-HTTP-Method-Override': 'PUT' };

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeAdminNavMenuModal();

            const redirectUrl = `${APP_CONSTANTS.ROUTES.DOCS_BASE}/${data.current_category_slug}/${data.new_menu_link}`;
            window.location.href = redirectUrl;

        } catch (error) {
            console.error('[Form] Gagal simpan:', error);
            notificationManager.hideNotification(loadingNotif);
        }
    });
}