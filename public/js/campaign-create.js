document.addEventListener("DOMContentLoaded", function () {
    console.log("=== CAMPAIGN CREATE PAGE LOADED ===");

    // === DOM ELEMENTS ===
    const form = document.getElementById("campaignCreateForm");
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

    // Debug form
    console.log("Form element:", form);
    console.log("Form action:", form?.action);
    console.log("Form method:", form?.method);

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

    // === MONEY INPUT FORMATTING ===
    moneyInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            formatMoneyInput(input);
        });
        // Set initial value
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
            
            // Hide placeholder
            const placeholder = preview.parentElement.querySelector('.campaign-upload-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
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
                item.remove();
                refreshPackageTitle();
                renderPreview();
            });
        }

        // File input preview for package
        const fileInput = item.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener("change", function () {
                const file = this.files[0];
                if (!file) return;
                
                const label = this.closest('.package-image-upload');
                const placeholder = label.querySelector('span');
                const small = label.querySelector('small');
                
                if (placeholder) {
                    placeholder.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #28a745;"></i>';
                }
                if (small) {
                    small.textContent = file.name.substring(0, 20) + (file.name.length > 20 ? '...' : '');
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
                <label>Judul Package</label>
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

            html += '</div>';
            feature.insertAdjacentHTML("beforeend", html);
        });

        // Re-bind money inputs
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
            previewContainer.innerHTML = '<p class="text-muted">Belum ada package</p>';
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

        // Cek semua field required
        const requiredFields = form.querySelectorAll("[required]");
        requiredFields.forEach((field) => {
            let value = field.value;

            if (field.hasAttribute("data-money")) {
                value = cleanMoney(value);
            }

            if (!value || value === "0" || value.trim() === "") {
                const label = field.closest(".campaign-field")?.querySelector("label")?.textContent || field.name || "Field";
                errorMessages.push(`${label} wajib diisi`);
                field.style.borderColor = "#dc3545";
                isValid = false;
            } else {
                field.style.borderColor = "";
            }
        });

        // Cek file thumbnail
        const thumbnail = document.getElementById("thumbnail");
        if (thumbnail && thumbnail.files.length === 0) {
            errorMessages.push("Thumbnail wajib diupload");
            isValid = false;
        }

        // Cek filter minimal 1
        const checkedFilters = document.querySelectorAll('input[name="filter[]"]:checked');
        if (checkedFilters.length === 0) {
            errorMessages.push("Minimal pilih 1 filter");
            isValid = false;
        }

        // Cek packages
        const packages = document.querySelectorAll("[data-package-item]");
        if (packages.length === 0) {
            errorMessages.push("Minimal buat 1 package");
            isValid = false;
        }

        packages.forEach((pkg, index) => {
            const nominal = pkg.querySelector('input[name*="[nominal]"]');
            if (nominal) {
                const cleanVal = cleanMoney(nominal.value);
                if (!cleanVal || cleanVal === "0") {
                    errorMessages.push(`Package ${index + 1}: Nominal wajib diisi`);
                    nominal.style.borderColor = "#dc3545";
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            alert("⚠️ Error Validasi:\n\n- " + errorMessages.join("\n- "));
        }

        return isValid;
    }

    // === FORM SUBMIT HANDLER ===
    if (publishBtn) {
        publishBtn.addEventListener("click", function (e) {
            e.preventDefault();
            console.log("=== PUBLISH BUTTON CLICKED ===");

            if (!validateForm()) {
                console.log("Validasi gagal");
                return;
            }

            // Siapkan FormData
            const formData = new FormData(form);

            // Bersihkan semua input money
            document.querySelectorAll("[data-money]").forEach((input) => {
                const clean = cleanMoney(input.value);
                formData.set(input.name, clean);
                input.value = clean;
            });

            // Log data
            console.log("=== FORM DATA ===");
            for (let pair of formData.entries()) {
                if (pair[1] instanceof File) {
                    console.log(pair[0] + ":", pair[1].name, `(${pair[1].size} bytes)`);
                } else {
                    console.log(pair[0] + ":", pair[1]);
                }
            }

            // Disable button
            publishBtn.disabled = true;
            publishBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Menyimpan...</span>';
            publishBtn.classList.add("btn-loading");

            // Submit dengan fetch
            fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    Accept: "application/json",
                },
            })
            .then((response) => {
                console.log("Response status:", response.status);

                if (response.redirected) {
                    console.log("Redirect to:", response.url);
                    window.location.href = response.url;
                    return;
                }

                return response.json().catch(() => {
                    return response.text().then((text) => {
                        try {
                            return JSON.parse(text);
                        } catch {
                            return { html: text };
                        }
                    });
                });
            })
            .then((data) => {
                console.log("Response data:", data);

                if (data && data.errors) {
                    let errorMsg = "❌ Error Validasi:\n\n";
                    Object.keys(data.errors).forEach((key) => {
                        errorMsg += `- ${data.errors[key].join("\n  ")}\n`;
                    });
                    alert(errorMsg);
                } else if (data && data.message) {
                    alert("✅ " + data.message);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else if (data && data.html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data.html, "text/html");
                    const errors = doc.querySelectorAll(".text-danger, .alert-danger");
                    if (errors.length > 0) {
                        let msg = "❌ Error:\n\n";
                        errors.forEach((err) => {
                            msg += `- ${err.textContent.trim()}\n`;
                        });
                        alert(msg);
                    } else {
                        alert("Terjadi kesalahan. Silakan coba lagi.");
                    }
                } else {
                    alert("Campaign berhasil dibuat!");
                    window.location.href = "/donasi";
                }

                // Reset button
                publishBtn.disabled = false;
                publishBtn.innerHTML = '<i class="bi bi-send-fill"></i> <span>Publikasikan Campaign</span>';
                publishBtn.classList.remove("btn-loading");
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("❌ Terjadi error: " + error.message);
                publishBtn.disabled = false;
                publishBtn.innerHTML = '<i class="bi bi-send-fill"></i> <span>Publikasikan Campaign</span>';
                publishBtn.classList.remove("btn-loading");
            });
        });
    }

    // === INITIAL RENDER ===
    renderPackageFeature();
    renderPreview();

    console.log("=== CAMPAIGN CREATE PAGE INITIALIZED SUCCESSFULLY ===");
});