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
    // Data global dari Blade (diambil dari body tag di layouts/docs.blade.php)
    window.APP_DATA = {
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        userRole: document.body.dataset.userRole,
        currentCategorySlug: document.body.dataset.currentCategory,
        currentMenuId: parseInt(document.body.dataset.currentMenuId) || null,
        // Data lain yang dibutuhkan secara global akan di-pass via window.APP_BLADE_DATA di .blade.php
        // seperti singleUseCase, useCases
    };

    // Inisialisasi komponen layout
    initSidebar();
    initHeader();
    initSearchModal();
    initGlobalContentDisplay();

    // Inisialisasi logika admin hanya jika user adalah admin
    if (window.APP_DATA.userRole === APP_CONSTANTS.ROLES.ADMIN) {
        initCategoryManager();
        initNavMenuManager();
        initUseCaseFormHandler();
        initUseCaseDetailDisplay();
        initUatDataManager();
        initReportDataManager();
        initDatabaseDataManager();
    }

    // Inisialisasi tombol logout
    authUtils.initLogoutButton();
});
