document.addEventListener("DOMContentLoaded", function () {
    // Initialize package counter
    const packageItems = document.querySelectorAll("[data-package-item]");
    let packageCounter = packageItems.length;

    // Function to update package titles
    function updatePackageTitles() {
        const items = document.querySelectorAll("[data-package-item]");
        items.forEach((item, index) => {
            const title = item.querySelector(".campaign-package-title strong");
            if (title) {
                title.textContent = `Package ${index + 1}`;
            }
            const removeBtn = item.querySelector("[data-remove-package]");
            if (removeBtn) {
                removeBtn.hidden = index === 0;
            }
        });
    }

    // Add package
    document
        .getElementById("addPackageButton")
        .addEventListener("click", function () {
            const packageList = document.getElementById("packageList");
            const firstPackage = packageList.querySelector(
                "[data-package-item]",
            );
            const newPackage = firstPackage.cloneNode(true);

            // Clean up cloned package
            newPackage.querySelectorAll("input, textarea").forEach((input) => {
                if (input.type !== "file" && input.type !== "hidden") {
                    input.value = "";
                }
                if (input.type === "hidden") {
                    input.remove();
                }
                // Update name attributes
                const name = input.getAttribute("name");
                if (name) {
                    input.setAttribute(
                        "name",
                        name.replace(/\[\d+\]/, `[${packageCounter}]`),
                    );
                }
            });

            // Remove image preview from cloned package
            const img = newPackage.querySelector(".package-image-upload img");
            if (img) {
                img.remove();
            }
            const placeholderSpan = newPackage.querySelector(
                ".package-image-upload span",
            );
            if (placeholderSpan) {
                placeholderSpan.style.display = "flex";
            }

            // Reset file input
            const fileInput = newPackage.querySelector(
                '.package-image-upload input[type="file"]',
            );
            if (fileInput) {
                fileInput.value = "";
                const name = fileInput.getAttribute("name");
                if (name) {
                    fileInput.setAttribute(
                        "name",
                        name.replace(/\[\d+\]/, `[${packageCounter}]`),
                    );
                }
            }

            // Show remove button
            const removeBtn = newPackage.querySelector("[data-remove-package]");
            if (removeBtn) {
                removeBtn.hidden = false;
            }

            packageList.appendChild(newPackage);
            packageCounter++;
            updatePackageTitles();

            // Reinitialize money formatting for new inputs
            if (window.initMoneyFormatting) {
                window.initMoneyFormatting();
            }
        });

    // Remove package
    document.addEventListener("click", function (e) {
        const removeBtn = e.target.closest("[data-remove-package]");
        if (removeBtn) {
            const packageItem = removeBtn.closest("[data-package-item]");
            const packageList = document.getElementById("packageList");
            if (
                packageList.querySelectorAll("[data-package-item]").length > 1
            ) {
                packageItem.remove();
                updatePackageTitles();
            }
        }
    });

    // Package image upload preview
    document.addEventListener("change", function (e) {
        const fileInput = e.target.closest(
            '.package-image-upload input[type="file"]',
        );
        if (fileInput) {
            const file = fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                const container = fileInput.closest(".package-image-upload");

                reader.onload = function (e) {
                    // Remove existing img or span
                    const existingImg = container.querySelector("img");
                    if (existingImg) existingImg.remove();

                    const existingSpan = container.querySelector("span");
                    if (existingSpan) existingSpan.style.display = "none";

                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "Package Image";
                    container.prepend(img);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // Quantity counter
    document.querySelectorAll(".feature-counter").forEach((counter) => {
        const minusBtn = counter.querySelector(".minus");
        const plusBtn = counter.querySelector(".plus");
        const qtySpan = counter.querySelector(".qty");
        const checkbox = counter
            .closest(".feature-row")
            .querySelector("#toggleQuantity");

        function updateQty(change) {
            let currentQty = parseInt(qtySpan.textContent) || 1;
            let newQty = currentQty + change;
            if (newQty < 1) newQty = 1;
            if (newQty > 99) newQty = 99;
            qtySpan.textContent = newQty;

            // Update hidden input for quantity
            let qtyInput = counter.querySelector(
                'input[name="default_quantity"]',
            );
            if (!qtyInput) {
                qtyInput = document.createElement("input");
                qtyInput.type = "hidden";
                qtyInput.name = "default_quantity";
                counter.appendChild(qtyInput);
            }
            qtyInput.value = newQty;
        }

        minusBtn.addEventListener("click", () => {
            if (checkbox.checked) {
                updateQty(-1);
            }
        });

        plusBtn.addEventListener("click", () => {
            if (checkbox.checked) {
                updateQty(1);
            }
        });

        // Initialize quantity value
        const initialQty = parseInt(qtySpan.textContent) || 1;
        let qtyInput = counter.querySelector('input[name="default_quantity"]');
        if (!qtyInput) {
            qtyInput = document.createElement("input");
            qtyInput.type = "hidden";
            qtyInput.name = "default_quantity";
            counter.appendChild(qtyInput);
        }
        qtyInput.value = initialQty;

        // Enable/disable counter based on checkbox
        checkbox.addEventListener("change", function () {
            minusBtn.disabled = !this.checked;
            plusBtn.disabled = !this.checked;
            if (!this.checked) {
                qtySpan.textContent = 1;
                qtyInput.value = 1;
            }
        });

        // Initial state
        minusBtn.disabled = !checkbox.checked;
        plusBtn.disabled = !checkbox.checked;
    });

    // Update package preview
    function updatePreview() {
        const previewContainer = document.getElementById("previewPackageList");
        const packages = document.querySelectorAll("[data-package-item]");
        previewContainer.innerHTML = "";

        packages.forEach((pkg, index) => {
            const titleInput = pkg.querySelector('input[name*="[title]"]');
            const nominalInput = pkg.querySelector('input[name*="[nominal]"]');
            const title = titleInput
                ? titleInput.value || `Package ${index + 1}`
                : `Package ${index + 1}`;
            const nominal = nominalInput ? nominalInput.value || "0" : "0";

            const previewItem = document.createElement("div");
            previewItem.className = "preview-package-item";
            previewItem.innerHTML = `
                        <strong>${title}</strong>
                        <span>Rp ${nominal}</span>
                    `;
            previewContainer.appendChild(previewItem);
        });
    }

    // Update preview on input change
    document.addEventListener("input", function (e) {
        if (e.target.closest("[data-package-item]")) {
            updatePreview();
        }
    });

    // Initial preview
    updatePreview();

    // Donatur name label sync
    const donaturCheckbox = document.getElementById("toggleDonatur");
    const donaturInput = document.querySelector(
        '.feature-input input[type="text"]',
    );
    if (donaturCheckbox && donaturInput) {
        donaturCheckbox.addEventListener("change", function () {
            donaturInput.disabled = !this.checked;
            if (!this.checked) {
                donaturInput.value = "";
            }
        });
        donaturInput.disabled = !donaturCheckbox.checked;
    }

    // Custom nominal sync
    const nominalCheckbox = document.getElementById("toggleNominal");
    const nominalInput = document.querySelector(
        ".feature-money .money-box input",
    );
    if (nominalCheckbox && nominalInput) {
        nominalCheckbox.addEventListener("change", function () {
            nominalInput.disabled = !this.checked;
            if (!this.checked) {
                nominalInput.value = "";
            }
        });
        nominalInput.disabled = !nominalCheckbox.checked;
    }
});
