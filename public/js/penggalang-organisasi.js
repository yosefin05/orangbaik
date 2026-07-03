document.addEventListener('DOMContentLoaded', function () {
    const thumbnailInput = document.getElementById('thumbnailInput');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');

    const fotoProfilInput = document.getElementById('fotoProfilInput');
    const fotoProfilPreview = document.getElementById('fotoProfilPreview');

    function previewImage(input, callback) {
        const file = input.files && input.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (event) {
            callback(event.target.result);
        };

        reader.readAsDataURL(file);
    }

    if (thumbnailInput && thumbnailPreview) {
        thumbnailInput.addEventListener('change', function () {
            previewImage(thumbnailInput, function (imageUrl) {
                thumbnailPreview.src = imageUrl;
                thumbnailPreview.style.display = 'block';

                if (thumbnailPlaceholder) {
                    thumbnailPlaceholder.style.display = 'none';
                }
            });
        });
    }

    // Script untuk menghapus thumbnail
        document.getElementById('removeThumbnail')?.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin menghapus banner ini?')) {
                // Tampilkan placeholder
                document.getElementById('thumbnailPlaceholder').style.display = 'block';
                document.getElementById('thumbnailPreview').style.display = 'none';
                this.closest('.verify-cover-info').style.display = 'none';
                
                // Tambahkan input hidden untuk menandai penghapusan
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'remove_thumbnail';
                hiddenInput.value = '1';
                this.closest('.verify-cover-upload').appendChild(hiddenInput);
            }
        });

    if (fotoProfilInput && fotoProfilPreview) {
        fotoProfilInput.addEventListener('change', function () {
            previewImage(fotoProfilInput, function (imageUrl) {
                fotoProfilPreview.innerHTML = '';

                const image = document.createElement('img');

                image.src = imageUrl;
                image.alt = 'Preview logo organisasi';

                fotoProfilPreview.appendChild(image);
            });
        });
    }
});