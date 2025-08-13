import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

let selectedDatabaseImageFilesMap = new Map();
let selectedDatabaseDocumentFilesMap = new Map();

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

export function initDatabaseDataManager() {
    const databaseDataModal = domUtils.getElement('databaseDataModal');
    const databaseDataModalTitle = domUtils.getElement('databaseDataModalTitle');
    const databaseDataForm = domUtils.getElement('databaseDataForm');
    const databaseDataFormUseCaseId = domUtils.getElement('databaseDataFormUseCaseId');
    const databaseDataFormId = domUtils.getElement('databaseDataFormId');
    const databaseDataFormMethod = domUtils.getElement('databaseDataFormMethod');
    const cancelDatabaseDataFormBtn = domUtils.getElement('cancelDatabaseDataFormBtn');
    const addDatabaseDataBtn = domUtils.getElement('addDatabaseDataBtn');
    const databaseDataTableBody = domUtils.getElement('databaseDataTableBody');

    const formDatabaseKeterangan = domUtils.getElement('form_database_keterangan');
    const formDatabaseRelasi = domUtils.getElement('form_database_relasi');

    const dropArea = domUtils.getElement('databaseDropArea');
    const fileInputBtn = domUtils.getElement('databaseFileInputBtn');
    const combinedFileInput = domUtils.getElement('databaseCombinedFileInput');
    const imagePreviewContainer = domUtils.getElement('databaseImagePreviewContainer');
    const documentPreviewContainer = domUtils.getElement('databaseDocumentPreviewContainer');

    // START: Drag and Drop Logic
    // Mencegah perilaku default browser pada area drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    // Menambahkan kelas 'drag-over' saat file masuk ke area drop
    ['dragenter', 'dragover'].forEach(eventName => {
        domUtils.addEventListener(dropArea, eventName, highlight, false);
    });

    // Menghapus kelas 'drag-over' saat file keluar dari area drop
    ['dragleave', 'drop'].forEach(eventName => {
        domUtils.addEventListener(dropArea, eventName, unhighlight, false);
    });

    // Fungsi untuk mencegah perilaku default
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Fungsi untuk menambahkan kelas highlight
    function highlight() {
        dropArea.classList.add('drag-over');
    }

    // Fungsi untuk menghapus kelas highlight
    function unhighlight() {
        dropArea.classList.remove('drag-over');
    }
    // END: Drag and Drop Logic

    function handleFileSelection(files) {
        let totalImageUpload = 0;
        let totalDocumentUpload = 0;

        Array.from(files).forEach(file => {
            const isImage = file.type.startsWith('image/');
            const isDocument = file.type.includes('pdf') || file.type.includes('msword') || file.type.includes('wordprocessingml') || file.type.includes('excel') || file.type.includes('spreadsheetml');

            if (isImage) {
                totalImageUpload++;
            } else if (isDocument) {
                totalDocumentUpload++;
            }
        });

        if (totalImageUpload > MAX_IMAGES_PER_SESSION || totalDocumentUpload > MAX_DOCUMENTS_PER_SESSION) {
            notificationManager.showNotification(`Maksimal ${MAX_IMAGES_PER_SESSION} gambar dan ${MAX_DOCUMENTS_PER_SESSION} dokumen per sesi upload.`, 'error');
            return;
        }

        Array.from(files).forEach(file => {
            const isImage = file.type.startsWith('image/');
            const isDocument = file.type.includes('pdf') || file.type.includes('msword') || file.type.includes('wordprocessingml') || file.type.includes('excel') || file.type.includes('spreadsheetml');

            if (isImage) {
                const existingImagesCount = imagePreviewContainer.querySelectorAll('.existing-file-preview').length;
                const newImagesCount = selectedDatabaseImageFilesMap.size;
                if (existingImagesCount + newImagesCount >= MAX_IMAGES_PER_SESSION) {
                    notificationManager.showNotification(`Maksimal ${MAX_IMAGES_PER_SESSION} file gambar yang bisa diunggah.`, 'error');
                    return;
                }
                if (file.size > IMAGE_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file gambar "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedDatabaseImageFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File gambar "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedDatabaseImageFilesMap.set(file.name, file);
                const previewElement = createCombinedImagePreviewElement(file);
                imagePreviewContainer.appendChild(previewElement);
                const noImageSpan = imagePreviewContainer.querySelector('span');
                if (noImageSpan && noImageSpan.textContent.includes('Tidak ada gambar')) {
                    noImageSpan.remove();
                }
            } else if (isDocument) {
                const existingDocumentsCount = documentPreviewContainer.querySelectorAll('.existing-file-preview').length;
                const newDocumentsCount = selectedDatabaseDocumentFilesMap.size;
                if (existingDocumentsCount + newDocumentsCount >= MAX_DOCUMENTS_PER_SESSION) {
                    notificationManager.showNotification(`Maksimal ${MAX_DOCUMENTS_PER_SESSION} file dokumen yang bisa diunggah.`, 'error');
                    return;
                }
                if (file.size > DOCUMENT_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file dokumen "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedDatabaseDocumentFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File dokumen "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedDatabaseDocumentFilesMap.set(file.name, file);
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

    domUtils.addEventListener(databaseDataForm, 'click', (e) => {
        const deleteBtn = e.target.closest('.delete-file-btn');
        if (deleteBtn) {
            const fileName = deleteBtn.dataset.fileName;
            const fileType = deleteBtn.dataset.fileType;
            if (fileType === 'image') {
                selectedDatabaseImageFilesMap.delete(fileName);
            } else if (fileType === 'document') {
                selectedDatabaseDocumentFilesMap.delete(fileName);
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
            if (imagePreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedDatabaseImageFilesMap.size === 0) {
                 imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
            if (documentPreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedDatabaseDocumentFilesMap.size === 0) {
                 documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }
        }
    });

    domUtils.addEventListener(dropArea, 'drop', (e) => {
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

    function openDatabaseDataModal(mode, databaseData = null) {
        if (!databaseDataForm) {
            notificationManager.showNotification("Elemen 'databaseDataForm' tidak ditemukan.", "error");
            return;
        }

        databaseDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedDatabaseImageFilesMap.clear();
        selectedDatabaseDocumentFilesMap.clear();

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Database.', 'error');
            return;
        }
        databaseDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            databaseDataModalTitle.textContent = 'Tambah Data Database Baru';
            databaseDataFormMethod.value = 'POST';
            databaseDataFormId.value = '';
            formDatabaseKeterangan.value = '';
            formDatabaseRelasi.value = '';
            combinedFileInput.value = null;
            documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
        } else if (mode === 'edit' && databaseData) {
            databaseDataModalTitle.textContent = 'Edit Data Database';
            databaseDataFormMethod.value = 'PUT';
            databaseDataFormId.value = databaseData.id_database;

            formDatabaseKeterangan.value = databaseData.keterangan || '';
            formDatabaseRelasi.value = databaseData.relasi || '';

            // Tampilkan dokumen lama
            if (databaseData.documents && Array.isArray(databaseData.documents) && databaseData.documents.length > 0) {
                documentPreviewContainer.innerHTML = '';
                databaseData.documents.forEach(document => {
                    const previewElement = createExistingDocumentPreviewElement(document);
                    documentPreviewContainer.appendChild(previewElement);
                });
            } else {
                documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }
            // Tampilkan gambar lama
            if (databaseData.images && Array.isArray(databaseData.images) && databaseData.images.length > 0) {
                imagePreviewContainer.innerHTML = '';
                databaseData.images.forEach(image => {
                    const previewElement = createExistingImagePreviewElement(image);
                    imagePreviewContainer.appendChild(previewElement);
                });
            } else {
                imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
        }
        domUtils.toggleModal(databaseDataModal, true);
    }
    window.openDatabaseDataModal = openDatabaseDataModal;

    function closeDatabaseDataModal() {
        domUtils.toggleModal(databaseDataModal, false);
        databaseDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedDatabaseImageFilesMap.clear();
        selectedDatabaseDocumentFilesMap.clear();
    }

    // Menambahkan event listener untuk menutup modal saat klik di luar form
    domUtils.addEventListener(document, 'click', (e) => {
        // Memeriksa apakah target klik berada di luar modal, tapi masih di dalam 'overlay' modal
        if (e.target === databaseDataModal) {
            closeDatabaseDataModal();
        }
    });

    domUtils.addEventListener(cancelDatabaseDataFormBtn, 'click', closeDatabaseDataModal);
    if (addDatabaseDataBtn) {
        domUtils.addEventListener(addDatabaseDataBtn, 'click', () => {
            openDatabaseDataModal('create');
        });
    }

    if (databaseDataTableBody) {
        domUtils.addEventListener(databaseDataTableBody, 'click', async (e) => {
            const editBtn = e.target.closest('.edit-database-btn');
            const deleteBtn = e.target.closest('.delete-database-btn');

            if (editBtn) {
                const databaseId = parseInt(editBtn.dataset.id);
                const database = (window.APP_BLADE_DATA.singleUseCase?.database_data || []).find(item => item.id_database === databaseId);
                if (database) {
                    openDatabaseDataModal('edit', database);
                } else {
                    notificationManager.showNotification('Data Database yang ingin diedit tidak ditemukan di cache.', 'error');
                }
            } else if (deleteBtn) {
                const databaseId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data Database ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data Database...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_DESTROY}/${databaseId}`, { method: 'DELETE' });
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

    domUtils.addEventListener(databaseDataForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan data Database...', 'loading');
        const databaseId = databaseDataFormId.value;
        const method = databaseDataFormMethod.value;
        let url = databaseId ? `${APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_UPDATE}/${databaseId}` : APP_CONSTANTS.API_ROUTES.USECASE.DATABASE_STORE;

        const formData = new FormData();

        formData.append('_method', method);
        formData.append('use_case_id', databaseDataFormUseCaseId.value);
        formData.append('keterangan', formDatabaseKeterangan.value);
        formData.append('relasi', formDatabaseRelasi.value);

        databaseDataForm.querySelectorAll('.existing-file-preview input[name="existing_images_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_images_kept[]', hiddenInput.value);
        });
        databaseDataForm.querySelectorAll('.existing-file-preview input[name="existing_documents_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_documents_kept[]', hiddenInput.value);
        });

        selectedDatabaseImageFilesMap.forEach((file) => {
            formData.append('new_images[]', file);
        });
        selectedDatabaseDocumentFilesMap.forEach((file) => {
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
            closeDatabaseDataModal();
            window.location.reload();
        } catch (error) {
            console.error('Gagal menyimpan/memperbarui data Database: ' + error.message);
            notificationManager.hideNotification(loadingNotif);
        }
    });
}
