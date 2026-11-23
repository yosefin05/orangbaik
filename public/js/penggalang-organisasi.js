(function () {
    function previewFile(input, target, placeholder, avatar) {
        if (!input || !target) return;
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                if (avatar) {
                    target.innerHTML = '<img src="' + event.target.result + '" alt="Preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                } else {
                    target.src = event.target.result;
                    target.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    previewFile(document.getElementById('thumbnailInput'), document.getElementById('thumbnailPreview'), document.getElementById('thumbnailPlaceholder'), false);
    previewFile(document.getElementById('fotoProfilInput'), document.getElementById('fotoProfilPreview'), null, true);
})();
