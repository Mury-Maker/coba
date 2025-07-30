// public/js/documentation/globalContentDisplay.js

import { domUtils } from '../core/domUtils.js';

export function initGlobalContentDisplay() {
    console.log('initGlobalContentDisplay dipanggil.'); // DEBUG
    const backButton = domUtils.getElement('backButtonId');

    if (backButton) {
        domUtils.addEventListener(backButton, 'click', () => {
            console.log('Back button clicked.'); // DEBUG
            window.history.back();
        });
    } else {
        console.log('Back button not found for global content display.'); // DEBUG
    }
}
