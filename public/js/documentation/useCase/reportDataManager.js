import { domUtils } from '../../core/domUtils.js';
import { apiClient } from '../../core/apiClient.js';
import { notificationManager } from '../../core/notificationManager.js';
import { APP_CONSTANTS } from '../../utils/constants.js';

let selectedImageFilesMap = new Map();
let selectedDocumentFilesMap = new Map();

const IMAGE_MAX_SIZE = 5 * 1024 * 1024;
const DOCUMENT_MAX_SIZE = 5 * 1024 * 1024;
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

export function initReportDataManager() {
    const reportDataModal = domUtils.getElement('reportDataModal');
    const reportDataForm = domUtils.getElement('reportDataForm');
    const reportDataModalTitle = domUtils.getElement('reportDataModalTitle');
    const reportDataFormUseCaseId = domUtils.getElement('reportDataFormUseCaseId');
    const reportDataFormId = domUtils.getElement('reportDataFormId');
    const reportDataFormMethod = domUtils.getElement('reportDataFormMethod');
    const cancelReportDataFormBtn = domUtils.getElement('cancelReportDataFormBtn');
    const addReportDataBtn = domUtils.getElement('addReportDataBtn');
    const reportDataTableBody = domUtils.getElement('reportDataTableBody');

    const formReportAktor = domUtils.getElement('form_report_aktor');
    const formReportNama = domUtils.getElement('form_report_nama');
    const formReportKeterangan = domUtils.getElement('form_report_keterangan');

    const dropArea = domUtils.getElement('dropArea');
    const fileInputBtn = domUtils.getElement('fileInputBtn');
    const combinedFileInput = domUtils.getElement('combinedFileInput');
    const imagePreviewContainer = domUtils.getElement('imagePreviewContainer');
    const documentPreviewContainer = domUtils.getElement('documentPreviewContainer');

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
                if (file.size > IMAGE_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file gambar "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedImageFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File gambar "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedImageFilesMap.set(file.name, file);
                const previewElement = createCombinedImagePreviewElement(file);
                imagePreviewContainer.appendChild(previewElement);
                const noImageSpan = imagePreviewContainer.querySelector('span');
                if (noImageSpan && noImageSpan.textContent.includes('Tidak ada gambar')) {
                    noImageSpan.remove();
                }
            } else if (isDocument) {
                if (file.size > DOCUMENT_MAX_SIZE) {
                    notificationManager.showNotification(`Ukuran file dokumen "${file.name}" melebihi batas 5MB.`, 'error');
                    return;
                }
                if (selectedDocumentFilesMap.has(file.name)) {
                    notificationManager.showNotification(`File dokumen "${file.name}" sudah ada.`, 'warning');
                    return;
                }
                selectedDocumentFilesMap.set(file.name, file);
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

    domUtils.addEventListener(reportDataForm, 'click', (e) => {
        const deleteBtn = e.target.closest('.delete-file-btn');
        if (deleteBtn) {
            const fileName = deleteBtn.dataset.fileName;
            const fileType = deleteBtn.dataset.fileType;
            if (fileType === 'image') {
                selectedImageFilesMap.delete(fileName);
            } else if (fileType === 'document') {
                selectedDocumentFilesMap.delete(fileName);
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

            if (imagePreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedImageFilesMap.size === 0) {
                 imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
            if (documentPreviewContainer.querySelectorAll('.existing-file-preview').length === 0 && selectedDocumentFilesMap.size === 0) {
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

    function openReportDataModal(mode, reportData = null) {
        if (!reportDataForm) {
            notificationManager.showNotification("Elemen 'reportDataForm' tidak ditemukan.", "error");
            return;
        }

        reportDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedImageFilesMap.clear();
        selectedDocumentFilesMap.clear();

        const useCaseId = window.APP_BLADE_DATA.singleUseCase ? window.APP_BLADE_DATA.singleUseCase.id : null;
        if (!useCaseId) {
            notificationManager.showNotification('Tidak ada Use Case yang dipilih untuk menambahkan data Report.', "error");
            return;
        }
        reportDataFormUseCaseId.value = useCaseId;

        if (mode === 'create') {
            reportDataModalTitle.textContent = 'Tambah Data Report Baru';
            reportDataFormMethod.value = 'POST';
            reportDataFormId.value = '';
            formReportAktor.value = '';
            formReportNama.value = '';
            formReportKeterangan.value = '';
            combinedFileInput.value = null;
            documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
        } else if (mode === 'edit' && reportData) {
            reportDataModalTitle.textContent = 'Edit Data Report';
            reportDataFormMethod.value = 'PUT';
            reportDataFormId.value = reportData.id_report;

            formReportAktor.value = reportData.aktor || '';
            formReportNama.value = reportData.nama_report || '';
            formReportKeterangan.value = reportData.keterangan || '';

            // Perbaikan logika untuk menampilkan file yang sudah ada
            const hasDocuments = reportData.documents && Array.isArray(reportData.documents) && reportData.documents.length > 0;
            if (hasDocuments) {
                documentPreviewContainer.innerHTML = '';
                reportData.documents.forEach(document => {
                    const previewElement = createExistingDocumentPreviewElement(document);
                    documentPreviewContainer.appendChild(previewElement);
                });
            } else {
                documentPreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</span>';
            }

            const hasImages = reportData.images && Array.isArray(reportData.images) && reportData.images.length > 0;
            if (hasImages) {
                imagePreviewContainer.innerHTML = '';
                reportData.images.forEach(image => {
                    const previewElement = createExistingImagePreviewElement(image);
                    imagePreviewContainer.appendChild(previewElement);
                });
            } else {
                imagePreviewContainer.innerHTML = '<span class="text-gray-500 text-sm">Tidak ada gambar yang diunggah.</span>';
            }
        }
        domUtils.toggleModal(reportDataModal, true);
    }

    function closeReportDataModal() {
        domUtils.toggleModal(reportDataModal, false);
        reportDataForm.reset();
        imagePreviewContainer.innerHTML = '';
        documentPreviewContainer.innerHTML = '';
        selectedImageFilesMap.clear();
        selectedDocumentFilesMap.clear();
    }
    
    // Menambahkan event listener untuk menutup modal saat klik di luar form
    domUtils.addEventListener(document, 'click', (e) => {
        // Memeriksa apakah target klik berada di luar modal, tapi masih di dalam 'overlay' modal
        if (e.target === reportDataModal) {
            closeReportDataModal();
        }
    });

    domUtils.addEventListener(cancelReportDataFormBtn, 'click', closeReportDataModal);
    if (addReportDataBtn) {
        domUtils.addEventListener(addReportDataBtn, 'click', () => openReportDataModal('create'));
    }

    if (reportDataTableBody) {
        domUtils.addEventListener(reportDataTableBody, 'click', async (e) => {
            const editBtn = e.target.closest('.edit-report-btn');
            const deleteBtn = e.target.closest('.delete-report-btn');

            if (editBtn) {
                const reportId = parseInt(editBtn.dataset.id);
                const report = (window.APP_BLADE_DATA.singleUseCase?.reportData || []).find(item => item.id_report === reportId);
                if (report) {
                    openReportDataModal('edit', report);
                } else {
                    notificationManager.showNotification('Data Report yang ingin diedit tidak ditemukan.', 'error');
                }
            } else if (deleteBtn) {
                const reportId = parseInt(deleteBtn.dataset.id);
                window.openCommonConfirmModal('Yakin ingin menghapus data Report ini? Tindakan ini tidak dapat dibatalkan!', async () => {
                    const loadingNotif = notificationManager.showNotification('Menghapus data Report...', 'loading');
                    try {
                        const data = await apiClient.fetchAPI(`${APP_CONSTANTS.API_ROUTES.USECASE.REPORT_DESTROY}/${reportId}`, { method: 'DELETE' });
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

    domUtils.addEventListener(reportDataForm, 'submit', async (e) => {
        e.preventDefault();

        const loadingNotif = notificationManager.showNotification('Menyimpan data Report...', 'loading');
        const reportId = reportDataFormId.value;
        const method = reportDataFormMethod.value;
        let url = reportId ? `${APP_CONSTANTS.API_ROUTES.USECASE.REPORT_UPDATE}/${reportId}` : APP_CONSTANTS.API_ROUTES.USECASE.REPORT_STORE;

        const formData = new FormData();

        formData.append('_method', method);
        formData.append('use_case_id', reportDataFormUseCaseId.value);
        formData.append('aktor', formReportAktor.value);
        formData.append('nama_report', formReportNama.value);
        formData.append('keterangan', formReportKeterangan.value);

        reportDataForm.querySelectorAll('.existing-file-preview input[name="existing_images_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_images_kept[]', hiddenInput.value);
        });

        reportDataForm.querySelectorAll('.existing-file-preview input[name="existing_documents_kept[]"]').forEach(hiddenInput => {
            formData.append('existing_documents_kept[]', hiddenInput.value);
        });

        selectedImageFilesMap.forEach((file) => {
            formData.append('new_images[]', file);
        });
        selectedDocumentFilesMap.forEach((file) => {
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
            closeReportDataModal();
            window.location.reload();
        } catch (error) {
            console.error('Gagal menyimpan/memperbarui data Report: ' + error.message);
            notificationManager.hideNotification(loadingNotif);
        }
    });
}