// public/js/layout/sidebar.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initSidebar() {
    console.log('initSidebar dipanggil.'); // DEBUG: Confirm initialization
    const mobileMenuToggle = domUtils.getElement('mobile-menu-toggle');
    const sidebar = domUtils.getElement('docs-sidebar');
    const backdrop = domUtils.getElement('sidebar-backdrop'); // Pastikan Anda memiliki elemen ini di Blade, jika tidak hapus

    // Toggle sidebar mobile
    if (mobileMenuToggle && sidebar && backdrop) {
        domUtils.addEventListener(mobileMenuToggle, 'click', () => {
            console.log('Mobile menu toggle clicked.'); // DEBUG
            domUtils.toggleClass(sidebar, 'show', true);
            domUtils.toggleClass(backdrop, 'show', true);
        });
        domUtils.addEventListener(backdrop, 'click', () => {
            console.log('Backdrop clicked, closing mobile menu.'); // DEBUG
            domUtils.toggleClass(sidebar, 'show', false);
            domUtils.toggleClass(backdrop, 'show', false);
        });
    } else {
        console.log('Mobile menu elements not found (mobileMenuToggle, sidebar, or backdrop).'); // DEBUG
    }

    const desktopSidebarToggle = domUtils.getElement('desktop-sidebar-toggle');
    if (desktopSidebarToggle && sidebar) {
        domUtils.addEventListener(desktopSidebarToggle, 'click', () => {
            console.log('Desktop sidebar toggle clicked.'); // DEBUG
            domUtils.toggleClass(sidebar, 'collapsed-desktop', !sidebar.classList.contains('collapsed-desktop'));
            domUtils.toggleClass(document.body, 'sidebar-collapsed', !document.body.classList.contains('sidebar-collapsed'));

            const icon = desktopSidebarToggle.querySelector('i');
            if (icon) {
                if (sidebar.classList.contains('collapsed-desktop')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-bars'); // Tetap fa-bars, tapi CSS akan ubah posisi
                    icon.title = 'Perluas Sidebar';
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-bars');
                    icon.title = 'Sembunyikan Sidebar';
                }
            }

            // Opsional: Tutup semua submenu saat sidebar dilipat
            if (sidebar.classList.contains('collapsed-desktop')) {
                const allOpenSubmenus = sidebar.querySelectorAll('.submenu-container.open');
                allOpenSubmenus.forEach(openSubmenu => {
                    domUtils.toggleClass(openSubmenu, 'open', false);
                    const relatedTrigger = openSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                    if (relatedTrigger) {
                        relatedTrigger.setAttribute('aria-expanded', 'false');
                        domUtils.toggleClass(relatedTrigger.querySelector('i'), 'open', false);
                    }
                });
            }
        });
    } else {
        console.log('Desktop sidebar toggle or sidebar element not found.'); // DEBUG
    }

    // Fungsi untuk mengelola dropdown submenu sidebar (klik panah)
    function handleSubmenuToggle(event) {
        const sidebarElement = domUtils.getElement('docs-sidebar');
        // Jika sidebar sedang collapsed dan di desktop, abaikan toggle submenu
        if (sidebarElement && sidebarElement.classList.contains('collapsed-desktop') && window.innerWidth >= 768) {
            console.log('Submenu toggle ignored in collapsed desktop mode.'); // DEBUG
            return;
        }

        event.preventDefault();
        event.stopPropagation(); // Mencegah event menyebar

        const submenuId = event.currentTarget.dataset.toggle;
        const submenu = domUtils.getElement(submenuId);
        const arrowIcon = event.currentTarget.querySelector('i');

        if (submenu) {
            const isCurrentlyOpen = submenu.classList.contains('open');
            console.log('Submenu toggled:', submenuId, 'Currently open:', isCurrentlyOpen); // DEBUG

            const parentLi = event.currentTarget.closest('li');
            if (parentLi) {
                const siblingSubmenus = parentLi.parentElement.querySelectorAll('.submenu-container.open');
                siblingSubmenus.forEach(siblingSubmenu => {
                    if (siblingSubmenu !== submenu && !submenu.contains(siblingSubmenu)) {
                        domUtils.toggleClass(siblingSubmenu, 'open', false);
                        const siblingTrigger = siblingSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                        if (siblingTrigger) {
                            siblingTrigger.setAttribute('aria-expanded', 'false');
                            domUtils.toggleClass(siblingTrigger.querySelector('i'), 'open', false);
                        }
                    }
                });
            }

            domUtils.toggleClass(submenu, 'open', !isCurrentlyOpen);
            event.currentTarget.setAttribute('aria-expanded', !isCurrentlyOpen);
            if (arrowIcon) {
                domUtils.toggleClass(arrowIcon, 'open', !isCurrentlyOpen);
            }
        } else {
            console.log('Submenu element not found for toggle:', submenuId); // DEBUG
        }
    }

    // Fungsi untuk mengelola klik pada parent menu saat sidebar minimize
    // Ini adalah fungsi baru yang ditambahkan
    function handleSubmenuToggle(event) {
        const sidebarElement = domUtils.getElement('docs-sidebar');
        if (sidebarElement && sidebarElement.classList.contains('collapsed-desktop') && window.innerWidth >= 768) {
            return;
        }
    
        event.preventDefault();
        event.stopPropagation();
    
        // Dapatkan elemen item menu (elemen <li>)
        const menuItemElement = event.currentTarget.closest('li');
    
        if (!menuItemElement) {
            console.error('Could not find parent <li> for the clicked item.');
            return;
        }
    
        const submenuId = event.currentTarget.dataset.toggle;
        const submenu = domUtils.getElement(submenuId);
        const arrowIcon = menuItemElement.querySelector('.menu-arrow-icon i'); // <-- Perubahan di sini!
    
        if (submenu) {
            const isCurrentlyOpen = submenu.classList.contains('open');
            console.log('Submenu toggled:', submenuId, 'Currently open:', isCurrentlyOpen);
    
            // Tutup submenu saudara (seperti logika yang sudah Anda buat)
            const siblingSubmenus = menuItemElement.parentElement.querySelectorAll('.submenu-container.open');
            siblingSubmenus.forEach(siblingSubmenu => {
                if (siblingSubmenu !== submenu && !submenu.contains(siblingSubmenu)) {
                    domUtils.toggleClass(siblingSubmenu, 'open', false);
                    const siblingTrigger = siblingSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                    if (siblingTrigger) {
                        siblingTrigger.setAttribute('aria-expanded', 'false');
                        const siblingIcon = siblingTrigger.closest('li').querySelector('.menu-arrow-icon i'); // Cari ikon di parent <li>
                        if (siblingIcon) {
                            domUtils.toggleClass(siblingIcon, 'open', false);
                        }
                    }
                }
            });
    
            // Ubah status submenu dan ikon panah
            domUtils.toggleClass(submenu, 'open', !isCurrentlyOpen);
            event.currentTarget.setAttribute('aria-expanded', !isCurrentlyOpen);
            if (arrowIcon) {
                domUtils.toggleClass(arrowIcon, 'open', !isCurrentlyOpen); // Ikon panah diubah
            }
        } else {
            console.log('Submenu element not found for toggle:', submenuId);
        }
    }

    // Fungsi untuk membuka parent menu dari item yang aktif saat load halaman
    function openActiveMenuParents() {
        const activeItemElement = sidebar.querySelector('.bg-blue-100'); // Cari menu yang aktif

        if (activeItemElement) {
            console.log('Active menu item found, opening parents.'); // DEBUG
            let currentElement = activeItemElement;
            while (currentElement && currentElement !== sidebar) {
                if (currentElement.classList.contains('submenu-container')) {
                    domUtils.toggleClass(currentElement, 'open', true);
                    const parentWrapper = currentElement.previousElementSibling;
                    const triggerButton = parentWrapper ? parentWrapper.querySelector('[data-toggle^="submenu-"]') : null;

                    if (triggerButton) {
                        const icon = triggerButton.querySelector('i');
                        if (icon) {
                            domUtils.toggleClass(icon, 'open', true);
                            triggerButton.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
                currentElement = currentElement.parentElement;
            }
        } else {
            console.log('No active menu item found for openActiveMenuParents.'); // DEBUG
        }
    }

    // Attach event listeners untuk submenu toggles
    function attachSubmenuEventListeners() {
        const submenuTriggers = document.querySelectorAll('[data-toggle^="submenu-"]');
        if (submenuTriggers.length > 0) {
            console.log('Attaching submenu event listeners. Found:', submenuTriggers.length, 'triggers.'); // DEBUG
        } else {
            console.log('No submenu triggers found.'); // DEBUG
        }

        submenuTriggers.forEach(trigger => {
            domUtils.removeEventListener(trigger, handleSubmenuToggle); // Clean up old listeners
            domUtils.addEventListener(trigger, 'click', handleSubmenuToggle);
        });

        // Attach event listener baru untuk klik parent menu saat sidebar minimize
        const parentLinks = document.querySelectorAll('.sidebar-menu-parent-link');
        parentLinks.forEach(link => {
            domUtils.removeEventListener(link, 'click', handleParentMenuClick); // Clean up old listeners
            domUtils.addEventListener(link, 'click', handleParentMenuClick);
        });
    }

    attachSubmenuEventListeners();
    openActiveMenuParents();

    window.refreshSidebarDropdowns = () => {
        console.log('refreshSidebarDropdowns called.'); // DEBUG
        attachSubmenuEventListeners();
        openActiveMenuParents();
    };

    // ADMIN: Event Listener untuk tombol admin di sidebar (delegasi event pada dokumen)
    if (window.APP_BLADE_DATA.userRole === APP_CONSTANTS.ROLES.ADMIN) {
        console.log('Sidebar: Admin mode detected, attaching menu action listeners.'); // DEBUG
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
                notificationManager.openAdminNavMenuModal('create', null, parentId);
            } else if (addChildMenuBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('--- Tombol "Tambah Sub Menu" diklik. ---'); // DEBUG
                const parentId = parseInt(addChildMenuBtn.dataset.parentId || '0');
                notificationManager.openAdminNavMenuModal('create', null, parentId);
            } else if (editNavMenuBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Edit NavMenu button clicked.'); // DEBUG
                const menuId = parseInt(editNavMenuBtn.dataset.menuId);
                apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.NAVMENU.GET}/${menuId}`)
                    .then(menuData => notificationManager.openAdminNavMenuModal('edit', menuData))
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
                notificationManager.openConfirmModal(`Apakah Anda yakin ingin menghapus menu "${menuNama}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua sub-menu terkait, serta seluruh konten (Aksi, UAT, Report, Database) di dalamnya.`, async () => {
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
            } else if (toggleSubmenuBtn) {
                // Ini adalah penanganan untuk panah toggle submenu, yang sudah ditangani oleh handleSubmenuToggle
                // Tidak perlu memanggil fungsi lain di sini karena event sudah ditangkap oleh listener lain
            }
        });
    }
}