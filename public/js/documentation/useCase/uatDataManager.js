// public/js/documentation/useCase/uatDataManager.js

import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';
import { initImagePreviewer } from '../../utils/imagePreviewer.js';

export function initUatDataManager() {
    const uatDataModal = domUtils.getElement('uatDataModal');
    const uatDataModalTitle = domUtils.getElement('uatDataModalTitle');
    const uatDataForm = domUtils.getElement('uatDataForm');
    const uatDataFormUseCaseId = domUtils.getElement('uatDataFormUseCaseId');
    const uatDataFormId = domUtils.getElement('uatDataFormId');
    const uatDataFormMethod = domUtils.getElement('uatDataFormMethod');
    const cancelUatDataFormBtn = domUtils.getElement('cancelUatDataFormBtn');
    const addUatDataBtn = domUtils.getElement('addUatDataBtn'); // Tombol "Tambah" di halaman detail use case
    const uatDataTableBody = domUtils.getElement('uatDataTableBody'); // Tabel daftar UAT di halaman detail use case

    const formUatNamaProsesUsecase = domUtils.getElement('form_uat_nama_proses_usecase');
    const formUatKeterangan = domUtils.getElement('form_uat_keterangan');
    const formUatStatus = domUtils.getElement('form_uat_status');
    const formUatImagesInput = domUtils.getElement('form_uat_images');
    const formUatImagesPreview = domUtils.getElement('form_uat_images_preview');
    const existingUatImagesContainer = domUtils.getElement('existing_uat_images_container');

    let currentUatImages = []; // Menyimpan daftar path gambar UAT saat ini (untuk edit)

    /**
     * Membuka modal UAT Data.
     * @param {'create' | 'edit'} mode - Mode operasi.
     * @param {object} [uatData=null] - Objek UAT Data untuk mode edit.
     */
    function openUatDataModal(mode, uatData = null) {
        if (!uatDataForm) {
            notificationManager.showNotification("Elemen 'uatDataForm' tidak ditemukan.", "error");
            return;
        }

        uatDataForm.reset();
        formUatImagesPreview.innerHTML = ''; // Bersihkan pratinjau
        existingUatImagesContainer.innerHTML = ''; // Bersihkan input hidden untuk gambar lama
        currentUatImages = []; // Reset daftar gambar saat ini

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data UAT.', 'error');
            return;
        }
        uatDataFormUseCaseId.value = useCaseId;


        if (mode === 'create') {
            uatDataModalTitle.textContent = 'Tambah Data UAT Baru';
            uatDataFormMethod.value = 'POST';
            uatDataFormId.value = '';
            // Isi nama proses usecase dari use case utama yang sedang dilihat
            if (window.APP_BLADE_DATA.singleUseCase) {
                formUatNamaProsesUsecase.value = window.APP_BLADE_DATA.singleUseCase.nama_proses || '';
            }
            formUatKeterangan.value = '';
            formUatStatus.value = '';
        } else if (mode === 'edit' && uatData) {
            uatDataModalTitle.textContent = 'Edit Data UAT';
            uatDataFormMethod.value = 'POST'; // Untuk FormData PUT
            uatDataFormId.value = uatData.id_uat;

            formUatNamaProsesUsecase.value = uatData.nama_proses_usecase || '';
            formUatKeterangan.value = uatData.keterangan_uat || '';
            formUatStatus.value = uatData.status_uat || '';

            // Tampilkan gambar-gambar UAT yang sudah ada
            if (uatData.images && uatData.images.length > 0) {
                currentUatImages = uatData.images.map(img => img.path); // Simpan path gambar saat ini
                uatData.images.forEach(image => {
                    formUatImagesPreview.appendChild(createImagePreviewElement(image.path, image.filename, image.id));
                    // Tambahkan input hidden untuk gambar yang sudah ada (untuk tracking mana yang dipertahankan)
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'existing_uat_images[]';
                    hiddenInput.value = image.path;
                    existingUatImagesContainer.appendChild(hiddenInput);
                });
            }
        }
        domUtils.toggleModal(uatDataModal, true);

        // Inisialisasi image previewer setelah modal terbuka dan form terisi
        // Ini memastikan elemen target ada di DOM.
        initImagePreviewer(formUatImagesInput, formUatImagesPreview);
    }

    /**
     * Menutup modal UAT Data.
     */
    function closeUatDataModal() {
        domUtils.toggleModal(uatDataModal, false);
        uatDataForm.reset();
        formUatImagesPreview.innerHTML = '';
        existingUatImagesContainer.innerHTML = '';
        currentUatImages = [];
        formUatImagesInput.value = ''; // Bersihkan input file
    }

    domUtils.addEventListener(cancelUatDataFormBtn, 'click', closeUatDataModal);

    // Event listener untuk tombol "Tambah" di halaman detail use case
    if (addUatDataBtn) {
        domUtils.addEventListener(addUatDataBtn, 'click', () => {
            openUatDataModal('create');
        });
    }

    // Delegasi event untuk tombol Edit dan Delete di tabel UAT
    if (uatDataTableBody) {
        domUtils.addEventListener(uatDataTableBody, 'click', async (e) => {
            const viewBtn = e.target.closest('.btn-action.bg-blue-500'); // Tombol detail
            const editBtn = e.target.closest('.edit-uat-btn');
            const deleteBtn = e.target.closest('.delete-uat-btn');

            if (viewBtn) {
                const uatId = parseInt(viewBtn.dataset.id);
                const uat = window.APP_BLADE_DATA.singleUseCase.uat_data.find(item => item.id_uat === uatId);
                if (uat) {
                    let imagesHtml = '<p class="text-gray-500 italic">Tidak ada gambar UAT.</p>';
                    if (uat.images && uat.images.length > 0) {
                        imagesHtml = `<div class="grid grid-cols-2 gap-2 mt-2">`;
                        uat.images.forEach(img => {
                            imagesHtml += `<div class="border rounded-lg overflow-hidden shadow-sm"><img src="${img.path}" alt="${img.filename}" class="w-full h-auto object-cover"><p class="p-1 text-xs text-gray-600 truncate">${img.filename}</p></div>`;
                        });
                        imagesHtml += `</div>`;
                    }
                    window.openCommonDetailModal('Detail Data UAT', `
                        <div class="detail-item">
                            <label>ID UAT:</label><p>${uat.id_uat}</p>
                        </div>
                        <div class="detail-item">
                            <label>Nama Proses Usecase:</label><p>${uat.nama_proses_usecase || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label><p class="prose max-w-none">${uat.keterangan_uat || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label><p>${uat.status_uat || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <label>Gambar UAT:</label>${imagesHtml}
                        </div>
                    `);
                } else {
                    notificationManager.showNotification('Detail data UAT tidak ditemukan.', 'error');
                }
            } else if (editBtn) {
                const uatId = parseInt(editBtn.dataset.id);
                const uat = window.APP_BLADE_DATA.singleUseCase.uat_data.find(item => item.id_uat === uatId);
                if (uat) {
                    openUatDataModal('edit', uat);
                } else {
                    notificationManager.showNotification('Data UAT yang ingin diedit tidak ditemukan.', 'error');
                }
            } else if (deleteBtn) {
                const uatId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data UAT ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data UAT...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.UAT_DESTROY}/${uatId}`, { method: 'DELETE' });
                        notificationManager.hideNotification(loadingNotif);
                        notificationManager.showCentralSuccessPopup(data.success);
                        window.location.reload();
                    } catch (error) {
                        notificationManager.hideNotification(loadingNotif);
                    }
                });
            }
        });
    }

    domUtils.addEventListener(uatDataForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan data UAT...', 'loading');
        const uatId = uatDataFormId.value;
        const method = uatDataFormMethod.value;
        let url = uatId ? `${APP_CONSTANTS.API_ROUTES.USECASE.UAT_UPDATE}/${uatId}` : APP_CONSTANTS.API_ROUTES.USECASE.UAT_STORE;
        let httpMethod = 'POST'; // Akan selalu POST untuk FormData

        const formData = new FormData(uatDataForm);

        // Tambahkan kembali path gambar yang sudah ada (jika tidak dihapus dari preview)
        existingUatImagesContainer.querySelectorAll('input[type="hidden"]').forEach(input => {
            formData.append(input.name, input.value);
        });

        // Masalah: FormData.append('_method', 'PUT') tidak berfungsi dengan HTTP method override
        // Solusi: Kirimkan method override sebagai header. apiClient sudah menangani ini.

        try {
            const options = {
                method: httpMethod,
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUatDataModal();
            window.location.reload(); // Reload halaman untuk melihat perubahan
        } catch (error) {
            notificationManager.hideNotification(loadingNotif);
        }
    });

    // Fungsi untuk membuat elemen pratinjau gambar dengan tombol hapus
    function createImagePreviewElement(src, filename, imageId) {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative group border rounded-md p-1';
        wrapper.innerHTML = `
            <img src="${src}" alt="${filename}" class="w-full h-24 object-cover rounded-sm">
            <p class="text-xs text-gray-600 truncate mt-1">${filename}</p>
            <button type="button" class="absolute top-0 right-0 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn" data-image-id="${imageId}" data-image-path="${src}">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="existing_uat_images[]" value="${src}">
        `;

        const removeButton = wrapper.querySelector('.remove-image-btn');
        domUtils.addEventListener(removeButton, 'click', () => {
            wrapper.remove();
            // Hapus juga dari currentUatImages jika ada, untuk memastikan tidak dikirim ulang saat edit
            currentUatImages = currentUatImages.filter(path => path !== src);
            // Hapus input hidden yang terkait dengan gambar ini
            const hiddenInputToRemove = existingUatImagesContainer.querySelector(`input[value="${src}"]`);
            if(hiddenInputToRemove) hiddenInputToRemove.remove();
        });
        return wrapper;
    }
}
