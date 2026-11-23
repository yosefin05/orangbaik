(function () {
    const input = document.getElementById('fotoProfilInput');
    const preview = document.getElementById('avatarPreview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (event) {
            preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview foto profil" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
        };
        reader.readAsDataURL(file);
    });
})();
