// public/js/app.js

// Import dari folder 'core'
import { apiClient } from './core/apiClient.js';
import { notificationManager } from './core/notificationManager.js';
import { domUtils } from './core/domUtils.js';
import { initGlobalModals } from './core/globalModals.js'; // <<< Pastikan ini diimpor

// Import dari folder 'layout'
import { initSidebar } from './layout/sidebar.js';
import { initHeader } from './layout/header.js';
import { initSearchModal } from './layout/searchModal.js';

// Import dari folder 'admin'
import { initCategoryManager } from './admin/categoryManager.js';
import { initNavMenuManager } from './admin/navMenuManager.js';

// Import dari folder 'documentation'
import { initUseCaseFormHandler } from './documentation/useCase/useCaseFormHandler.js';
import { initUseCaseDetailDisplay } from './documentation/useCase/useCaseDetailDisplay.js';
import { initUatDataManager } from './documentation/useCase/uatDataManager.js';
import { initReportDataManager } from './documentation/useCase/reportDataManager.js';
import { initDatabaseDataManager } from './documentation/useCase/databaseDataManager.js';
import { initGlobalContentDisplay as initDocGlobalContentDisplay } from './documentation/globalContentDisplay.js'; // Rename to avoid conflict

// Import dari folder 'utils'
import { authUtils } from './utils/auth.js';
import { APP_CONSTANTS } from './utils/constants.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('app.js: DOMContentLoaded event fired!');

    const APP_DATA = window.APP_BLADE_DATA;
    console.log('app.js: window.APP_BLADE_DATA initialized:', APP_DATA);

    // >>> INI PERUBAHAN KRITIS: Pastikan initGlobalModals dipanggil di awal <<<
    // Ini adalah modul yang mengekspos openCommonConfirmModal ke window
    initGlobalModals();
    console.log('app.js: Global modals initialized.');

    // Inisialisasi komponen layout
    console.log('app.js: Initializing layout components...');
    initSidebar();
    initHeader();
    initSearchModal();
    initDocGlobalContentDisplay();
    console.log('app.js: Layout components initialized.');

    // Inisialisasi logika admin hanya jika user adalah admin
    if (APP_DATA.userRole === APP_CONSTANTS.ROLES.ADMIN) {
        console.log('app.js: Initializing admin components...');
        initCategoryManager();
        initNavMenuManager();
        initUseCaseFormHandler();
        initUseCaseDetailDisplay();
        initUatDataManager();
        initReportDataManager();
        initDatabaseDataManager();
        console.log('app.js: Admin components initialized.');
    } else {
        console.log('app.js: Not an admin. Skipping admin component initialization.');
    }

    // Inisialisasi tombol logout
    authUtils.initLogoutButton();
    console.log('app.js: All initializations complete.');
});
