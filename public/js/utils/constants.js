// public/js/utils/constants.js

export const APP_CONSTANTS = {
    ROLES: {
        ADMIN: 'admin',
        ANGGOTA: 'anggota',
        GUEST: 'guest'
    },
    API_ROUTES: {
        CATEGORIES: {
            GET: '/api/categories', // Akan ditambah slug
            STORE: '/api/categories',
            UPDATE: '/api/categories', // Akan ditambah slug
            DESTROY: '/api/categories' // Akan ditambah slug
        },
        NAVMENU: {
            GET_ALL: '/api/navmenu/all', // Akan ditambah categorySlug
            PARENTS: '/api/navmenu/parents', // Akan ditambah categorySlug
            GET: '/api/navmenu', // Akan ditambah menu_id
            STORE: '/api/navmenu',
            UPDATE: '/api/navmenu', // Akan ditambah menu_id
            DESTROY: '/api/navmenu' // Akan ditambah menu_id
        },
        USECASE: {
            STORE: '/api/usecase',
            UPDATE: '/api/usecase', // Akan ditambah id
            DESTROY: '/api/usecase', // Akan ditambah id
            UAT_STORE: '/api/usecase/uat',
            UAT_UPDATE: '/api/usecase/uat', // Akan ditambah id
            UAT_DESTROY: '/api/usecase/uat', // Akan ditambah id
            REPORT_STORE: '/api/usecase/report',
            REPORT_UPDATE: '/api/usecase/report', // Akan ditambah id
            REPORT_DESTROY: '/api/usecase/report', // Akan ditambah id
            DATABASE_STORE: '/api/usecase/database',
            DATABASE_UPDATE: '/api/usecase/database', // Akan ditambah id
            DATABASE_DESTROY: '/api/usecase/database' // Akan ditambah id
        },
        SEARCH: '/api/search'
    },
    ROUTES: {
        LOGIN: '/login',
        DOCS_BASE: '/docs'
    }
};
