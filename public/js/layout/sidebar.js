// public/js/layout/sidebar.js

import { domUtils } from '../core/domUtils.js';

export function initSidebar() {
    const mobileMenuToggle = domUtils.getElement('mobile-menu-toggle');
    const sidebar = domUtils.getElement('docs-sidebar');
    const backdrop = domUtils.getElement('sidebar-backdrop'); // Pastikan Anda memiliki elemen ini di Blade, jika tidak hapus

    // Toggle sidebar mobile
    if (mobileMenuToggle && sidebar && backdrop) {
        domUtils.addEventListener(mobileMenuToggle, 'click', () => {
            domUtils.toggleClass(sidebar, 'show', true);
            domUtils.toggleClass(backdrop, 'show', true);
        });
        domUtils.addEventListener(backdrop, 'click', () => {
            domUtils.toggleClass(sidebar, 'show', false);
            domUtils.toggleClass(backdrop, 'show', false);
        });
    }

    const desktopSidebarToggle = domUtils.getElement('desktop-sidebar-toggle');
    if (desktopSidebarToggle && sidebar) {
        domUtils.addEventListener(desktopSidebarToggle, 'click', () => {
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
                    icon.classList.add('fa-bars'); // Tetap fa-bars
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
    }

    // Fungsi untuk mengelola dropdown submenu sidebar (klik panah)
    function handleSubmenuToggle(event) {
        const sidebarElement = domUtils.getElement('docs-sidebar');
        // Jangan buka submenu jika sidebar dilipat di desktop
        if (sidebarElement && sidebarElement.classList.contains('collapsed-desktop') && window.innerWidth >= 768) {
            return;
        }

        event.preventDefault();
        event.stopPropagation(); // Mencegah event menyebar

        const submenuId = event.currentTarget.dataset.toggle;
        const submenu = domUtils.getElement(submenuId);
        const arrowIcon = event.currentTarget.querySelector('i');

        if (submenu) {
            const isCurrentlyOpen = submenu.classList.contains('open');

            // Tutup semua submenu lain pada level yang sama (sibling)
            const parentLi = event.currentTarget.closest('li');
            if (parentLi) {
                const siblingSubmenus = parentLi.parentElement.querySelectorAll('.submenu-container.open');
                siblingSubmenus.forEach(siblingSubmenu => {
                    if (siblingSubmenu !== submenu && !submenu.contains(siblingSubmenu)) { // Hindari menutup ancestor
                        domUtils.toggleClass(siblingSubmenu, 'open', false);
                        const siblingTrigger = siblingSubmenu.previousElementSibling.querySelector('[data-toggle^="submenu-"]');
                        if (siblingTrigger) {
                            siblingTrigger.setAttribute('aria-expanded', 'false');
                            domUtils.toggleClass(siblingTrigger.querySelector('i'), 'open', false);
                        }
                    }
                });
            }

            // Toggle submenu yang diklik
            domUtils.toggleClass(submenu, 'open', !isCurrentlyOpen);
            event.currentTarget.setAttribute('aria-expanded', !isCurrentlyOpen);
            if (arrowIcon) {
                domUtils.toggleClass(arrowIcon, 'open', !isCurrentlyOpen);
            }
        }
    }

    // Fungsi untuk membuka parent menu dari item yang aktif saat load halaman
    function openActiveMenuParents() {
        const activeItemElement = sidebar.querySelector('.bg-blue-100'); // Cari menu yang aktif

        if (activeItemElement) {
            let currentElement = activeItemElement;
            while (currentElement && currentElement !== sidebar) {
                if (currentElement.classList.contains('submenu-container')) {
                    domUtils.toggleClass(currentElement, 'open', true);
                    // Perbaiki cara mendapatkan triggerButton:
                    // Trigger button adalah elemen yang memiliki data-toggle="submenu-XYZ"
                    // dan merupakan sibling dari submenu-container
                    // Kita perlu naik ke parent <li>, lalu cari div.sidebar-menu-item-wrapper, lalu cari button
                    const parentWrapper = currentElement.previousElementSibling; // Ini adalah div.sidebar-menu-item-wrapper
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
        }
    }

    // Attach event listeners untuk submenu toggles
    function attachSubmenuEventListeners() {
        const submenuTriggers = document.querySelectorAll('[data-toggle^="submenu-"]');
        submenuTriggers.forEach(trigger => {
            // Hapus listener lama untuk mencegah duplikasi (penting saat refresh HTML sidebar)
            trigger.removeEventListener('click', handleSubmenuToggle);
            // Tambahkan listener baru
            domUtils.addEventListener(trigger, 'click', handleSubmenuToggle);
        });
    }

    // Panggil saat DOM siap
    attachSubmenuEventListeners();
    openActiveMenuParents();

    // Pastikan ini juga dipanggil saat sidebar di-refresh dari AJAX (misal setelah CRUD admin)
    window.refreshSidebarDropdowns = () => {
        attachSubmenuEventListeners();
        openActiveMenuParents();
    };
}
