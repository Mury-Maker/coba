// public/js/layout/searchModal.js

import { domUtils } from '../core/domUtils.js';
import { apiClient } from '../core/apiClient.js';
import { notificationManager } from '../core/notificationManager.js';
import { APP_CONSTANTS } from '../utils/constants.js';

export function initSearchModal() {
    console.log('Search modal initialized.'); // DEBUG: Confirm initialization
    const openSearchModalBtnHeader = domUtils.getElement('open-search-modal-btn-header');
    const searchOverlay = domUtils.getElement('search-overlay');
    const searchOverlayInput = domUtils.getElement('search-overlay-input');
    const searchResultsList = domUtils.getElement('search-results-list');
    const clearSearchInputBtn = domUtils.getElement('clear-search-input-btn');
    const closeSearchOverlayBtn = domUtils.getElement('close-search-overlay-btn');

    const openSearchModal = () => {
        domUtils.toggleClass(searchOverlay, 'open', true);
        searchOverlayInput.value = '';
        searchOverlayInput.focus();
        searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
        domUtils.hideElement(clearSearchInputBtn);
        console.log('Search modal opened.');
    };

    const closeSearchModal = () => {
        domUtils.toggleClass(searchOverlay, 'open', false);
        searchOverlayInput.value = '';
        searchResultsList.innerHTML = '';
        domUtils.hideElement(clearSearchInputBtn);
        console.log('Search modal closed.');
    };

    if (openSearchModalBtnHeader) {
        domUtils.addEventListener(openSearchModalBtnHeader, 'click', openSearchModal);
    } else {
        console.log('Open search modal button not found.');
    }

    if (searchOverlay) {
        domUtils.addEventListener(searchOverlay, 'click', (e) => {
            if (e.target === searchOverlay || e.target.closest('#close-search-overlay-btn')) {
                closeSearchModal();
            }
        });
    }

    if (closeSearchOverlayBtn) {
        domUtils.addEventListener(closeSearchOverlayBtn, 'click', closeSearchModal);
    }

    domUtils.addEventListener(document, 'keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            console.log('Keyboard shortcut Ctrl+K/Cmd+K pressed.');
            openSearchModal();
        }
        if (e.key === 'Escape' && searchOverlay.classList.contains('open')) {
            console.log('Escape key pressed, closing search modal.');
            closeSearchModal();
        }
    });

    let searchTimeout;
    domUtils.addEventListener(searchOverlayInput, 'input', () => {
        clearTimeout(searchTimeout);
        const query = searchOverlayInput.value.trim();
        console.log('Search input changed. Query:', query);

        if (query.length > 0) {
            domUtils.showElement(clearSearchInputBtn);
        } else {
            domUtils.hideElement(clearSearchInputBtn);
            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
            return;
        }

        if (query.length < 2) {
            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Masukkan minimal 2 karakter untuk mencari.</p>';
            return;
        }

        searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mencari...</p>';

        searchTimeout = setTimeout(async () => {
            console.log('Performing search API call for query:', query);
            try {
                const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.SEARCH}?query=${query}`);
                searchResultsList.innerHTML = '';

                if (data.results && data.results.length > 0) {
                    const groupedResultsByMenuName = data.results.reduce((acc, result) => {
                        if (!acc[result.category_name]) {
                            acc[result.category_name] = [];
                        }
                        acc[result.category_name].push(result);
                        return acc;
                    }, {});

                    for (const groupName in groupedResultsByMenuName) {
                        const menuGroupHeader = document.createElement('div');
                        menuGroupHeader.className = 'search-result-category';
                        menuGroupHeader.textContent = groupName;
                        searchResultsList.appendChild(menuGroupHeader);

                        groupedResultsByMenuName[groupName].forEach(result => {
                            const itemLink = document.createElement('a');
                            itemLink.href = result.url;
                            itemLink.className = 'search-result-item px-6 py-3 block hover:bg-gray-100 rounded-md';
                            itemLink.innerHTML = `
                                <div class="search-title">${result.name}</div>
                                ${result.context && result.context !== 'Judul Menu' ? `<p class="search-context">${result.context}</p>` : ''}
                            `;
                            domUtils.addEventListener(itemLink, 'click', closeSearchModal);
                            searchResultsList.appendChild(itemLink);
                        });
                    }
                    console.log('Search results displayed:', data.results.length);
                } else {
                    searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Tidak ada hasil yang ditemukan.</p>';
                    console.log('No search results found.');
                }

            } catch (error) {
                searchResultsList.innerHTML = '<p class="text-center text-red-500 p-8">Terjadi kesalahan saat mencari.</p>';
                console.error('Search API call failed:', error);
            }
        }, 300);
    });

    if (clearSearchInputBtn) {
        domUtils.addEventListener(clearSearchInputBtn, 'click', () => {
            searchOverlayInput.value = '';
            searchOverlayInput.focus();
            domUtils.hideElement(clearSearchInputBtn);
            searchResultsList.innerHTML = '<p class="text-center text-gray-500 p-8">Mulai ketik untuk mencari...</p>';
            console.log('Clear search input button clicked.');
        });
    }
}
