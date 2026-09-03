(function () {
    function sync(editor) {
        editor
            .closest("[data-rich-text-editor]")
            .querySelector("[data-rich-text-input]").value = editor.innerHTML;
    }

    function insertImage(editor, file) {
        if (!file || !file.type.startsWith("image/")) return;
        const reader = new FileReader();
        reader.onload = function () {
            editor.focus();
            document.execCommand("insertImage", false, reader.result);
            sync(editor);
        };
        reader.readAsDataURL(file);
    }

    document
        .querySelectorAll("[data-rich-text-editor]")
        .forEach(function (wrapper) {
            const editor = wrapper.querySelector(".rich-text-content");
            const input = wrapper.querySelector("[data-image-input]");

            wrapper
                .querySelectorAll("[data-command]")
                .forEach(function (button) {
                    button.addEventListener("click", function () {
                        if (button.dataset.command === "createLink") {
                            const url = window.prompt("Masukkan URL tautan:");
                            if (url)
                                document.execCommand("createLink", false, url);
                        } else {
                            document.execCommand(
                                button.dataset.command,
                                false,
                                null,
                            );
                        }
                        editor.focus();
                        sync(editor);
                    });
                });

            wrapper
                .querySelector("[data-insert-image]")
                .addEventListener("click", function () {
                    input.click();
                });
            input.addEventListener("change", function () {
                Array.from(input.files).forEach(function (file) {
                    insertImage(editor, file);
                });
                input.value = "";
            });
            editor.addEventListener("input", function () {
                sync(editor);
            });
            editor.addEventListener("keydown", function (event) {
                if (event.ctrlKey && event.key.toLowerCase() === "j") {
                    event.preventDefault();
                    document.execCommand("justifyFull", false, null);
                    sync(editor);
                }
            });
            editor.addEventListener("paste", function (event) {
                Array.from(event.clipboardData.files).forEach(function (file) {
                    if (file.type.startsWith("image/")) {
                        event.preventDefault();
                        insertImage(editor, file);
                    }
                });
            });
            editor.closest("form").addEventListener("submit", function () {
                sync(editor);
            });
        });
})();
