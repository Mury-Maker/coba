// public/js/utils/imageViewerManual.js

import { domUtils } from '../core/domUtils.js';
import { notificationManager } from '../core/notificationManager.js';

let slideIndex = 1;
let currentGalleryImages = [];

// --- FUNGSI GLOBAL YANG DIPANGGIL DARI ONCLICK INLINE DI HTML ---
window.openModal = function(initialSlide = 1) {
    const modal = domUtils.getElement("myModal");
    if (modal) {
        domUtils.toggleClass(modal, 'show', true);
        slideIndex = initialSlide;
        showSlides(slideIndex);
    }
}

window.closeModal = function() {
    const modal = domUtils.getElement("myModal");
    if (modal) {
        domUtils.toggleClass(modal, 'show', false);

        const slidesContainer = modal.querySelector('#slidesContainer');
        const thumbnailRow = modal.querySelector('#thumbnailRow');
        const captionTextElement = modal.querySelector('#caption');
        const numbertextElement = modal.querySelector('.numbertext');

        if (slidesContainer) slidesContainer.innerHTML = '';
        if (thumbnailRow) thumbnailRow.innerHTML = '';
        if (captionTextElement) captionTextElement.innerHTML = '';
        if (numbertextElement) numbertextElement.innerHTML = '';

        const prevBtn = modal.querySelector('.prev-w3');
        const nextBtn = modal.querySelector('.next-w3');
        if (prevBtn) domUtils.toggleClass(prevBtn, 'hidden', true);
        if (nextBtn) domUtils.toggleClass(nextBtn, 'hidden', true);
    }
}

window.plusSlides = function(n) {
    showSlides(slideIndex += n);
}

window.currentSlide = function(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("demo");
    let numbertextElement = document.querySelector(".numbertext");
    let captionTextElement = document.getElementById("caption");
    const modal = domUtils.getElement("myModal");
    const prevBtn = modal.querySelector('.prev-w3');
    const nextBtn = modal.querySelector('.next-w3');

    if (slides.length === 0) {
        if (prevBtn) domUtils.toggleClass(prevBtn, 'hidden', true);
        if (nextBtn) domUtils.toggleClass(nextBtn, 'hidden', true);
        if (numbertextElement) numbertextElement.innerHTML = '';
        if (captionTextElement) captionTextElement.innerHTML = 'Tidak ada gambar.';
        return;
    }

    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}

    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active-w3", "");
    }

    if (slides[slideIndex-1]) {
        slides[slideIndex-1].style.display = "block";
    }
    if (dots[slideIndex-1]) {
        dots[slideIndex-1].className += " active-w3";
    }

    if (numbertextElement) {
        numbertextElement.innerHTML = `${slideIndex} / ${slides.length}`;
    }
    if (captionTextElement && dots[slideIndex-1]) {
        captionTextElement.innerHTML = dots[slideIndex-1].alt;
    }

    if (prevBtn && nextBtn) {
        if (slides.length <= 1) {
            domUtils.toggleClass(prevBtn, 'hidden', true);
            domUtils.toggleClass(nextBtn, 'hidden', true);
        } else {
            domUtils.toggleClass(prevBtn, 'hidden', false);
            domUtils.toggleClass(nextBtn, 'hidden', false);
        }
    }
}

export function initImageViewerManual() {
    console.log('initImageViewerManual dipanggil.');

    domUtils.addEventListener(document, 'click', (e) => {
        const clickedGalleryItem = e.target.closest('a.gallery-item');

        if (clickedGalleryItem) {
            e.preventDefault();
            e.stopPropagation();

            const galleryContainer = clickedGalleryItem.closest('.grid.gap-4');
            if (!galleryContainer) {
                console.error("Gallery container not found for the clicked image.");
                notificationManager.showNotification("Gagal membuka galeri: Container tidak ditemukan.", "error");
                return;
            }

            const imageElements = galleryContainer.querySelectorAll('a.gallery-item');
            currentGalleryImages = [];
            let initialSlideIndex = 0;

            imageElements.forEach((el, index) => {
                const fullSrc = el.dataset.fullSrc || el.href;
                const caption = el.dataset.caption || '';
                currentGalleryImages.push({
                    full: fullSrc,
                    thumb: el.querySelector('img')?.src || fullSrc,
                    caption: caption
                });
                if (el === clickedGalleryItem) {
                    initialSlideIndex = index;
                }
            });

            if (currentGalleryImages.length === 0) {
                notificationManager.showNotification("Tidak ada gambar untuk ditampilkan.", "error");
                return;
            }

            populateModalContentInternal(currentGalleryImages, initialSlideIndex);
        }
    });
    domUtils.addEventListener(document, 'keydown', (e) => {
        const modal = domUtils.getElement("myModal");
        
        // Pastikan modal sedang terbuka
        if (modal && modal.classList.contains('show')) {
            if (e.key === "ArrowRight") {
                // Tombol Panah Kanan ditekan
                plusSlides(1);
            } else if (e.key === "ArrowLeft") {
                // Tombol Panah Kiri ditekan
                plusSlides(-1);
            }
        }
    });
}

function populateModalContentInternal(imagesData, clickedIndex) {
    const modal = domUtils.getElement("myModal");
    if (!modal) {
        console.error("Modal viewer (myModal) not found in DOM.");
        notificationManager.showNotification("Elemen modal viewer tidak ditemukan. Mohon refresh halaman.", "error");
        return;
    }

    const modalContentContainer = modal.querySelector('.modal-content-w3');
    if (!modalContentContainer) {
        console.error("Modal content container for image viewer not found.");
        notificationManager.showNotification("Elemen konten modal viewer tidak lengkap.", "error");
        return;
    }

    const slidesContainer = modal.querySelector('#slidesContainer');
    const captionTextElement = modal.querySelector('#caption');
    const thumbnailRow = modal.querySelector('#thumbnailRow');

    if (!slidesContainer) { console.error('slidesContainer not found in modal!'); return; }
    if (!captionTextElement) { console.error('captionTextElement not found in modal!'); return; }
    if (!thumbnailRow) { console.error('thumbnailRow not found in modal!'); return; }

    slidesContainer.innerHTML = '';
    captionTextElement.innerHTML = '';
    thumbnailRow.innerHTML = '';

    imagesData.forEach((imageData, index) => {
        // Slide item
        const slideDiv = document.createElement('div');
        slideDiv.className = 'mySlides fade';
        slideDiv.innerHTML = `
            <div class="numbertext"></div> <img src="${imageData.full}" style="width:100%">
        `;
        slidesContainer.appendChild(slideDiv);

        // Thumbnail item
        const columnDiv = document.createElement('div');
        columnDiv.className = 'column';
        columnDiv.innerHTML = `
            <img class="demo cursor hover-shadow" src="${imageData.thumb}" style="width:100%" onclick="window.currentSlide(${index + 1})" alt="${imageData.caption}">
        `;
        thumbnailRow.appendChild(columnDiv);
    });

    window.openModal(clickedIndex + 1);
}
