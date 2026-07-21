// ============================================================
// IMAGE PREVIEW - ORANGBAIK.ID ADMIN
// Universal image preview untuk semua form di admin
// ============================================================

document.addEventListener('DOMContentLoaded', function() {

    console.log('🖼️ Image Preview loaded!');

    // ==========================================================
    // FUNGSI HELPER: DRAG & DROP
    // ==========================================================
    function setupDragDrop(dropzone, input) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        dropzone.addEventListener('dragenter', function() {
            this.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(e) {
            this.classList.remove('dragover');
            const dt = e.dataTransfer;
            if (dt.files && dt.files.length > 0) {
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
    }

    // ==========================================================
    // FUNGSI: SETUP SINGLE IMAGE PREVIEW (Thumbnail, Foto Profil)
    // ==========================================================
    function setupImagePreview(config) {
        const {
            inputId,
            dropzoneId,
            placeholderId,
            previewId,
            previewImgId,
            removeBtnId,
            isCircle = false,
            isThumbnail = false,
            maxSize = 2 * 1024 * 1024,
            onPreviewShow = null,
            onPreviewHide = null
        } = config;

        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        const dropzone = document.getElementById(dropzoneId);
        const placeholder = document.getElementById(placeholderId);
        const preview = document.getElementById(previewId);
        const previewImg = document.getElementById(previewImgId);
        const removeBtn = document.getElementById(removeBtnId);

        if (!dropzone || !placeholder || !preview || !previewImg) {
            return;
        }

        console.log('✅ Setup preview for:', inputId);

        // ==========================================================
        // PREVIEW FILE
        // ==========================================================
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            if (file.size > maxSize) {
                alert('Ukuran file maksimal ' + (maxSize / (1024 * 1024)) + ' MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;

                if (isCircle) {
                    // Foto Profil - Lingkaran
                    previewImg.style.borderRadius = '50%';
                    previewImg.style.aspectRatio = '1/1';
                    previewImg.style.objectFit = 'cover';
                    previewImg.style.width = '100%';
                    previewImg.style.height = '100%';
                } else if (isThumbnail) {
                    // Thumbnail - Persegi Panjang (16:9)
                    previewImg.style.borderRadius = '8px';
                    previewImg.style.aspectRatio = '16/9';
                    previewImg.style.objectFit = 'cover';
                    previewImg.style.width = '100%';
                    previewImg.style.height = '100%';
                }

                placeholder.style.display = 'none';
                preview.style.display = 'flex';
                dropzone.classList.add('has-preview');

                if (typeof onPreviewShow === 'function') {
                    onPreviewShow(dropzone);
                }
            }
            reader.readAsDataURL(file);
        });

        // ==========================================================
        // REMOVE FILE - Pakai Event Delegation
        // ==========================================================
        dropzone.addEventListener('click', function(e) {
            const target = e.target.closest('.btn-remove-image, .btn-remove-avatar, .btn-remove-thumbnail');
            
            if (target && preview.style.display !== 'none') {
                e.stopPropagation();
                e.preventDefault();
                
                console.log('🗑️ Hapus gambar untuk:', inputId);
                
                input.value = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                dropzone.classList.remove('has-preview');
                previewImg.src = '#';
                
                if (typeof onPreviewHide === 'function') {
                    onPreviewHide(dropzone);
                }
                
                input.dispatchEvent(new Event('change'));
            }
        });

        // ==========================================================
        // DRAG & DROP
        // ==========================================================
        setupDragDrop(dropzone, input);
    }

    // ==========================================================
    // FUNGSI: SETUP GALLERY PREVIEW (Multiple Images)
    // ==========================================================
    function setupGalleryPreview(config) {
        const {
            inputId,
            gridId,
            placeholderId,
            dropzoneId,
            maxFiles = 3,
            maxSize = 2 * 1024 * 1024,
            onPreviewShow = null,
            onPreviewHide = null
        } = config;

        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        const grid = document.getElementById(gridId);
        const placeholder = document.getElementById(placeholderId);
        const dropzone = document.getElementById(dropzoneId);

        if (!grid || !placeholder || !dropzone) {
            return;
        }

        console.log('✅ Setup gallery preview for:', inputId);

        let galleryFiles = [];

        // ==========================================================
        // RENDER GALLERY
        // ==========================================================
        function renderGallery() {
            const previews = grid.querySelectorAll('.gallery-preview-item');
            previews.forEach(el => el.remove());

            if (galleryFiles.length === 0) {
                placeholder.style.display = 'flex';
                dropzone.classList.remove('has-preview');
                if (typeof onPreviewHide === 'function') {
                    onPreviewHide(dropzone);
                }
                return;
            }

            placeholder.style.display = 'none';
            dropzone.classList.add('has-preview');
            
            if (typeof onPreviewShow === 'function') {
                onPreviewShow(dropzone);
            }

            galleryFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const item = document.createElement('div');
                    item.className = 'gallery-preview-item';
                    item.innerHTML = `
                        <img src="${event.target.result}" alt="Gambar ${index + 1}" />
                        <button type="button" class="btn-remove-image" data-index="${index}" title="Hapus gambar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <span class="gallery-index">${index + 1}</span>
                    `;
                    grid.appendChild(item);
                }
                reader.readAsDataURL(file);
            });
        }

        // ==========================================================
        // HAPUS GAMBAR
        // ==========================================================
        function removeGalleryImage(index) {
            if (index >= 0 && index < galleryFiles.length) {
                galleryFiles.splice(index, 1);

                const dataTransfer = new DataTransfer();
                galleryFiles.forEach(file => dataTransfer.items.add(file));
                input.files = dataTransfer.files;

                renderGallery();
            }
        }

        // ==========================================================
        // EVENT INPUT
        // ==========================================================
        input.addEventListener('change', function(e) {
            const newFiles = Array.from(this.files);
            let allFiles = [...galleryFiles, ...newFiles];

            if (allFiles.length > maxFiles) {
                alert('Maksimal ' + maxFiles + ' gambar. Saat ini sudah ada ' + galleryFiles.length + ' gambar.');
                this.value = '';
                return;
            }

            for (let file of newFiles) {
                if (file.size > maxSize) {
                    alert('Ukuran file maksimal ' + (maxSize / (1024 * 1024)) + ' MB.');
                    this.value = '';
                    return;
                }
            }

            galleryFiles = allFiles;

            const dataTransfer = new DataTransfer();
            galleryFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;

            renderGallery();
        });

        // ==========================================================
        // EVENT DELEGATION - TOMBOL HAPUS DI GALERI
        // ==========================================================
        grid.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-image');

            if (removeBtn) {
                e.stopPropagation();
                e.preventDefault();

                const index = parseInt(removeBtn.dataset.index);
                if (!isNaN(index)) {
                    console.log('🗑️ Hapus gallery index:', index);
                    removeGalleryImage(index);
                }
            }
        });

        // ==========================================================
        // DRAG & DROP
        // ==========================================================
        setupDragDrop(dropzone, input);
    }

    // ==========================================================
    // REGISTER SEMUA PREVIEW DI HALAMAN
    // ==========================================================

    // ==========================================================
    // 1. THUMBNAIL (Berita, Campaign) - Persegi Panjang 16:9
    // ==========================================================
    setupImagePreview({
        inputId: 'thumbnail',
        dropzoneId: 'thumbnailDropzone',
        placeholderId: 'thumbnailPlaceholder',
        previewId: 'thumbnailPreview',
        previewImgId: 'thumbnailPreviewImg',
        removeBtnId: 'thumbnailRemoveBtn',
        isThumbnail: true,
        onPreviewShow: function(dropzone) {
            const wrapper = document.getElementById('thumbnailUploadWrapper');
            if (wrapper) {
                wrapper.style.display = 'none';
            }
        },
        onPreviewHide: function(dropzone) {
            const wrapper = document.getElementById('thumbnailUploadWrapper');
            if (wrapper) {
                wrapper.style.display = 'flex';
            }
        }
    });

    // ==========================================================
    // 2. FOTO PROFIL (Testimoni, User, Penggalang) - Lingkaran
    // ==========================================================
    setupImagePreview({
        inputId: 'foto_profil',
        dropzoneId: 'foto_profilDropzone',
        placeholderId: 'foto_profilPlaceholder',
        previewId: 'foto_profilPreview',
        previewImgId: 'foto_profilPreviewImg',
        removeBtnId: 'foto_profilRemoveBtn',
        isCircle: true,
        onPreviewShow: function(dropzone) {
            const wrapper = document.getElementById('uploadBtnWrapper');
            if (wrapper) {
                wrapper.style.display = 'none';
            }
        },
        onPreviewHide: function(dropzone) {
            const wrapper = document.getElementById('uploadBtnWrapper');
            if (wrapper) {
                wrapper.style.display = 'flex';
            }
        }
    });

    // ==========================================================
    // 3. FOTO (Penggalang Dana) - Lingkaran
    // ==========================================================
    setupImagePreview({
        inputId: 'foto',
        dropzoneId: 'fotoDropzone',
        placeholderId: 'fotoPlaceholder',
        previewId: 'fotoPreview',
        previewImgId: 'fotoPreviewImg',
        removeBtnId: 'fotoRemoveBtn',
        isCircle: true
    });

    // ==========================================================
    // 4. GAMBAR GALERI (Berita) - Multiple
    // ==========================================================
    setupGalleryPreview({
        inputId: 'gambar',
        gridId: 'galleryPreviewGrid',
        placeholderId: 'galleryPlaceholder',
        dropzoneId: 'galleryDropzone',
        maxFiles: 3,
        onPreviewShow: function(dropzone) {
            const wrapper = document.getElementById('galleryUploadWrapper');
            if (wrapper) {
                wrapper.style.display = 'none';
            }
        },
        onPreviewHide: function(dropzone) {
            const wrapper = document.getElementById('galleryUploadWrapper');
            if (wrapper) {
                wrapper.style.display = 'flex';
            }
        }
    });

    // ==========================================================
    // 5. GAMBAR PENDUKUNG (Campaign) - Multiple
    // ==========================================================
    setupGalleryPreview({
        inputId: 'gambar_pendukung',
        gridId: 'gambar_pendukungGrid',
        placeholderId: 'gambar_pendukungPlaceholder',
        dropzoneId: 'gambar_pendukungDropzone',
        maxFiles: 5
    });

    // ==========================================================
    // 6. PACKAGE IMAGE (Campaign Package) - Persegi Panjang
    // ==========================================================
    setupImagePreview({
        inputId: 'package_image',
        dropzoneId: 'package_imageDropzone',
        placeholderId: 'package_imagePlaceholder',
        previewId: 'package_imagePreview',
        previewImgId: 'package_imagePreviewImg',
        removeBtnId: 'package_imageRemoveBtn',
        isThumbnail: true
    });

    console.log('✅ Image Preview setup complete!');
    console.log('   - Thumbnail: persegi panjang (16:9)');
    console.log('   - Foto Profil: lingkaran');
    console.log('   - Gallery: multiple images');
});