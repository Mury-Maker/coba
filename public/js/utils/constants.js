// public/js/utils/constants.js

export const APP_CONSTANTS = {
    ROLES: {
        ADMIN: 'admin',
        ANGGOTA: 'anggota',
        GUEST: 'guest'
    },
    API_ROUTES: {
        CATEGORIES: {
            GET: '/api/categories',
            STORE: '/api/categories',
            UPDATE: '/api/categories',
            DESTROY: '/api/categories'
        },
        NAVMENU: {
            GET_ALL: '/api/navmenu/all',
            PARENTS: '/api/navmenu/parents',
            GET: '/api/navmenu',
            STORE: '/api/navmenu',
            UPDATE: '/api/navmenu',
            DESTROY: '/api/navmenu'
        },
        USECASE: {
            STORE: '/api/usecase',
            UPDATE: '/api/usecase',
            DESTROY: '/api/usecase',
            UAT_STORE: '/api/usecase/uat',
            UAT_UPDATE: '/api/usecase/uat',
            UAT_DESTROY: '/api/usecase/uat',
            REPORT_STORE: '/api/usecase/report',
            REPORT_UPDATE: '/api/usecase/report',
            REPORT_DESTROY: '/api/usecase/report',
            DATABASE_STORE: '/api/usecase/database',
            DATABASE_UPDATE: '/api/usecase/database',
            DATABASE_DESTROY: '/api/usecase/database'
        },
        SEARCH: '/api/search'
    },
    ROUTES: {
        LOGIN: '/login',
        DOCS_BASE: '/docs'
    }
};
