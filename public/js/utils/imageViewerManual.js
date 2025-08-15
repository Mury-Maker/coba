import { domUtils } from '../core/domUtils.js';
import { notificationManager } from '../core/notificationManager.js';

let slideIndex = 1;
let currentGalleryImages = [];

// === Modal Control ===
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
        if (slidesContainer) slidesContainer.innerHTML = '';

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

// === Show Slides ===
function showSlides(n) {
    let slides = document.getElementsByClassName("mySlides");
    const modal = domUtils.getElement("myModal");
    const prevBtn = modal.querySelector('.prev-w3');
    const nextBtn = modal.querySelector('.next-w3');

    if (slides.length === 0) {
        if (prevBtn) domUtils.toggleClass(prevBtn, 'hidden', true);
        if (nextBtn) domUtils.toggleClass(nextBtn, 'hidden', true);
        return;
    }

    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }

    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
        domUtils.toggleClass(slides[i], 'zoomed', false);
    }

    if (slides[slideIndex-1]) {
        slides[slideIndex-1].style.display = "flex";
    }

    if (slides.length <= 1) {
        domUtils.toggleClass(prevBtn, 'hidden', true);
        domUtils.toggleClass(nextBtn, 'hidden', true);
    } else {
        domUtils.toggleClass(prevBtn, 'hidden', false);
        domUtils.toggleClass(nextBtn, 'hidden', false);
    }
}

// === Better Zoom Function ===
function enableBetterZoom(slideDiv, imgElement) {
    let scale = 1;
    let originX = 0;
    let originY = 0;
    let isDragging = false;
    let startX, startY;

    // Zoom pakai scroll wheel
    imgElement.addEventListener('wheel', function (e) {
        e.preventDefault();
        let zoomIntensity = 0.1;

        if (e.deltaY < 0) {
            scale += zoomIntensity;
        } else {
            scale = Math.max(1, scale - zoomIntensity);
        }

        let rect = imgElement.getBoundingClientRect();
        originX = ((e.clientX - rect.left) / rect.width) * 100;
        originY = ((e.clientY - rect.top) / rect.height) * 100;

        imgElement.style.transformOrigin = `${originX}% ${originY}%`;
        imgElement.style.transform = `scale(${scale})`;
    });

    // Drag saat zoom
    imgElement.addEventListener('mousedown', function (e) {
        if (scale > 1) {
            isDragging = true;
            startX = e.pageX - imgElement.offsetLeft;
            startY = e.pageY - imgElement.offsetTop;
            imgElement.style.cursor = 'grabbing';
        }
    });

    document.addEventListener('mouseup', function () {
        isDragging = false;
        imgElement.style.cursor = scale > 1 ? 'grab' : 'zoom-in';
    });

    document.addEventListener('mousemove', function (e) {
        if (!isDragging) return;
        e.preventDefault();
        imgElement.style.position = 'relative';
        imgElement.style.left = `${e.pageX - startX}px`;
        imgElement.style.top = `${e.pageY - startY}px`;
    });

    // Double click reset zoom
    imgElement.addEventListener('dblclick', function () {
        scale = 1;
        imgElement.style.transform = 'scale(1)';
        imgElement.style.left = '0px';
        imgElement.style.top = '0px';
        imgElement.style.cursor = 'zoom-in';
    });
}

// === Init Image Viewer ===
export function initImageViewerManual() {
    domUtils.addEventListener(document, 'click', (e) => {
        const clickedGalleryItem = e.target.closest('a.gallery-item');

        if (clickedGalleryItem) {
            e.preventDefault();
            e.stopPropagation();

            const galleryContainer = clickedGalleryItem.closest('.grid.gap-4');
            if (!galleryContainer) {
                notificationManager.showNotification("Gagal membuka galeri: Container tidak ditemukan.", "error");
                return;
            }

            const imageElements = galleryContainer.querySelectorAll('a.gallery-item');
            currentGalleryImages = [];
            let initialSlideIndex = 0;

            imageElements.forEach((el, index) => {
                const fullSrc = el.dataset.fullSrc || el.href;
                currentGalleryImages.push({ full: fullSrc });
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
        if (modal && modal.classList.contains('show')) {
            if (e.key === "ArrowRight") plusSlides(1);
            else if (e.key === "ArrowLeft") plusSlides(-1);
        }
    });
}

// === Populate Modal Slides ===
function populateModalContentInternal(imagesData, clickedIndex) {
    const modal = domUtils.getElement("myModal");
    if (!modal) {
        notificationManager.showNotification("Elemen modal viewer tidak ditemukan. Mohon refresh halaman.", "error");
        return;
    }

    const slidesContainer = modal.querySelector('#slidesContainer');
    if (!slidesContainer) {
        notificationManager.showNotification("Elemen kontainer slides tidak ditemukan.", "error");
        return;
    }

    slidesContainer.innerHTML = '';

    imagesData.forEach((imageData) => {
        const slideDiv = document.createElement('div');
        slideDiv.className = 'mySlides';

        const imgElement = document.createElement('img');
        imgElement.src = imageData.full;

        enableBetterZoom(slideDiv, imgElement);

        slideDiv.appendChild(imgElement);
        slidesContainer.appendChild(slideDiv);
    });

    window.openModal(clickedIndex + 1);
}
