import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

let selectedUatImageFilesMap = new Map();
let selectedUatDocumentFilesMap = new Map();

const IMAGE_MAX_SIZE = 5 * 1024 * 1024; // 5MB
const DOCUMENT_MAX_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_IMAGES_PER_SESSION = 25;
const MAX_DOCUMENTS_PER_SESSION = 5;

function getFileIcon(filename) {
    const extension = filename.split('.').pop().toLowerCase();
    switch (extension) {
        case 'pdf': return 'fas fa-file-pdf text-red-500';
        case 'doc':
        case 'docx': return 'fas fa-file-word text-blue-500';
        case 'xls':
        case 'xlsx': return 'fas fa-file-excel text-green-500';
        default: return 'fas fa-file text-gray-500';
    }
}

function createCombinedImagePreviewElement(file) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative group';
    const reader = new FileReader();
    reader.onload = (e) => {
        wrapper.innerHTML = `
            <img src="${e.target.result}" alt="${file.name}" class="w-full h-32 object-cover rounded-md border border-gray-300">
            <button type="button" class="absolute top-1 right-1 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity delete-file-btn" data-file-name="${file.name}" data-file-type="image">
                <i class="fas fa-times text-sm"></i>
            </button>
        `;
    };
    reader.readAsDataURL(file);

    return wrapper;
}

function createCombinedDocumentPreviewElement(file) {
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-md';
    const fileIcon = getFileIcon(file.name);
    wrapper.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="${fileIcon} fa-lg"></i>
            <span class="text-sm text-gray-700 truncate">${file.name}</span>
        </div>
        <button type="button" class="text-red-500 hover:text-red-700 delete-file-btn" data-file-name="${file.name}" data-file-type="document">
            <i class="fas fa-times"></i>
        </button>
    `;

    return wrapper;
}

function createExistingImagePreviewElement(fileData) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative group existing-file-preview';
    wrapper.innerHTML = `
        <a href="${fileData.path}" target="_blank" class="block">
            <img src="${fileData.path}" alt="${fileData.filename}" class="w-full h-32 object-cover rounded-md border border-gray-300">
        </a>
        <button type="button" class="absolute top-1 right-1 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity delete-existing-file-btn" data-file-id="${fileData.id}" data-file-type="image">
            <i class="fas fa-times text-sm"></i>
        </button>
    `;
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'existing_images_kept[]';
    hiddenInput.value = fileData.id;
    wrapper.appendChild(hiddenInput);

    return wrapper;
}

function createExistingDocumentPreviewElement(fileData) {
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-md existing-file-preview';
    const fileIcon = getFileIcon(fileData.filename);
    wrapper.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="${fileIcon} fa-lg"></i>
            <a href="${fileData.path}" target="_blank" class="text-sm text-blue-500 hover:underline truncate">${fileData.filename}</a>
        </div>
        <button type="button" class="text-red-500 hover:text-red-700 delete-existing-file-btn" data-file-id="${fileData.id}" data-file-type="document">
            <i class="fas fa-times"></i>
        </button>
    `;
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'existing_documents_kept[]';
    hiddenInput.value = fileData.id;
    wrapper.appendChild(hiddenInput);

    return wrapper;
}

