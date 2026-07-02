document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('fotoProfilInput');
    const preview = document.getElementById('avatarPreview');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (event) {
            preview.innerHTML = '';

            const image = document.createElement('img');

            image.src = event.target.result;
            image.alt = 'Preview foto profil';

            preview.appendChild(image);
        };

        reader.readAsDataURL(file);
    });
});