// public/js/utils/auth.js

import { domUtils } from '../core/domUtils.js';

export const authUtils = {
    initLogoutButton: () => {
        console.log('authUtils.initLogoutButton dipanggil.'); // DEBUG
        const logoutForm = domUtils.getElement('logout-form');
        const logoutBtn = domUtils.getElement('logout-btn');
        const logoutFormMobile = domUtils.getElement('logout-form-mobile');
        const logoutBtnMobile = logoutFormMobile ? logoutFormMobile.querySelector('button') : null;

        const handleLogout = (form) => {
            console.log('Logout initiated.'); // DEBUG
            window.Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Anda akan logout dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Logout confirmed, submitting form.'); // DEBUG
                    form.submit();
                } else {
                    console.log('Logout cancelled.'); // DEBUG
                }
            });
        };

        if (logoutForm && logoutBtn) {
            domUtils.addEventListener(logoutBtn, 'click', (e) => {
                e.preventDefault();
                handleLogout(logoutForm);
            });
        } else {
            console.log('Desktop logout button or form not found.'); // DEBUG
        }

        if (logoutFormMobile && logoutBtnMobile) {
            domUtils.addEventListener(logoutBtnMobile, 'click', (e) => {
                e.preventDefault();
                handleLogout(logoutFormMobile);
            });
        } else {
            console.log('Mobile logout button or form not found.'); // DEBUG
        }
    }
};