export function initUatDataManager() {
    const uatDataModal = domUtils.getElement('uatDataModal');
    const uatDataModalTitle = domUtils.getElement('uatDataModalTitle');
    const uatDataForm = domUtils.getElement('uatDataForm');
    const uatDataFormUseCaseId = domUtils.getElement('uatDataFormUseCaseId');
    const uatDataFormId = domUtils.getElement('uatDataFormId');
    const uatDataFormMethod = domUtils.getElement('uatDataFormMethod');
    const cancelUatDataFormBtn = domUtils.getElement('cancelUatDataFormBtn');
    const addUatDataBtn = domUtils.getElement('addUatDataBtn');
    const uatDataTableBody = domUtils.getElement('uatDataTableBody');

    const formUatNamaProsesUsecase = domUtils.getElement('form_uat_nama_proses_usecase');
    const formUatKeterangan = domUtils.getElement('form_uat_keterangan');
    const formUatStatus = domUtils.getElement('form_uat_status');

    const dropArea = domUtils.getElement('uatDropArea');
    const fileInputBtn = domUtils.getElement('uatFileInputBtn');
    const combinedFileInput = domUtils.getElement('uatCombinedFileInput');
    const imagePreviewContainer = domUtils.getElement('uatImagePreviewContainer');
    const documentPreviewContainer = domUtils.getElement('uatDocumentPreviewContainer'); // Tambah kontainer dokumen

    function handleFileSelection(files) {
        if (files.length > MAX_IMAGES_PER_SESSION) {
            notificationManager.showNotification(`Maksimal ${MAX_IMAGES_PER_SESSION} file per sesi upload.`, 'error');
            return;
        }

        Array.from(files).forEach(file => {
            const isImage = file.type.startsWith('image/');
            const isDocument = file.type.includes('pdf') || file.type.includes('msword') || file.type.includes('wordprocessingml') || file.type.includes('excel') || file.type.includes('spreadsheetml');

            if (isImage) {
                const existingImagesCount = imagePreviewContainer.querySelectorAll('.existing-file-preview').length;
                const newImagesCount = selectedUatImageFilesMap.size;
                if (existingImagesCount + newImagesCount >= MAX_IMAGES_PER_SESSION) {
                    notificationManager.showNotification(`Maksimal ${MAX_IMAGES_PER_SESSION} file gambar yang bisa diunggah.`, 'error');
                    return;
                }
                if (file.size > IMAGE_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file gambar "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedUatImageFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File gambar "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedUatImageFilesMap.set(file.name, file);
                const previewElement = createCombinedImagePreviewElement(file);
                imagePreviewContainer.appendChild(previewElement);
                const noImageSpan = imagePreviewContainer.querySelector('span');
                if (noImageSpan && noImageSpan.textContent.includes('Tidak ada gambar')) {
                    noImageSpan.remove();
                }
            } else if (isDocument) { // Tambah logika dokumen
                 const existingDocumentsCount = documentPreviewContainer.querySelectorAll('.existing-file-preview').length;
                const newDocumentsCount = selectedUatDocumentFilesMap.size;
                if (existingDocumentsCount + newDocumentsCount >= MAX_DOCUMENTS_PER_SESSION) {
                    notificationManager.showNotification(`Maksimal ${MAX_DOCUMENTS_PER_SESSION} file dokumen yang bisa diunggah.`, 'error');
                    return;
                }
                if (file.size > DOCUMENT_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file dokumen "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedUatDocumentFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File dokumen "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedUatDocumentFilesMap.set(file.name, file);
                const previewElement = createCombinedDocumentPreviewElement(file);
                documentPreviewContainer.appendChild(previewElement);
                const noDocumentSpan = documentPreviewContainer.querySelector('span');
                if (noDocumentSpan && noDocumentSpan.textContent.includes('Tidak ada dokumen')) {
                    noDocumentSpan.remove();
                }
            } else {
                notificationManager.showNotification(`File "${file.name}" tidak didukung.`, 'error');
            }
        });
    }

    domUtils.addEventListener(uatDataForm, 'click', (e) => {
        const deleteBtn = e.target.closest('.delete-file-btn');
        if (deleteBtn) {
            const fileName = deleteBtn.dataset.fileName;
            const fileType = deleteBtn.dataset.fileType;
            if (fileType === 'image') {
                selectedUatImageFilesMap.delete(fileName);
            } else if (fileType === 'document') {
                selectedUatDocumentFilesMap.delete(fileName);
            }
            deleteBtn.closest('.relative, .flex').remove();

            if (imagePreviewContainer.children.length === 0) {
                 imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
            if (documentPreviewContainer.children.length === 0) {
                 documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }
        }

        const deleteExistingBtn = e.target.closest('.delete-existing-file-btn');
        if (deleteExistingBtn) {
            deleteExistingBtn.closest('.existing-file-preview').remove();

            if (imagePreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedUatImageFilesMap.size === 0) {
                 imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
            if (documentPreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedUatDocumentFilesMap.size === 0) {
                 documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }
        }
    });

    domUtils.addEventListener(dropArea, 'dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('border-blue-500');
    });

    domUtils.addEventListener(dropArea, 'dragleave', () => {
        dropArea.classList.remove('border-blue-500');
    });

    domUtils.addEventListener(dropArea, 'drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('border-blue-500');
        const files = e.dataTransfer.files;
        handleFileSelection(files);
    });

    domUtils.addEventListener(fileInputBtn, 'click', () => {
        combinedFileInput.click();
    });

    domUtils.addEventListener(combinedFileInput, 'change', (e) => {
        const files = e.target.files;
        handleFileSelection(files);
        combinedFileInput.value = null;
    });

    function openUatDataModal(mode, uatData = null) {
        if (!uatDataForm) {
            notificationManager.showNotification("Elemen 'uatDataForm' tidak ditemukan.", "error");
            return;
        }

        uatDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedUatImageFilesMap.clear();
        selectedUatDocumentFilesMap.clear();

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
            if (window.APP_BLADE_DATA.singleUseCase) {
                formUatNamaProsesUsecase.value = window.APP_BLADE_DATA.singleUseCase.nama_proses || '';
            }
            formUatKeterangan.value = '';
            formUatStatus.value = '';
            combinedFileInput.value = null;
            documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
        } else if (mode === 'edit' && uatData) {
            uatDataModalTitle.textContent = 'Edit Data UAT';
            uatDataFormMethod.value = 'PUT';
            uatDataFormId.value = uatData.id_uat;

            formUatNamaProsesUsecase.value = uatData.nama_proses_usecase || '';
            formUatKeterangan.value = uatData.keterangan_uat || '';
            formUatStatus.value = uatData.status_uat || '';

            if (uatData.documents && Array.isArray(uatData.documents) && uatData.documents.length > 0) {
                documentPreviewContainer.innerHTML = '';
                uatData.documents.forEach(document => {
                    documentPreviewContainer.appendChild(createExistingDocumentPreviewElement(document));
                });
            } else {
                documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }
            if (uatData.images && Array.isArray(uatData.images) && uatData.images.length > 0) {
                imagePreviewContainer.innerHTML = '';
                uatData.images.forEach(image => {
                    imagePreviewContainer.appendChild(createExistingImagePreviewElement(image));
                });
            } else {
                imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
        }
        domUtils.toggleModal(uatDataModal, true);
    }

    function closeUatDataModal() {
        domUtils.toggleModal(uatDataModal, false);
        uatDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedUatImageFilesMap.clear();
        selectedUatDocumentFilesMap.clear();
    }

    // Menambahkan event listener untuk menutup modal saat klik di luar form
    domUtils.addEventListener(document, 'click', (e) => {
        // Memeriksa apakah target klik berada di luar modal, tapi masih di dalam 'overlay' modal
        if (e.target === uatDataModal) {
            closeUatDataModal();
        }
    });

    domUtils.addEventListener(cancelUatDataFormBtn, 'click', closeUatDataModal);
    if (addUatDataBtn) {
        domUtils.addEventListener(addUatDataBtn, 'click', () => {
            openUatDataModal('create');
        });
    }

    if (uatDataTableBody) {
        domUtils.addEventListener(uatDataTableBody, 'click', async (e) => {
            const editBtn = e.target.closest('.edit-uat-btn');
            const deleteBtn = e.target.closest('.delete-uat-btn');

            if (editBtn) {
                const uatId = parseInt(editBtn.dataset.id);
                const uat = (window.APP_BLADE_DATA.singleUseCase?.uatData || []).find(item => item.id_uat === uatId);
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
                        console.error('API request GAGAL:', error);
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

        const formData = new FormData();

        formData.append('_method', method);
        formData.append('use_case_id', uatDataFormUseCaseId.value);
        formData.append('nama_proses_usecase', formUatNamaProsesUsecase.value);
        formData.append('keterangan_uat', formUatKeterangan.value);
        formData.append('status_uat', formUatStatus.value);

        uatDataForm.querySelectorAll('.existing-file-preview input[name="existing_images_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_images_kept[]', hiddenInput.value);
        });
        // Tambah input dokumen lama
        uatDataForm.querySelectorAll('.existing-file-preview input[name="existing_documents_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_documents_kept[]', hiddenInput.value);
        });

        selectedUatImageFilesMap.forEach((file) => {
            formData.append('new_images[]', file);
        });
        // Tambah input dokumen baru
        selectedUatDocumentFilesMap.forEach((file) => {
            formData.append('new_documents[]', file);
        });

        try {
            const options = {
                method: 'POST',
                body: formData,
            };
            if (method === 'PUT') {
                options.headers = { 'X-HTTP-Method-Override': 'PUT' };
            }

            const data = await apiClient.fetchAPI(url, options);

            notificationManager.hideNotification(loadingNotif);
            notificationManager.showCentralSuccessPopup(data.success);
            closeUatDataModal();
            window.location.reload();
        } catch (error) {
            console.error('Gagal menyimpan/memperbarui data UAT: ' + error.message);
            notificationManager.hideNotification(loadingNotif);
        }
    });

    // Tambahan untuk memastikan fungsi ini bisa diakses secara global jika diperlukan
    // window.openUatDataModal = openUatDataModal;
}