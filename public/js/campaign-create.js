document.addEventListener('DOMContentLoaded', function () {
    const moneyInputs = document.querySelectorAll('[data-money]');
    const fileInputs = document.querySelectorAll('input[type="file"]');
    const filterInputs = document.querySelectorAll('input[name="filter_campaign[]"]');
    const filterNote = document.getElementById('filterNote');
    const packageList = document.getElementById('packageList');
    const addPackageButton = document.getElementById('addPackageButton');

    let packageIndex = 1;

    function onlyNumber(value) {
        return String(value || '').replace(/[^\d]/g, '');
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value || 0);
    }

    function formatMoneyInput(input) {
        const clean = onlyNumber(input.value);

        input.value = clean ? formatNumber(Number(clean)) : '';
    }

    moneyInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            formatMoneyInput(input);
        });
    });

    fileInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const file = input.files[0];

            if (!file) return;

            const preview = document.querySelector('[data-preview="' + input.id + '"]');

            if (!preview) return;

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });

    filterInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const checked = document.querySelectorAll('input[name="filter_campaign[]"]:checked');

            if (checked.length > 4) {
                input.checked = false;

                if (filterNote) {
                    filterNote.textContent = 'Maksimal hanya 4 filter.';
                    filterNote.style.color = 'var(--danger)';
                }

                return;
            }

            if (filterNote) {
                filterNote.textContent = 'Catatan: maksimal 4 filter.';
                filterNote.style.color = '';
            }
        });
    });

    function refreshPackageTitle() {
        const items = packageList.querySelectorAll('[data-package-item]');

        items.forEach(function (item, index) {
            const title = item.querySelector('.campaign-package-title strong');
            const removeButton = item.querySelector('[data-remove-package]');

            if (title) {
                title.textContent = 'Package ' + (index + 1);
            }

            if (removeButton) {
                removeButton.hidden = items.length === 1;
            }
        });
    }

    function bindPackageMoneyInput(item) {
        const input = item.querySelector('[data-money]');

        if (!input) return;

        input.addEventListener('input', function () {
            formatMoneyInput(input);
        });
    }

    function bindRemovePackage(item) {
        const removeButton = item.querySelector('[data-remove-package]');

        if (!removeButton) return;

        removeButton.addEventListener('click', function () {
            item.remove();
            refreshPackageTitle();
        });
    }

    function createPackageItem() {
        const item = document.createElement('div');

        item.className = 'campaign-package-item';
        item.setAttribute('data-package-item', '');

        item.innerHTML = `
            <div class="campaign-package-title">
                <strong>Package ${packageIndex + 1}</strong>
                <button type="button" data-remove-package>
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>

            <label class="package-image-upload">
                <input type="file" name="packages[${packageIndex}][image]" accept="image/png,image/jpeg,image/jpg" hidden>
                <span>
                    <i class="bi bi-image"></i>
                </span>
                <small>Tambahkan Gambar</small>
            </label>

            <div class="campaign-field compact">
                <label>Judul Package <span>*</span></label>
                <div class="campaign-input-wrap">
                    <input type="text" name="packages[${packageIndex}][title]" placeholder="Masukkan judul package" required>
                    <i class="bi bi-pencil-fill"></i>
                </div>
            </div>

            <div class="campaign-field compact">
                <label>Deskripsi Package <small>(Opsional)</small></label>
                <div class="campaign-input-wrap">
                    <textarea name="packages[${packageIndex}][description]" rows="3" placeholder="Masukkan deskripsi package"></textarea>
                    <i class="bi bi-pencil-fill"></i>
                </div>
            </div>

            <div class="campaign-field compact">
                <label>Nominal Package <span>*</span></label>
                <div class="campaign-money-wrap">
                    <span>Rp</span>
                    <input type="text" name="packages[${packageIndex}][nominal]" placeholder="0" inputmode="numeric" data-money required>
                </div>
            </div>
        `;

        packageIndex++;

        bindPackageMoneyInput(item);
        bindRemovePackage(item);

        return item;
    }

    if (addPackageButton && packageList) {
        const firstItem = packageList.querySelector('[data-package-item]');

        if (firstItem) {
            bindPackageMoneyInput(firstItem);
            bindRemovePackage(firstItem);
        }

        refreshPackageTitle();

        addPackageButton.addEventListener('click', function () {
            const item = createPackageItem();

            packageList.appendChild(item);
            refreshPackageTitle();
        });
    }
});