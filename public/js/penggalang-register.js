/* ============================================================
   VERIFIKASI PENGGALANG DANA — PROGRES & BANTUAN ISI FORM
   Dipakai halaman Individu & Organisasi.

   Hanya menambah bantuan tampilan; TIDAK mengubah data form
   dan tidak menyentuh pratinjau foto / banner (itu tetap dipegang
   penggalang-individu.js / penggalang-organisasi.js).

   Atribut yang dibaca:
   - [data-reg-section]        : tiap section (id = tujuan link langkah)
   - [data-reg-required]       : field wajib (input / select / textarea)
   - [data-reg-required-group] : wajib diisi SALAH SATU di dalamnya
                                 (mis. unggah file ATAU tempel tautan)
   - [data-reg-step="id"]      : link langkah di kolom progres
   - [data-reg-bar|percent|count] : bar, persen, dan teks progres
============================================================ */

(function () {

    const form = document.getElementById('regForm');

    if (!form) {
        return;
    }

    const sections = Array.from(form.querySelectorAll('[data-reg-section]'));
    const steps = Array.from(document.querySelectorAll('[data-reg-step]'));

    const barEl = document.querySelector('[data-reg-bar]');
    const percentEl = document.querySelector('[data-reg-percent]');
    const countEl = document.querySelector('[data-reg-count]');


    /* ========================================
       PROGRES
    ======================================== */

    function isFilled(el) {
        if (el.type === 'file') {
            return !!(el.files && el.files.length > 0);
        }

        return el.value.trim() !== '';
    }

    function unitFilled(unit) {
        if (unit.hasAttribute('data-reg-required-group')) {
            return Array.from(unit.querySelectorAll('input, select, textarea'))
                .some(isFilled);
        }

        return isFilled(unit);
    }

    function updateProgress() {
        let total = 0;
        let done = 0;

        sections.forEach(function (section) {
            const units = Array.from(
                section.querySelectorAll('[data-reg-required], [data-reg-required-group]')
            );

            const filled = units.filter(unitFilled).length;

            total += units.length;
            done += filled;

            const step = steps.find(function (item) {
                return item.dataset.regStep === section.id;
            });

            if (step) {
                step.classList.toggle(
                    'is-done',
                    units.length > 0 && filled === units.length
                );
            }
        });

        const percent = total > 0 ? Math.round((done / total) * 100) : 0;

        if (barEl) {
            barEl.style.width = percent + '%';
        }

        if (percentEl) {
            percentEl.textContent = percent + '%';
        }

        if (countEl) {
            countEl.textContent = done === total
                ? 'Semua data wajib sudah terisi. Silakan kirim.'
                : done + ' dari ' + total + ' data wajib sudah terisi';
        }
    }

    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);

    // tombol "back" browser (bfcache) mengembalikan isi form
    window.addEventListener('pageshow', updateProgress);

    updateProgress();


    /* ========================================
       LANGKAH SAAT INI (mengikuti scroll)
    ======================================== */

    if ('IntersectionObserver' in window && steps.length) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                steps.forEach(function (step) {
                    step.classList.toggle(
                        'is-current',
                        step.dataset.regStep === entry.target.id
                    );
                });
            });
        }, {
            rootMargin: '-25% 0px -65% 0px'
        });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }


    /* ========================================
       ERROR DARI SERVER
       Buka bagian lipat yang berisi error, lalu arahkan
       pengguna ke kolom pertama yang bermasalah.
    ======================================== */

    const firstError = form.querySelector('.reg-error');

    if (firstError) {
        form.querySelectorAll('details').forEach(function (details) {
            if (details.querySelector('.reg-error')) {
                details.open = true;
            }
        });

        window.setTimeout(function () {
            const field = firstError.closest('.reg-field') || firstError;
            const input = field.querySelector('input, select, textarea');

            field.scrollIntoView({ block: 'center', behavior: 'smooth' });

            if (input) {
                input.focus({ preventScroll: true });
            }
        }, 150);
    }

})();