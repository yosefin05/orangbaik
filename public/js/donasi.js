document.addEventListener('DOMContentLoaded', function () {

    const section = document.getElementById('donasiFilter');
    const form = document.getElementById('filterForm');

    if (!section || !form) {
        return;
    }

    const items = Array.from(form.querySelectorAll('[data-filter-item]'));
    const backdrop = form.querySelector('[data-filter-backdrop]');

    let current = null;


    /* ========================================
       BUKA / TUTUP PANEL
       Desktop: dropdown. Mobile: bottom sheet (diatur CSS).
    ======================================== */

    function setOpen(item, isOpen) {
        const trigger = item.querySelector('[data-filter-trigger]');
        const panel = item.querySelector('.filter-panel');

        item.classList.toggle('is-open', isOpen);
        trigger.setAttribute('aria-expanded', String(isOpen));
        panel.setAttribute('aria-hidden', String(!isOpen));
    }

    function closePanel(restoreFocus) {
        if (!current) {
            return;
        }

        const trigger = current.querySelector('[data-filter-trigger]');

        setOpen(current, false);
        current = null;

        section.classList.remove('is-open');
        document.body.classList.remove('filter-open');

        if (restoreFocus) {
            trigger.focus();
        }
    }

    function openPanel(item) {
        if (current && current !== item) {
            setOpen(current, false);
        }

        current = item;
        setOpen(item, true);

        section.classList.add('is-open');
        document.body.classList.add('filter-open');

        // fokus setelah panel terlihat (visibility baru berubah di frame berikutnya)
        const panel = item.querySelector('.filter-panel');

        requestAnimationFrame(function () {
            panel.focus({ preventScroll: true });
        });
    }

    items.forEach(function (item) {
        const trigger = item.querySelector('[data-filter-trigger]');

        trigger.addEventListener('click', function () {
            if (current === item) {
                closePanel(false);
            } else {
                openPanel(item);
            }
        });

        item.querySelectorAll('[data-filter-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closePanel(true);
            });
        });
    });

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closePanel(false);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePanel(true);
        }
    });


    /* ========================================
       ATUR ULANG (per panel)
       Radio kembali ke "Semua", checklist dikosongkan.
       Belum diterapkan sampai tombol "Terapkan" ditekan.
    ======================================== */

    form.querySelectorAll('[data-filter-clear]').forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = button.closest('.filter-panel');

            panel.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                input.checked = false;
            });

            const all = panel.querySelector('input[type="radio"][value=""]');

            if (all) {
                all.checked = true;
            }
        });
    });


    /* ========================================
       SUBMIT
       Input bernilai kosong ("Semua ...") tidak ikut terkirim
       agar URL tetap bersih: /donasi?kategori=zakat&filter_ids[]=2
    ======================================== */

    function toggleEmptyInputs(disabled) {
        form.querySelectorAll('input[value=""]').forEach(function (input) {
            input.disabled = disabled;
        });
    }

    form.addEventListener('submit', function () {
        toggleEmptyInputs(true);
    });

    // tombol "back" browser (bfcache) tidak boleh meninggalkan input dalam keadaan disabled
    window.addEventListener('pageshow', function () {
        toggleEmptyInputs(false);
    });

});