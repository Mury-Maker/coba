// public/js/layout/header.js

import { domUtils } from '../core/domUtils.js';
import { notificationManager } from '../core/notificationManager.js';
import { apiClient } from '../core/apiClient.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initHeader() {
    console.log('Header initialized.'); // DEBUG: Confirm initialization
    const categoryDropdownBtn = domUtils.getElement('category-dropdown-btn');
    const categoryDropdownText = domUtils.getElement('category-button-text');
    const categoryDropdownMenu = domUtils.getElement('category-dropdown-menu');

    const categoryDropdownBtnMobile = domUtils.getElement('category-dropdown-btn-mobile');
    const categoryDropdownTextMobile = domUtils.getElement('category-button-text-mobile');
    const categoryDropdownMenuMobile = domUtils.getElement('category-dropdown-menu-mobile');

    const addCategoryBtnHeader = document.querySelector('#category-dropdown-menu [data-action="add-category"]');


    function updateCategoryButtonText(categoryKey, targetTextElement) {
        let categoryDisplayName = categoryKey.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        if (targetTextElement) {
            targetTextElement.textContent = categoryDisplayName;
        }
    }

    function reorderCategoryMenu(menuElement) {
        if (!menuElement) return;
        const epesantrenItem = menuElement.querySelector('a[data-category-key="epesantren"]');
        if (epesantrenItem) {
            const parentDiv = epesantrenItem.closest('div');
            if (parentDiv && parentDiv.parentElement) {
                parentDiv.parentElement.insertBefore(parentDiv, parentDiv.parentElement.firstChild);
            }
        }
    }

    reorderCategoryMenu(categoryDropdownMenu);
    reorderCategoryMenu(categoryDropdownMenuMobile);

    if (categoryDropdownBtn && categoryDropdownMenu) {
        domUtils.addEventListener(categoryDropdownBtn, 'click', (e) => {
            e.stopPropagation();
            const isOpen = categoryDropdownMenu.classList.toggle('open');
            const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-down, .fa-chevron-up');
            domUtils.toggleClass(chevronIcon, 'fa-chevron-up', isOpen);
            domUtils.toggleClass(chevronIcon, 'fa-chevron-down', !isOpen);
            console.log('Desktop category dropdown toggled.');
        });

        domUtils.addEventListener(document, 'click', (event) => {
            if (!categoryDropdownBtn.contains(event.target) && !categoryDropdownMenu.contains(event.target)) {
                domUtils.toggleClass(categoryDropdownMenu, 'open', false);
                const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-up', false);
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-down', true);
                }
            }
        });

        categoryDropdownMenu.querySelectorAll('a').forEach(item => {
            domUtils.addEventListener(item, 'click', (e) => {
                const newCategoryKey = item.dataset.categoryKey;
                updateCategoryButtonText(newCategoryKey, categoryDropdownText);
                domUtils.toggleClass(categoryDropdownMenu, 'open', false);
                const chevronIcon = categoryDropdownBtn.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-up', false);
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-down', true);
                }
                console.log('Desktop category item clicked:', newCategoryKey);
            });
        });
    } else {
        console.log('Desktop category dropdown elements not found.');
    }

    if (categoryDropdownBtnMobile && categoryDropdownMenuMobile) {
        domUtils.addEventListener(categoryDropdownBtnMobile, 'click', (e) => {
            e.stopPropagation();
            const isOpen = categoryDropdownMenuMobile.classList.toggle('open');
            const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-down, .fa-chevron-up');
            domUtils.toggleClass(chevronIcon, 'fa-chevron-up', isOpen);
            domUtils.toggleClass(chevronIcon, 'fa-chevron-down', !isOpen);
            console.log('Mobile category dropdown toggled.');
        });

        domUtils.addEventListener(document, 'click', (event) => {
            if (!categoryDropdownBtnMobile.contains(event.target) && !categoryDropdownMenuMobile.contains(event.target)) {
                domUtils.toggleClass(categoryDropdownMenuMobile, 'open', false);
                const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-up', false);
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-down', true);
                }
            }
        });

        categoryDropdownMenuMobile.querySelectorAll('a').forEach(item => {
            domUtils.addEventListener(item, 'click', (e) => {
                const newCategoryKey = item.dataset.categoryKey;
                updateCategoryButtonText(newCategoryKey, categoryDropdownTextMobile);
                domUtils.toggleClass(categoryDropdownMenuMobile, 'open', false);
                const chevronIcon = categoryDropdownBtnMobile.querySelector('.fa-chevron-up');
                if (chevronIcon) {
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-up', false);
                    domUtils.toggleClass(chevronIcon, 'fa-chevron-down', true);
                }
                console.log('Mobile category item clicked:', newCategoryKey);
            });
        });
    } else {
        console.log('Mobile category dropdown elements not found.');
    }

    // ADMIN: Event Listeners untuk tombol Add/Edit/Delete Kategori di header/sidebar
    if (window.APP_BLADE_DATA.userRole === APP_CONSTANTS.ROLES.ADMIN) {
        console.log('Header: Admin mode detected, attaching category action listeners.');
        domUtils.addEventListener(document, 'click', (e) => {
            const clickedAddCategoryBtn = e.target.closest('[data-action="add-category"]');
            const editCategoryBtn = e.target.closest('[data-action="edit-category"]');
            const deleteCategoryBtn = e.target.closest('[data-action="delete-category"]');

            if (clickedAddCategoryBtn) {
                if (addCategoryBtnHeader && addCategoryBtnHeader.contains(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Add Category button clicked (via direct target).');
                    // setTimeout(() => notificationManager.openAdminCategoryModal('create'), 0); // Removed setTimeout
                    notificationManager.openAdminCategoryModal('create');
                } else if (categoryDropdownMenu.contains(e.target) && clickedAddCategoryBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Add Category button clicked (via delegation within dropdown).');
                    // setTimeout(() => notificationManager.openAdminCategoryModal('create'), 0); // Removed setTimeout
                    notificationManager.openAdminCategoryModal('create');
                }
            } else if (editCategoryBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Edit Category button clicked.');
                const categorySlug = editCategoryBtn.dataset.slug;
                const categoryName = editCategoryBtn.dataset.name;
                // setTimeout(() => notificationManager.openAdminCategoryModal('edit', categoryName, categorySlug), 0); // Removed setTimeout
                notificationManager.openAdminCategoryModal('edit', categoryName, categorySlug);
            } else if (deleteCategoryBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Delete Category button clicked.');
                const categorySlug = deleteCategoryBtn.dataset.slug;
                const categoryName = deleteCategoryBtn.dataset.name;
                // PERBAIKAN DI SINI: Hapus setTimeout
                notificationManager.openConfirmModal(`Apakah Anda yakin ingin menghapus kategori "${categoryName}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua menu dan konten terkait.`, async () => {
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
                            window.location.reload();
                        }
                    } catch (error) {
                        notificationManager.hideNotification(loadingNotif);
                        // Error ditangani oleh apiClient
                    }
                });
            }
        });
    }
}
