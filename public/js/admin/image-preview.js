// ============================================================
// IMAGE PREVIEW - ORANGBAIK.ID ADMIN
// Universal image preview untuk semua form di admin
// ============================================================

document.addEventListener('DOMContentLoaded', function() {

    console.log('🖼️ Image Preview loaded!');

    // ==========================================================
    // FUNGSI 1: THUMBNAIL PREVIEW (Persegi Panjang)
    // Untuk: thumbnail, gambar campaign, gambar berita
    // ==========================================================
    function setupThumbnailPreview(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const {
            dropzoneId = inputId + 'Dropzone',
            placeholderId = inputId + 'Placeholder',
            previewId = inputId + 'Preview',
            previewImgId = inputId + 'PreviewImg',
            removeBtnId = inputId + 'RemoveBtn',
            maxSize = 2 * 1024 * 1024,
            allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
        } = options;

        const dropzone = document.getElementById(dropzoneId);
        const placeholder = document.getElementById(placeholderId);
        const preview = document.getElementById(previewId);
        const previewImg = document.getElementById(previewImgId);
        const removeBtn = document.getElementById(removeBtnId);

        if (!dropzone || !placeholder || !preview || !previewImg) return;

        // ==========================================================
        // PREVIEW FILE
        // ==========================================================
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan: JPG, PNG, JPEG, WEBP.');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('Ukuran file maksimal ' + (maxSize / (1024 * 1024)) + ' MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;
                placeholder.style.display = 'none';
                preview.style.display = 'block';
                dropzone.classList.add('has-preview');
            }
            reader.readAsDataURL(file);
        });

        // ==========================================================
        // REMOVE FILE
        // ==========================================================
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                input.value = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                dropzone.classList.remove('has-preview');
                previewImg.src = '#';
                input.dispatchEvent(new Event('change'));
            });
        }

        // ==========================================================
        // DRAG & DROP
        // ==========================================================
        setupDragDrop(dropzone, input);
    }

    // ==========================================================
    // FUNGSI 2: FOTO PROFIL PREVIEW (Lingkaran)
    // Untuk: foto_profil, foto, avatar user/penggalang
    // ==========================================================
    function setupAvatarPreview(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const {
            dropzoneId = inputId + 'Dropzone',
            placeholderId = inputId + 'Placeholder',
            previewId = inputId + 'Preview',
            previewImgId = inputId + 'PreviewImg',
            removeBtnId = inputId + 'RemoveBtn',
            maxSize = 2 * 1024 * 1024,
            allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
        } = options;

        const dropzone = document.getElementById(dropzoneId);
        const placeholder = document.getElementById(placeholderId);
        const preview = document.getElementById(previewId);
        const previewImg = document.getElementById(previewImgId);
        const removeBtn = document.getElementById(removeBtnId);

        if (!dropzone || !placeholder || !preview || !previewImg) return;

        // ==========================================================
        // PREVIEW FILE - Dengan styling lingkaran
        // ==========================================================
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan: JPG, PNG, JPEG, WEBP.');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('Ukuran file maksimal ' + (maxSize / (1024 * 1024)) + ' MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;
                previewImg.style.borderRadius = '50%';
                previewImg.style.aspectRatio = '1/1';
                previewImg.style.objectFit = 'cover';
                previewImg.style.width = '100%';
                previewImg.style.height = '100%';
                placeholder.style.display = 'none';
                preview.style.display = 'block';
                dropzone.classList.add('has-preview');
            }
            reader.readAsDataURL(file);
        });

        // ==========================================================
        // REMOVE FILE
        // ==========================================================
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                input.value = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                dropzone.classList.remove('has-preview');
                previewImg.src = '#';
                input.dispatchEvent(new Event('change'));
            });
        }

        // ==========================================================
        // DRAG & DROP
        // ==========================================================
        setupDragDrop(dropzone, input);
    }

    // ==========================================================
    // FUNGSI 3: GALERI PREVIEW (Multiple Images)
    // Untuk: gambar, gambar_pendukung
    // ==========================================================
    function setupGalleryPreview(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const {
            gridId = inputId + 'Grid',
            placeholderId = inputId + 'Placeholder',
            dropzoneId = inputId + 'Dropzone',
            maxFiles = 3,
            maxSize = 2 * 1024 * 1024,
            allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
        } = options;

        const grid = document.getElementById(gridId);
        const placeholder = document.getElementById(placeholderId);
        const dropzone = document.getElementById(dropzoneId);

        if (!grid || !placeholder || !dropzone) return;

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
                return;
            }

            placeholder.style.display = 'none';
            dropzone.classList.add('has-preview');

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

                    const removeBtn = item.querySelector('.btn-remove-image');
                    removeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const idx = parseInt(this.dataset.index);
                        removeGalleryImage(idx);
                    });
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
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan: JPG, PNG, JPEG, WEBP.');
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
        // DRAG & DROP
        // ==========================================================
        setupDragDrop(dropzone, input);
    }

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
    // REGISTER SEMUA PREVIEW DI HALAMAN
    // ==========================================================

    // ==========================================================
    // 1. THUMBNAIL (Persegi Panjang)
    // Untuk: berita, campaign, dll
    // ==========================================================
    setupThumbnailPreview('thumbnail');

    // ==========================================================
    // 2. FOTO PROFIL (Lingkaran)
    // Untuk: user, penggalang, testimoni
    // ==========================================================
    setupAvatarPreview('foto_profil');
    setupAvatarPreview('foto');

    // ==========================================================
    // 3. GALERI GAMBAR (Multiple)
    // Untuk: berita, campaign
    // ==========================================================
    setupGalleryPreview('gambar', { maxFiles: 3 });
    setupGalleryPreview('gambar_pendukung', { maxFiles: 5 });

    // ==========================================================
    // 4. PACKAGE IMAGE (Campaign Package)
    // ==========================================================
    setupThumbnailPreview('package_image');

    console.log('✅ Image Preview setup complete!');
    console.log('   - Thumbnail: persegi panjang');
    console.log('   - Avatar: lingkaran');
    console.log('   - Gallery: multiple images');
});