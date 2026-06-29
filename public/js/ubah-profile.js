document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (!avatarInput || !avatarPreview) {
        return;
    }

    avatarInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        const imageUrl = URL.createObjectURL(file);

        avatarPreview.innerHTML = `
            <img src="${imageUrl}" alt="Preview Foto Profile">
        `;
    });
});