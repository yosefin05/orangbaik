document.addEventListener("DOMContentLoaded", function () {
    const moneyInputs = document.querySelectorAll("[data-money]");
    const fileInputs = document.querySelectorAll('input[type="file"]');
    const filterInputs = document.querySelectorAll(
        'input[name="filter_campaign[]"]',
    );
    const filterNote = document.getElementById("filterNote");
    const packageList = document.getElementById("packageList");
    const addPackageButton = document.getElementById("addPackageButton");
    const quantityHTML = `
<div class="package-feature quantity-feature">

    <label>Jumlah Package</label>

    <div class="feature-counter">

        <button type="button" class="minus">−</button>

        <span>1</span>

        <button type="button" class="plus">+</button>

    </div>

</div>
`;

    const donaturHTML = `
<div class="package-feature donatur-feature">

    <label>Nama Pekurban</label>

    <div class="campaign-input-wrap">

        <input
            type="text"
            placeholder="Masukkan Nama Pekurban">

        <i class="bi bi-pencil-fill"></i>

    </div>

</div>
`;

    const nominalHTML = `
<div class="package-feature custom-feature">

    <label>Nominal Lainnya</label>

    <div class="campaign-money-wrap">

        <span>Rp</span>

        <input
            type="text"
            placeholder="0">

    </div>

</div>
`;

    function renderPackageFeature() {
        const quantityChecked =
            document.getElementById("toggleQuantity")?.checked;
        const donaturChecked =
            document.getElementById("toggleDonatur")?.checked;
        const nominalChecked =
            document.getElementById("toggleNominal")?.checked;

        document
            .querySelectorAll(".package-extra-feature")
            .forEach(function (feature) {
                feature.innerHTML = "";

                // Nama Pekurban
                if (donaturChecked) {
                    feature.insertAdjacentHTML(
                        "beforeend",
                        `
                <div class="campaign-field compact">
                    <label>Nama Pekurban</label>

                    <div class="campaign-input-wrap">
                        <input
                            type="text"
                            placeholder="Masukkan Nama Pekurban">

                        <i class="bi bi-pencil-fill"></i>
                    </div>
                </div>
            `,
                    );
                }

                // Nominal + Jumlah
                feature.insertAdjacentHTML(
                    "beforeend",
                    `
            <div class="package-price-row">

                ${
                    nominalChecked
                        ? `
                <div class="campaign-field compact custom-nominal">
                    <label>Nominal Lainnya</label>

                    <div class="campaign-money-wrap">
                        <span>Rp</span>

                        <input
                            type="text"
                            placeholder="0"
                            inputmode="numeric"
                            data-money>
                    </div>
                </div>
                `
                        : ""
                }

                ${
                    quantityChecked
                        ? `
                <div class="package-quantity">

                    <label>Jumlah</label>

                    <div class="feature-counter">

                        <button type="button" class="minus">
                            <i class="bi bi-dash"></i>
                        </button>

                        <span>1</span>

                        <button type="button" class="plus">
                            <i class="bi bi-plus"></i>
                        </button>

                    </div>

                </div>
                `
                        : ""
                }

            </div>
        `,
                );
            });

        document.querySelectorAll("[data-money]").forEach(function (input) {
            input.addEventListener("input", function () {
                formatMoneyInput(input);
            });
        });
    }
    document
        .getElementById("toggleQuantity")
        .addEventListener("change", renderPackageFeature);

    document
        .getElementById("toggleDonatur")
        .addEventListener("change", renderPackageFeature);

    document
        .getElementById("toggleNominal")
        .addEventListener("change", renderPackageFeature);

    renderPackageFeature();

    let packageIndex = 1;

    function onlyNumber(value) {
        return String(value || "").replace(/[^\d]/g, "");
    }

    function formatNumber(value) {
        return new Intl.NumberFormat("id-ID").format(value || 0);
    }

    function formatMoneyInput(input) {
        const clean = onlyNumber(input.value);

        input.value = clean ? formatNumber(Number(clean)) : "";
    }

    moneyInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            formatMoneyInput(input);
        });
    });

    fileInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            const file = input.files[0];

            if (!file) return;

            const preview = document.querySelector(
                '[data-preview="' + input.id + '"]',
            );

            if (!preview) return;

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });

    filterInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            const checked = document.querySelectorAll(
                'input[name="filter[]"]:checked',
            );

            if (checked.length > 4) {
                input.checked = false;

                if (filterNote) {
                    filterNote.textContent = "Maksimal hanya 4 filter.";
                    filterNote.style.color = "var(--danger)";
                }

                return;
            }

            if (filterNote) {
                filterNote.textContent = "Catatan: maksimal 4 filter.";
                filterNote.style.color = "";
            }
        });
    });

    function refreshPackageTitle() {
        const items = packageList.querySelectorAll("[data-package-item]");

        items.forEach(function (item, index) {
            const title = item.querySelector(".campaign-package-title strong");
            const removeButton = item.querySelector("[data-remove-package]");

            if (title) {
                title.textContent = "Package " + (index + 1);
            }

            if (removeButton) {
                removeButton.hidden = items.length === 1;
            }
        });
    }

    function bindPackageMoneyInput(item) {
        const input = item.querySelector("[data-money]");

        if (!input) return;

        input.addEventListener("input", function () {
            formatMoneyInput(input);
        });
    }

    function bindRemovePackage(item) {
        const removeButton = item.querySelector("[data-remove-package]");

        if (!removeButton) return;

        removeButton.addEventListener("click", function () {
            item.remove();
            refreshPackageTitle();
            renderPreview();
        });
    }

    function createPackageItem() {
        const item = document.createElement("div");

        item.className = "campaign-package-item";
        item.setAttribute("data-package-item", "");

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

                    <input
                        type="text"
                        name="packages[${packageIndex}][nominal]"
                        placeholder="0"
                        inputmode="numeric"
                        data-money
                        required>
                </div>
                <div class="package-extra-feature"></div>
            </div>
        `;

        packageIndex++;

        bindPackageMoneyInput(item);
        bindRemovePackage(item);

        return item;
    }

    if (addPackageButton && packageList) {
        const firstItem = packageList.querySelector("[data-package-item]");

        if (firstItem) {
            bindPackageMoneyInput(firstItem);
            bindRemovePackage(firstItem);
        }

        refreshPackageTitle();

        addPackageButton.addEventListener("click", function () {
            const item = createPackageItem();
            packageList.appendChild(item);
            bindPackageMoneyInput(item);
            bindRemovePackage(item);
            refreshPackageTitle();
            renderPackageFeature();
            renderPreview();
        });
    }

    const previewContainer = document.getElementById("previewPackageList");

    function formatRupiahPreview(value) {
        value = value.replace(/\D/g, "");

        if (!value) return "Rp0";

        return "Rp" + Number(value).toLocaleString("id-ID");
    }

    function renderPreview() {
        console.log("render preview jalan");

        if (!previewContainer) return;
        previewContainer.innerHTML = "";
        const quantityChecked =
            document.getElementById("toggleQuantity")?.checked;
        const donaturChecked =
            document.getElementById("toggleDonatur")?.checked;
        const nominalChecked =
            document.getElementById("toggleNominal")?.checked;
        const packages = document.querySelectorAll("[data-package-item]");
        packages.forEach((item) => {
            const title =
                item.querySelector('input[name*="[title]"]')?.value.trim() ||
                "";
            const description =
                item
                    .querySelector('textarea[name*="[description]"]')
                    ?.value.trim() || "";
            const nominal =
                item.querySelector('input[name*="[nominal]"]')?.value || "";
            const imageInput = item.querySelector(
                'input[type="file"][name*="[image]"]',
            );

            let imageHTML = `
            <div class="preview-package-placeholder">
                <i class="bi bi-image"></i>
            </div>
        `;

            if (imageInput.files.length > 0) {
                imageHTML = `
                <img src="${URL.createObjectURL(imageInput.files[0])}">
            `;
            }
            const isCard =
                title !== "" ||
                description !== "" ||
                imageInput.files.length > 0;
                
            const donaturHTML = donaturChecked
                ? `
                <div class="preview-donatur">
                    <small>Nama Pekurban</small>
                    <input
                        type="text"
                        placeholder="Masukkan Nama Pekurban"
                        disabled>
                </div>
            `
                : "";
            const counterHTML = quantityChecked
                ? `
                <div class="preview-counter">
                    <button>-</button>
                    <strong>1</strong>
                    <button>+</button>
                </div>
            `
                : "";
            previewContainer.insertAdjacentHTML(
                "beforeend",
                `
            <div class="preview-package">
                ${imageHTML}
                <div class="preview-package-body">
                    <h5>${title}</h5>
                    <p>${description}</p>
                    <span>${formatRupiahPreview(nominal)}</span>
                    ${donaturHTML}
                </div>
                ${counterHTML}
            </div>
        `,
            );
        });

        if (nominalChecked) {
            previewContainer.insertAdjacentHTML(
                "beforeend",
                `
            <div class="preview-list">
                <div class="preview-custom">
                    <small>Masukkan Donasi Lainnya</small>
                    <div class="money-box">
                        <span>Rp</span>
                        <input
                            type="text"
                            value="0"
                            disabled>
                    </div>
                </div>
            </div>
        `,
            );
        }
    }
    document.addEventListener("input", renderPreview);

    document.addEventListener("change", renderPreview);

    renderPreview();
});
