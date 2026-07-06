document.addEventListener("DOMContentLoaded", () => {
    const helpForm = document.querySelector(".help-search-box");
    const textarea = document.querySelector(".help-search-input");

    if (textarea) {
        textarea.addEventListener("input", () => {
            textarea.style.height = "auto";
            textarea.style.height = `${textarea.scrollHeight}px`;
        });
    }

    if (helpForm) {
        helpForm.addEventListener("submit", (event) => {
            event.preventDefault();

            if (!textarea || textarea.value.trim() === "") {
                textarea?.focus();
                return;
            }

            alert("Fitur bantuan/chatbot belum terhubung ke backend.");
        });
    }
});