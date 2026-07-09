document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("openPenggalangModal");
    const closeBtn = document.getElementById("closePenggalangModal");
    const modal = document.getElementById("penggalangModal");

    // Buka modal
    if (openBtn) {
        openBtn.addEventListener("click", function (e) {
            e.preventDefault();
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        });
    }

    // Tutup modal
    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        });
    }

    // Klik area luar modal
    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    }

    // Tombol ESC
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modal.classList.contains("active")) {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        }
    });

    window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
});
