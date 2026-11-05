document.addEventListener("DOMContentLoaded", function () {
    /* ========================================
       HEADER SCROLL
    ======================================== */

    const header = document.getElementById("siteHeader");

    if (header) {
        function handleHeaderScroll() {
            header.classList.toggle("is-scrolled", window.scrollY > 12);
        }

        handleHeaderScroll();

        window.addEventListener("scroll", handleHeaderScroll, {
            passive: true,
        });
    }

    /* ========================================
       MOBILE BACK BUTTON
    ======================================== */

    const backButton = document.querySelector("[data-mobile-back]");

    if (backButton) {
        backButton.addEventListener("click", function (event) {
            const previousPage = document.referrer;

            if (!previousPage) {
                return;
            }

            try {
                const previousUrl = new URL(previousPage);

                if (previousUrl.origin === window.location.origin) {
                    event.preventDefault();
                    window.history.back();
                }
            } catch (error) {
                // Fallback menggunakan href menuju halaman Beranda.
            }
        });
    }

    /* ========================================
       BOTTOM NAV & FOOTER OBSERVER
    ======================================== */

    const bottomNav = document.querySelector(".mobile-bottom-nav");
    const footer = document.querySelector("footer");

    if (bottomNav && footer && "IntersectionObserver" in window) {
        const footerObserver = new IntersectionObserver(
            function (entries) {
                const entry = entries[0];

                document.body.classList.toggle(
                    "footer-in-view",
                    entry.isIntersecting,
                );
            },
            {
                threshold: 0,
            },
        );

        footerObserver.observe(footer);
    }

    /* ========================================
       RESET FILTER
       Mempertahankan fungsi sebelumnya.
    ======================================== */

    const resetBtn = document.getElementById("resetFilterBtn");

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            const form = document.getElementById("filterForm");

            if (!form) {
                return;
            }

            // Reset semua select.
            form.querySelectorAll("select").forEach(function (select) {
                select.selectedIndex = 0;
            });

            // hapus kategori (bukan dikosongkan) agar URL bersih
            form.querySelectorAll('input[name="kategori"]').forEach(
                function (input) {
                    input.remove();
                },
            );

            // Uncheck semua checkbox.
            form.querySelectorAll('input[type="checkbox"]').forEach(
                function (checkbox) {
                    checkbox.checked = false;
                },
            );

            // Reset text input dan hidden input.
            form.querySelectorAll(
                'input[type="text"], input[type="hidden"]',
            ).forEach(function (input) {
                input.value = "";
            });

            form.submit();
        });
    }
});
