document.addEventListener("DOMContentLoaded", function () {
    const commentForm = document.getElementById("commentForm");
    const loginModalOverlay = document.getElementById("loginModalOverlay");
    const modalCancelBtn = document.getElementById("modalCancelBtn");
    const modalLoginBtn = document.getElementById("modalLoginBtn");
    const items = document.querySelectorAll(".news-gallery-item");
    const modal = document.getElementById("galleryModal");
    const image = document.getElementById("galleryImage");
    const close = document.querySelector(".gallery-close");
    const prev = document.querySelector(".gallery-prev");
    const next = document.querySelector(".gallery-next");
    let current = 0;

    function show(index) {
        current = index;
        image.src = items[index].dataset.image;
        modal.classList.add("active");
    }

    items.forEach((item, index) => {
        item.addEventListener("click", () => {
            show(index);
        });
    });

    close.onclick = () => {
        modal.classList.remove("active");
    };

    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.classList.remove("active");
        }
    });

    prev.onclick = () => {
        current = (current - 1 + items.length) % items.length;
        show(current);
    };

    next.onclick = () => {
        current = (current + 1) % items.length;
        show(current);
    };

    document.addEventListener("keydown", (e) => {
        if (!modal.classList.contains("active")) return;
        if (e.key === "ArrowRight") next.click();
        if (e.key === "ArrowLeft") prev.click();
        if (e.key === "Escape") modal.classList.remove("active");
    });

    if (commentForm) {
        commentForm.addEventListener("submit", function (e) {
            if (this.dataset.loggedIn !== "1") {
                e.preventDefault();
                loginModalOverlay.classList.add("active");
            }
        });
    }

    if (modalCancelBtn) {
        modalCancelBtn.addEventListener("click", function () {
            loginModalOverlay.classList.remove("active");
        });
    }

    if (loginModalOverlay) {
        loginModalOverlay.addEventListener("click", function (e) {
            if (e.target === loginModalOverlay) {
                loginModalOverlay.classList.remove("active");
            }
        });
    }

    if (modalLoginBtn) {
        modalLoginBtn.addEventListener("click", function (e) {
            e.preventDefault();

            fetch("{{ route('set.intended.url') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                body: JSON.stringify({
                    url: window.location.href,
                }),
            }).then(() => {
                window.location.href = commentForm.dataset.loginUrl;
            });
        });
    }
});
