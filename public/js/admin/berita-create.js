// public/js/admin/berita-create.js

document.addEventListener('DOMContentLoaded', function() {

    console.log('Script berita-create.js loaded!');

    // ==========================================================
    // THUMBNAIL PREVIEW - FULL
    // ==========================================================
    const thumbnailInput = document.getElementById('thumbnail');
    const thumbnailDropzone = document.getElementById('thumbnailDropzone');
    const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const thumbnailPreviewImg = document.getElementById('thumbnailPreviewImg');
    const thumbnailRemoveBtn = document.getElementById('thumbnailRemoveBtn');

    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    thumbnailPreviewImg.src = event.target.result;
                    thumbnailPlaceholder.style.display = 'none';
                    thumbnailPreview.style.display = 'block';
                    thumbnailDropzone.classList.add('has-preview');
                }
                reader.readAsDataURL(file);
            }
        });

        if (thumbnailRemoveBtn) {
            thumbnailRemoveBtn.addEventListener('click', function(e) {
                e.stopPropagation(); // Hentikan event bubbling!
                e.preventDefault();
                console.log('Thumbnail remove clicked');
                thumbnailInput.value = '';
                thumbnailPreview.style.display = 'none';
                thumbnailPlaceholder.style.display = 'flex';
                thumbnailDropzone.classList.remove('has-preview');
            });
        }
    }

    // ==========================================================
    // GALLERY PREVIEW
    // ==========================================================
    const galleryInput = document.getElementById('gambar');
    const galleryGrid = document.getElementById('galleryPreviewGrid');
    const galleryPlaceholder = document.getElementById('galleryPlaceholder');
    const galleryDropzone = document.getElementById('galleryDropzone');
    let galleryFiles = [];

    // ==========================================================
    // RENDER GALLERY PREVIEWS
    // ==========================================================
    function renderGalleryPreviews() {
        console.log('renderGalleryPreviews called, files:', galleryFiles.length);

        // Clear grid (keep placeholder)
        const previews = galleryGrid.querySelectorAll('.gallery-preview-item');
        previews.forEach(el => el.remove());

        if (galleryFiles.length === 0) {
            galleryPlaceholder.style.display = 'flex';
            galleryDropzone.classList.remove('has-preview');
            return;
        }

        galleryPlaceholder.style.display = 'none';
        galleryDropzone.classList.add('has-preview');

        galleryFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const item = document.createElement('div');
                item.className = 'gallery-preview-item';
                item.dataset.index = index;
                item.innerHTML = `
                    <img src="${event.target.result}" alt="Gambar ${index + 1}" />
                    <button type="button" class="btn-remove-image" data-index="${index}" title="Hapus gambar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <span class="gallery-index">${index + 1}</span>
                `;
                galleryGrid.appendChild(item);
            }
            reader.readAsDataURL(file);
        });
    }

    // ==========================================================
    // HAPUS GAMBAR
    // ==========================================================
    function removeGalleryImage(index) {
        console.log('removeGalleryImage dipanggil, index:', index);

        if (index >= 0 && index < galleryFiles.length) {
            galleryFiles.splice(index, 1);

            // Update input files
            const dataTransfer = new DataTransfer();
            galleryFiles.forEach(file => dataTransfer.items.add(file));
            galleryInput.files = dataTransfer.files;

            renderGalleryPreviews();
        }
    }

    // ==========================================================
    // EVENT LISTENER GALLERY INPUT
    // ==========================================================
    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            console.log('Gallery change event triggered!');

            const newFiles = Array.from(this.files);
            const maxFiles = 3;
            const maxFileSize = 2 * 1024 * 1024;

            // Gabungkan file lama + file baru
            let allFiles = [...galleryFiles, ...newFiles];

            // Cek total file tidak boleh lebih dari 3
            if (allFiles.length > maxFiles) {
                alert('Maksimal hanya boleh upload 3 gambar. Saat ini sudah ada ' + galleryFiles.length + ' gambar.');
                this.value = '';
                return;
            }

            // Cek ukuran file baru
            for (let file of newFiles) {
                if (file.size > maxFileSize) {
                    alert('Ukuran tiap gambar maksimal 2 MB.');
                    this.value = '';
                    return;
                }
            }

            // Update galleryFiles dengan semua file
            galleryFiles = allFiles;

            // Update input files
            const dataTransfer = new DataTransfer();
            galleryFiles.forEach(file => dataTransfer.items.add(file));
            galleryInput.files = dataTransfer.files;

            renderGalleryPreviews();
        });
    }

    // ==========================================================
    // EVENT DELEGATION - TOMBOL HAPUS
    // DENGAN stopPropagation() AGAR TIDAK MEMICU INPUT FILE!
    // ==========================================================
    galleryGrid.addEventListener('click', function(e) {
        // Cari tahu apakah yang diklik adalah tombol hapus atau anaknya
        const removeBtn = e.target.closest('.btn-remove-image');

        if (removeBtn) {
            e.stopPropagation(); // Hentikan event bubbling ke parent (dropzone)!
            e.preventDefault();

            const index = parseInt(removeBtn.dataset.index);
            console.log('Tombol hapus diklik! Index:', index);

            if (!isNaN(index)) {
                removeGalleryImage(index);
            }
        }
    });

    // ==========================================================
    // DRAG & DROP SUPPORT
    // ==========================================================
    const dropzones = document.querySelectorAll('.upload-dropzone');

    dropzones.forEach(dropzone => {
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
            const input = this.querySelector('.upload-input');
            if (input) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // ==========================================================
    // FORM VALIDATION
    // ==========================================================
    const form = document.getElementById('beritaForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const judul = document.getElementById('judul').value.trim();
            const thumbnail = document.getElementById('thumbnail').files.length;
            const isi = document.getElementById('isi').value.trim();

            if (!judul) {
                e.preventDefault();
                alert('Judul berita wajib diisi.');
                document.getElementById('judul').focus();
                return;
            }

            if (!thumbnail) {
                e.preventDefault();
                alert('Thumbnail berita wajib diupload.');
                return;
            }

            if (!isi) {
                e.preventDefault();
                alert('Isi berita wajib diisi.');
                document.getElementById('isi').focus();
                return;
            }
        });
    }

});