// public/js/app.js

// Import dari folder 'core'
import { apiClient } from './core/apiClient.js';
import { notificationManager } from './core/notificationManager.js';
import { domUtils } from './core/domUtils.js';
import { initGlobalModals } from './core/globalModals.js';

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
import { initGlobalContentDisplay as initDocGlobalContentDisplay } from './documentation/globalContentDisplay.js';

// Import dari folder 'utils'
import { authUtils } from './utils/auth.js';
import { APP_CONSTANTS } from './utils/constants.js';
import { initImageViewerManual } from './utils/imageViewerManual.js';

// Fungsi untuk memasang ulang semua event listener yang membutuhkan DOM manipulasi
function reattachAllEventListeners() {
    console.log('app.js: Re-attaching all event listeners...');
    const APP_DATA = window.APP_BLADE_DATA;

    if (APP_DATA.userRole === APP_CONSTANTS.ROLES.ADMIN) {
        initCategoryManager();
        initNavMenuManager();
        initUseCaseFormHandler();
        initUseCaseDetailDisplay();
        initUatDataManager();
        initReportDataManager();
        initDatabaseDataManager();
    }

    authUtils.initLogoutButton();
    initDocGlobalContentDisplay();
    initImageViewerManual();
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('app.js: DOMContentLoaded event fired!');

    // Panggil fungsi inisialisasi utama
    initGlobalModals();
    initSidebar();
    initHeader();
    initSearchModal();

    // Panggil semua listener setelah DOM selesai dimuat
    reattachAllEventListeners();
    window.reattachAllEventListeners = reattachAllEventListeners;

    console.log('app.js: All initializations complete.');
});
