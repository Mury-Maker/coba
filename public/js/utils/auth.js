// public/js/utils/auth.js

import { domUtils } from '../core/domUtils.js';

export const authUtils = {
    initLogoutButton: () => {
        const logoutForm = domUtils.getElement('logout-form');
        const logoutBtn = domUtils.getElement('logout-btn');
        const logoutFormMobile = domUtils.getElement('logout-form-mobile'); // Untuk mobile
        const logoutBtnMobile = logoutFormMobile ? logoutFormMobile.querySelector('button') : null;

        const handleLogout = (form) => {
            window.Swal.fire({ // Menggunakan Swal dari window
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
                    form.submit();
                }
            });
        };

        if (logoutForm && logoutBtn) {
            domUtils.addEventListener(logoutBtn, 'click', (e) => {
                e.preventDefault();
                handleLogout(logoutForm);
            });
        }

        if (logoutFormMobile && logoutBtnMobile) {
            domUtils.addEventListener(logoutBtnMobile, 'click', (e) => {
                e.preventDefault();
                handleLogout(logoutFormMobile);
            });
        }
    }
};
