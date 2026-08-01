document.addEventListener("DOMContentLoaded", function () {
    console.log("=== CAMPAIGN CREATE PAGE LOADED ===");

    // === DOM ELEMENTS ===
    const publishBtn = document.getElementById("publishBtn") || document.getElementById("forceSubmitBtn");
    const moneyInputs = document.querySelectorAll("[data-money]");
    const fileInputs = document.querySelectorAll('input[type="file"]');
    const filterInputs = document.querySelectorAll('input[name="filter[]"]');
    const filterNote = document.getElementById("filterNote");
    const packageList = document.getElementById("packageList");
    const addPackageButton = document.getElementById("addPackageButton");
    const previewContainer = document.getElementById("previewPackageList");
    const toggleQuantity = document.getElementById("toggleQuantity");
    const toggleDonatur = document.getElementById("toggleDonatur");
    const toggleNominal = document.getElementById("toggleNominal");
    const form = document.getElementById("campaignCreateForm");

    console.log("Form element:", form);
    console.log("Form action:", form?.action);

    // === HELPER FUNCTIONS ===
    function onlyNumber(value) {
        return String(value || "").replace(/[^\d]/g, "");
    }

    function formatNumber(value) {
        return new Intl.NumberFormat("id-ID").format(value || 0);
    }

    function formatMoneyInput(input) {
        const clean = onlyNumber(input.value);
        input.value = clean ? formatNumber(Number(clean)) : "";
        input.dataset.raw = clean;
    }

    function cleanMoney(value) {
        if (!value) return "0";
        return String(value).replace(/[^0-9]/g, "");
    }

    function formatRupiah(value) {
        const clean = cleanMoney(value);
        if (!clean || clean === "0") return "Rp0";
        return "Rp" + Number(clean).toLocaleString("id-ID");
    }

    // === SHOW VALIDATION ERRORS ===
    function showValidationErrors(errors) {
        const oldErrorContainer = document.getElementById("validationErrors");
        if (oldErrorContainer) {
            oldErrorContainer.remove();
        }

        const errorContainer = document.createElement("div");
        errorContainer.id = "validationErrors";
        errorContainer.className = "validation-error-container";
        errorContainer.style.cssText = `
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        `;

        errorContainer.innerHTML = `
            <strong>⚠️ Mohon perbaiki kesalahan berikut:</strong>
            <ul style="margin: 10px 0 0 20px; padding: 0;">
                ${errors.map((err) => `<li>${err}</li>`).join("")}
            </ul>
        `;

        const formElement = document.getElementById("campaignCreateForm");
        if (formElement) {
            formElement.parentNode.insertBefore(errorContainer, formElement);
        }

        errorContainer.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    // === MONEY INPUT FORMATTING ===
    moneyInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            formatMoneyInput(input);
        });
        if (input.value) {
            formatMoneyInput(input);
        }
    });

    // === FILE INPUT PREVIEW ===
    fileInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            const file = input.files[0];
            if (!file) return;

            const preview = document.querySelector('[data-preview="' + input.id + '"]');
            if (!preview) return;

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;

            const placeholder = preview.parentElement.querySelector(".campaign-upload-placeholder");
            if (placeholder) {
                placeholder.style.display = "none";
            }
        });
    });

    // === FILTER VALIDATION ===
    filterInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            const checked = document.querySelectorAll('input[name="filter[]"]:checked');

            if (checked.length > 4) {
                input.checked = false;
                if (filterNote) {
                    filterNote.textContent = "Maksimal hanya 4 filter.";
                    filterNote.style.color = "#dc3545";
                }
                return;
            }

            if (filterNote) {
                filterNote.textContent = "Catatan: maksimal 4 filter.";
                filterNote.style.color = "";
            }
        });
    });

    // === PACKAGE MANAGEMENT ===
    let packageIndex = 1;

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

    function bindPackageEvents(item) {
        const input = item.querySelector("[data-money]");
        if (input) {
            input.addEventListener("input", function () {
                formatMoneyInput(input);
            });
        }

        const removeButton = item.querySelector("[data-remove-package]");
        if (removeButton) {
            removeButton.addEventListener("click", function () {
                if (packageList.querySelectorAll("[data-package-item]").length > 1) {
                    item.remove();
                    refreshPackageTitle();
                    renderPreview();
                }
            });
        }

        const fileInput = item.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener("change", function () {
                const file = this.files[0];
                if (!file) return;

                const label = this.closest(".package-image-upload");
                const placeholder = label.querySelector("span");
                const small = label.querySelector("small");

                if (placeholder) {
                    placeholder.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #28a745;"></i>';
                }
                if (small) {
                    small.textContent = file.name.substring(0, 20) + (file.name.length > 20 ? "..." : "");
                }
                renderPreview();
            });
        }
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
                <label>Judul Package <small>(Opsional)</small></label>
                <div class="campaign-input-wrap">
                    <input type="text" name="packages[${packageIndex}][title]" placeholder="Masukkan judul package">
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
                <label>Nominal Package <small>(Opsional)</small></label>
                <div class="campaign-money-wrap">
                    <span>Rp</span>
                    <input
                        type="text"
                        name="packages[${packageIndex}][nominal]"
                        placeholder="0"
                        inputmode="numeric"
                        data-money>
                </div>
                <div class="package-extra-feature"></div>
            </div>
        `;

        packageIndex++;
        bindPackageEvents(item);
        renderPackageFeature();
        return item;
    }

    // === PACKAGE FEATURES ===
    function renderPackageFeature() {
        const quantityChecked = toggleQuantity?.checked || false;
        const donaturChecked = toggleDonatur?.checked || false;
        const nominalChecked = toggleNominal?.checked || false;

        document.querySelectorAll(".package-extra-feature").forEach(function (feature) {
            feature.innerHTML = "";

            if (donaturChecked) {
                feature.insertAdjacentHTML(
                    "beforeend",
                    `
                    <div class="campaign-field compact">
                        <label>Nama Pekurban</label>
                        <div class="campaign-input-wrap">
                            <input type="text" placeholder="Masukkan Nama Pekurban" name="donatur_name">
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                    </div>
                `
                );
            }

            let html = '<div class="package-price-row">';

            if (nominalChecked) {
                html += `
                    <div class="campaign-field compact custom-nominal">
                        <label>Nominal Lainnya</label>
                        <div class="campaign-money-wrap">
                            <span>Rp</span>
                            <input type="text" placeholder="0" inputmode="numeric" data-money name="custom_nominal">
                        </div>
                    </div>
                `;
            }

            if (quantityChecked) {
                html += `
                    <div class="package-quantity">
                        <label>Jumlah</label>
                        <div class="feature-counter">
                            <button type="button" class="minus" onclick="this.nextElementSibling.textContent = Math.max(1, parseInt(this.nextElementSibling.textContent) - 1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span>1</span>
                            <button type="button" class="plus" onclick="this.previousElementSibling.textContent = parseInt(this.previousElementSibling.textContent) + 1">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            html += "</div>";
            feature.insertAdjacentHTML("beforeend", html);
        });

        document.querySelectorAll("[data-money]").forEach(function (input) {
            input.addEventListener("input", function () {
                formatMoneyInput(input);
            });
        });
    }

    // === FEATURE TOGGLES ===
    if (toggleQuantity) toggleQuantity.addEventListener("change", renderPackageFeature);
    if (toggleDonatur) toggleDonatur.addEventListener("change", renderPackageFeature);
    if (toggleNominal) toggleNominal.addEventListener("change", renderPackageFeature);

    // === ADD PACKAGE ===
    if (addPackageButton && packageList) {
        const firstItem = packageList.querySelector("[data-package-item]");
        if (firstItem) {
            bindPackageEvents(firstItem);
        }
        refreshPackageTitle();

        addPackageButton.addEventListener("click", function () {
            const item = createPackageItem();
            packageList.appendChild(item);
            refreshPackageTitle();
            renderPreview();
        });
    }

    // === RENDER PREVIEW ===
    function renderPreview() {
        if (!previewContainer) return;
        previewContainer.innerHTML = "";

        const quantityChecked = toggleQuantity?.checked || false;
        const donaturChecked = toggleDonatur?.checked || false;
        const nominalChecked = toggleNominal?.checked || false;
        const packages = document.querySelectorAll("[data-package-item]");

        if (packages.length === 0) {
            previewContainer.innerHTML = `
                <div class="preview-empty">
                    <p class="text-muted">Belum ada package</p>
                    <small class="text-muted">Default packages akan muncul (Rp10.000, Rp25.000, Rp50.000, Rp100.000)</small>
                </div>
            `;
            return;
        }

        packages.forEach((item) => {
            const title = item.querySelector('input[name*="[title]"]')?.value?.trim() || "Package";
            const description = item.querySelector('textarea[name*="[description]"]')?.value?.trim() || "";
            const nominal = item.querySelector('input[name*="[nominal]"]')?.value || "0";
            const imageInput = item.querySelector('input[type="file"][name*="[image]"]');

            let imageHTML = `<div class="preview-package-placeholder"><i class="bi bi-image"></i></div>`;
            if (imageInput?.files?.length > 0) {
                try {
                    imageHTML = `<img src="${URL.createObjectURL(imageInput.files[0])}" alt="Package">`;
                } catch (e) {
                    console.warn("Gagal preview image:", e);
                }
            }

            const donaturHTML = donaturChecked
                ? `
                <div class="preview-donatur">
                    <small>Nama Pekurban</small>
                    <input type="text" placeholder="Masukkan Nama Pekurban" disabled>
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
                        ${description ? `<p>${description}</p>` : ""}
                        <span>${formatRupiah(nominal)}</span>
                        ${donaturHTML}
                    </div>
                    ${counterHTML}
                </div>
            `
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
                            <input type="text" value="0" disabled>
                        </div>
                    </div>
                </div>
            `
            );
        }
    }

    // === PREVIEW EVENTS ===
    document.addEventListener("input", function (e) {
        if (
            e.target.closest("[data-package-item]") ||
            e.target.id === "toggleQuantity" ||
            e.target.id === "toggleDonatur" ||
            e.target.id === "toggleNominal"
        ) {
            renderPreview();
        }
    });

    document.addEventListener("change", function (e) {
        if (
            e.target.closest("[data-package-item]") ||
            e.target.id === "toggleQuantity" ||
            e.target.id === "toggleDonatur" ||
            e.target.id === "toggleNominal"
        ) {
            renderPreview();
        }
    });

    // === VALIDATION FUNCTION ===
    function validateForm() {
        let isValid = true;
        let errorMessages = [];

        console.log("🔍 Validating form...");

        document.querySelectorAll(".is-invalid").forEach((el) => el.classList.remove("is-invalid"));

        // Cek thumbnail
        const thumbnail = document.getElementById("thumbnail");
        if (thumbnail && thumbnail.files.length === 0) {
            errorMessages.push("Thumbnail wajib diupload");
            thumbnail.classList.add("is-invalid");
            isValid = false;
            console.log("❌ No thumbnail uploaded");
        }

        // Cek required fields
        const requiredFields = form.querySelectorAll("[required]");
        console.log(`📋 Found ${requiredFields.length} required fields`);

        requiredFields.forEach((field) => {
            if (field.type === "hidden" || field.disabled) return;
            if (field.type === "file") return; // Skip file, sudah dihandle terpisah

            let value = field.value;
            let fieldName = field.name || field.id || "unknown";

            console.log(`  Checking: ${fieldName} = "${value}"`);

            if (field.hasAttribute("data-money")) {
                value = cleanMoney(value);
                console.log(`    Cleaned money: "${value}"`);
            }

            if (!value || value === "0" || value.trim() === "") {
                let label = field.closest(".campaign-field")?.querySelector("label")?.textContent?.trim();
                if (!label) {
                    const parentLabel = field.closest("label");
                    if (parentLabel) {
                        label = parentLabel.textContent?.trim() || fieldName;
                    }
                }
                if (!label) label = fieldName;

                errorMessages.push(`${label} wajib diisi`);
                field.classList.add("is-invalid");
                isValid = false;
                console.log(`    ❌ Empty field: ${fieldName}`);
            } else {
                field.classList.remove("is-invalid");
                console.log(`    ✅ Valid: ${fieldName}`);
            }
        });

        // Cek filter minimal 1
        const checkedFilters = document.querySelectorAll('input[name="filter[]"]:checked');
        if (checkedFilters.length === 0) {
            errorMessages.push("Minimal pilih 1 filter");
            document.querySelector(".campaign-filter-grid")?.classList.add("is-invalid");
            isValid = false;
            console.log("❌ No filter selected");
        }

        // CEK PACKAGE - TIDAK WAJIB LAGI!
        // Package bersifat opsional, tidak perlu divalidasi
        console.log("✅ Packages are optional - skipping validation");

        if (!isValid) {
            console.log("❌ Validation failed:", errorMessages);
            showValidationErrors(errorMessages);
        } else {
            console.log("✅ Validation passed!");
            const errorContainer = document.getElementById("validationErrors");
            if (errorContainer) errorContainer.remove();
        }

        return isValid;
    }

    // === INITIAL RENDER ===
    renderPackageFeature();
    renderPreview();

    // === FORM SUBMIT HANDLER ===
    if (form) {
        console.log("🔗 Attaching submit handler to form");

        form.addEventListener("submit", function (e) {
            console.log("🚀 Form submit triggered!");

            const oldErrors = document.getElementById("validationErrors");
            if (oldErrors) oldErrors.remove();

            if (!validateForm()) {
                console.log("⛔ Validation failed, preventing submit");
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            console.log("✅ Validation passed, submitting...");

            if (publishBtn) {
                publishBtn.disabled = true;
                publishBtn.classList.add("btn-loading");
                publishBtn.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Menyimpan...</span>';
            }

            return true;
        });
    } else {
        console.error("❌ Form not found!");
    }

    // === INJECT CSS ===
    const style = document.createElement("style");
    style.textContent = `
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25) !important;
        }
        
        .is-invalid ~ .campaign-input-wrap i,
        .is-invalid ~ .campaign-money-wrap i,
        .is-invalid + .campaign-input-wrap i,
        .is-invalid + .campaign-money-wrap i {
            color: #dc3545 !important;
        }
        
        .validation-error-container {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .campaign-field .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .campaign-filter-grid.is-invalid {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 10px;
            background: rgba(220, 53, 69, 0.05);
        }
        
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .btn-loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .preview-empty {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .preview-empty .text-muted {
            color: #6c757d;
            margin-bottom: 4px;
        }
        
        .preview-empty small {
            color: #adb5bd;
            font-size: 12px;
        }
    `;
    document.head.appendChild(style);

    // === KEYBOARD SHORTCUT ===
    document.addEventListener("keydown", function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
            const activeElement = document.activeElement;
            if (activeElement && form && form.contains(activeElement)) {
                e.preventDefault();
                form.dispatchEvent(new Event("submit"));
            }
        }
    });

    console.log("=== CAMPAIGN CREATE PAGE INITIALIZED SUCCESSFULLY ===");
});