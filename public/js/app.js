// public/js/app.js

// Import dari folder 'core'
import { apiClient } from './core/apiClient.js';
import { notificationManager } from './core/notificationManager.js';
import { domUtils } from './core/domUtils.js';

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
import { initGlobalContentDisplay } from './documentation/globalContentDisplay.js';

// Import dari folder 'utils'
import { authUtils } from './utils/auth.js';
import { APP_CONSTANTS } from './utils/constants.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('app.js: DOMContentLoaded event fired!');

    // Baca data dari window.APP_BLADE_DATA yang sudah diinisialisasi di Blade
    const APP_DATA = window.APP_BLADE_DATA;
    console.log('app.js: window.APP_BLADE_DATA initialized:', APP_DATA);

    // Inisialisasi komponen layout
    initSidebar();
    initHeader();
    initSearchModal();
    initGlobalContentDisplay();

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
    } else {
        console.log('app.js: Not an admin. Skipping admin component initialization.');
    }

    // Inisialisasi tombol logout
    authUtils.initLogoutButton();
    console.log('app.js: All initializations complete.');
});
