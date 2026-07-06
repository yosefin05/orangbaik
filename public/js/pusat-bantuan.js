document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("#help-ai-form");
    const textarea = document.querySelector(".chatbox-input");
    const chatBox = document.querySelector("#help-ai-chat");

    if (!form || !textarea || !chatBox) return;

    textarea.addEventListener("input", resizeTextarea);

    textarea.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const message = textarea.value.trim();

        if (!message) {
            textarea.focus();
            return;
        }

        appendMessage("user", "Kamu", message);

        textarea.value = "";
        resizeTextarea();

        const loadingMessage = appendMessage(
            "bot loading",
            "OrangBaik.id Assistant",
            "Sedang menyiapkan jawaban"
        );

        setTimeout(() => {
            loadingMessage.remove();

            appendMessage(
                "bot",
                "OrangBaik.id Assistant",
                "Fitur AI sedang disiapkan oleh backend. Nantinya pertanyaan ini akan dijawab otomatis berdasarkan informasi resmi OrangBaik.id."
            );
        }, 600);
    });

    function appendMessage(type, name, text) {
        const messageElement = document.createElement("div");
        messageElement.className = `chat-message ${type}`;

        messageElement.innerHTML = `
            <strong>${escapeHTML(name)}</strong>
            <p>${escapeHTML(text)}</p>
        `;

        chatBox.appendChild(messageElement);

        messageElement.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
        });

        return messageElement;
    }

    function resizeTextarea() {
        textarea.style.height = "auto";
        textarea.style.height = `${textarea.scrollHeight}px`;
    }

    function escapeHTML(value) {
        return value
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
});