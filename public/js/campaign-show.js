/* ============================================================
   CAMPAIGN SHOW — JS (ORANGBAIK.ID)
   File mandiri: tanpa sintaks Blade, jadi bisa dimuat dengan
   <script src="{{ asset('js/campaign-show.js') }}"></script>

   Daftar isi:
   1.  Share campaign
   2.  Topbar mobile (solid setelah scroll)
   3.  Copy referral link (sidebar)
   4.  Menu "Kelola" pemilik
   5.  Toggle detail kabar terbaru
   6.  Lightbox
   7.  Auto-wire lightbox untuk gambar di konten
   8.  Toast
   9.  Modal fundraiser
   10. QR fullscreen
   11. Copy & share referral
   12. Keyboard & klik di luar modal
   13. Download QR (sidebar)
   14. Rich text: preview "Lihat selengkapnya" (mobile)
============================================================ */


/* ============================================================
   1. SHARE CAMPAIGN
============================================================ */
function shareCampaign() {
    // judul diambil dari halaman (bukan dari Blade) agar aman untuk tanda kutip
    const heading = document.querySelector('.description-section h1');
    const title = ((heading && heading.textContent) || document.title).trim();

    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Dukung campaign ini di OrangBaik.id',
            url: window.location.href
        }).catch(() => { });
    } else {
        const url = window.location.href;

        navigator.clipboard.writeText(url).then(() => {
            alert('Link campaign berhasil disalin!');
        }).catch(() => {
            prompt('Salin link ini:', url);
        });
    }
}


/* ============================================================
   2. HEADER MOBILE
   Topbar jadi solid dengan judul setelah scroll melewati hero
============================================================ */
(function () {
    const bar = document.getElementById('campaignTopbar');
    const hero = document.querySelector('.hero-wrapper');

    if (!bar) return;

    let ticking = false;

    function update() {
        ticking = false;

        // di desktop topbar disembunyikan lewat CSS: tidak perlu dihitung
        if (window.getComputedStyle(bar).display === 'none') return;

        const limit = (hero ? hero.offsetHeight : 160) - bar.offsetHeight;
        bar.classList.toggle('is-solid', window.scrollY > Math.max(limit, 0));
    }

    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(update);
    }, { passive: true });

    window.addEventListener('resize', update);
    update();
})();


/* ============================================================
   3. COPY REFERRAL LINK (dari sidebar)
============================================================ */
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    if (!input) return;

    input.select();
    document.execCommand('copy');
    showToast('Link referral berhasil disalin!');
}


/* ============================================================
   4. OWNER "KELOLA" DROPDOWN MENU
============================================================ */
function toggleOwnerMenu(e) {
    e.stopPropagation();

    const dropdown = document.getElementById('ownerMenuDropdown');
    if (!dropdown) return;

    dropdown.classList.toggle('open');
}

document.addEventListener('click', function (e) {
    const dropdown = document.getElementById('ownerMenuDropdown');
    if (!dropdown) return;

    const menu = dropdown.closest('.owner-menu');

    if (menu && !menu.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});


/* ============================================================
   5. TOGGLE UPDATE DETAIL
   (versi ganda yang tertinggal setelah fungsi ini sudah dihapus:
   kode tersebut berada di luar fungsi dan memakai variabel `id`
   yang tidak ada, sehingga script berhenti dengan ReferenceError
   dan semua kode di bawahnya tidak pernah berjalan.)
============================================================ */
function toggleUpdateDetail(id) {
    const detailContent = document.getElementById(`detailContent-${id}`);
    const previewText = document.getElementById(`previewText-${id}`);
    const toggleText = document.getElementById(`toggleText-${id}`);
    const toggleIcon = document.getElementById(`toggleIcon-${id}`);

    if (!detailContent || !toggleText) return;

    const isOpening = detailContent.hidden;

    detailContent.hidden = !isOpening;

    if (previewText) {
        previewText.hidden = isOpening;
    }

    toggleText.textContent = isOpening
        ? 'Tutup Selengkapnya'
        : 'Baca Selengkapnya';

    if (toggleIcon) {
        toggleIcon.classList.toggle('bi-chevron-compact-down', !isOpening);
        toggleIcon.classList.toggle('bi-chevron-compact-up', isOpening);
    }
}


/* ============================================================
   6. LIGHTBOX
============================================================ */
let lightboxImages = [];
let currentImageIndex = 0;

function openLightbox(imageSrc, allImages) {
    lightboxImages = allImages || [imageSrc];
    currentImageIndex = lightboxImages.indexOf(imageSrc);

    if (currentImageIndex === -1) {
        currentImageIndex = 0;
        lightboxImages = [imageSrc];
    }

    const overlay = document.getElementById('lightboxOverlay');
    const img = document.getElementById('lightboxImage');
    const counter = document.getElementById('lightboxCounter');
    const dots = document.getElementById('lightboxDots');

    if (!overlay || !img) return;

    img.src = lightboxImages[currentImageIndex];

    if (counter) {
        counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;
    }

    if (dots) {
        dots.innerHTML = '';

        lightboxImages.forEach((_, i) => {
            const dot = document.createElement('span');

            dot.className = 'lightbox-dot' + (i === currentImageIndex ? ' active' : '');
            dot.onclick = function (e) {
                e.stopPropagation();
                goToImage(i);
            };

            dots.appendChild(dot);
        });
    }

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', lightboxKeydown);
}

function closeLightbox() {
    const overlay = document.getElementById('lightboxOverlay');

    if (overlay) {
        overlay.classList.remove('active');
    }

    document.body.style.overflow = '';
    document.removeEventListener('keydown', lightboxKeydown);
}

function changeImage(direction) {
    const total = lightboxImages.length;

    if (total <= 1) return;

    currentImageIndex = (currentImageIndex + direction + total) % total;
    updateLightboxImage();
}

function goToImage(index) {
    if (index < 0 || index >= lightboxImages.length) return;

    currentImageIndex = index;
    updateLightboxImage();
}

function updateLightboxImage() {
    const img = document.getElementById('lightboxImage');
    const counter = document.getElementById('lightboxCounter');
    const dots = document.querySelectorAll('.lightbox-dot');

    if (!img) return;

    img.src = lightboxImages[currentImageIndex];

    if (counter) {
        counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;
    }

    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === currentImageIndex);
    });
}

function lightboxKeydown(e) {
    if (e.key === 'ArrowLeft') changeImage(-1);
    if (e.key === 'ArrowRight') changeImage(1);
    if (e.key === 'Escape') closeLightbox();
}

let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('DOMContentLoaded', function () {
    // geser kiri / kanan di layar sentuh
    const wrapper = document.querySelector('.lightbox-image-wrapper');

    if (wrapper) {
        wrapper.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        wrapper.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;

            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > 50) {
                changeImage(diff > 0 ? 1 : -1);
            }
        }, { passive: true });
    }

    // klik area gelap menutup lightbox
    const overlay = document.getElementById('lightboxOverlay');

    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });
    }
});


/* ============================================================
   7. AUTO-WIRE LIGHTBOX UNTUK GAMBAR DI KONTEN
   (deskripsi campaign & isi update masing-masing jadi galeri sendiri)
============================================================ */
document.addEventListener('DOMContentLoaded', function () {

    function wireLightboxImages(containerSelector) {
        document.querySelectorAll(containerSelector).forEach(function (container) {
            const imgs = Array.from(container.querySelectorAll('img'));

            if (!imgs.length) return;

            const srcs = imgs.map(img => img.src);

            imgs.forEach(function (img) {
                img.addEventListener('click', function () {
                    openLightbox(img.src, srcs);
                });
            });
        });
    }

    wireLightboxImages('.rich-text-output');
    wireLightboxImages('.update-full-body');
});


/* ============================================================
   8. TOAST NOTIFICATION
============================================================ */
function showToast(message) {
    const toast = document.getElementById('toastNotification');
    const toastMsg = document.getElementById('toastMessage');

    if (!toast || !toastMsg) return;

    toastMsg.textContent = message;
    toast.classList.add('show');

    clearTimeout(toast._timeout);

    toast._timeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}


/* ============================================================
   9. MODAL FUNDRAISER — CORE
============================================================ */
let modalReferralCode = '';
let modalReferralUrl = '';

function openFundraiserModal(referralCode, referralUrl, qrSvg) {
    modalReferralCode = referralCode || '';
    modalReferralUrl = referralUrl || '';

    const referralLinkInput = document.getElementById('modalReferralLink');

    if (referralLinkInput) {
        referralLinkInput.value = referralUrl || '';
    }

    const referralCodeElement = document.getElementById('modalReferralCode');

    if (referralCodeElement) {
        referralCodeElement.textContent = referralCode || '-';
    }

    const qrContainer = document.getElementById('modalQrContainer');

    if (qrContainer && qrSvg) {
        qrContainer.innerHTML = qrSvg;
    }

    const modal = document.getElementById('fundraiserModal');

    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeFundraiserModal() {
    const modal = document.getElementById('fundraiserModal');

    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Buka modal dari QR di sidebar
function openFundraiserModalFromSidebar() {
    const referralLinkInput = document.getElementById('referralLink');

    if (!referralLinkInput) {
        showToast('Data referral tidak ditemukan');
        return;
    }

    const referralUrl = referralLinkInput.value;

    let referralCode = '';
    const codeElement = document.getElementById('sidebarReferralCode');

    if (codeElement) {
        referralCode = codeElement.textContent;
    } else {
        try {
            const urlParams = new URLSearchParams(new URL(referralUrl).search);

            referralCode = urlParams.get('ref')
                || 'REF-' + Math.random().toString(36).substring(2, 10).toUpperCase();
        } catch (e) {
            referralCode = 'REF-' + Math.random().toString(36).substring(2, 10).toUpperCase();
        }
    }

    const qrWrapper = document.querySelector('.qr-canvas-wrapper');

    if (!qrWrapper) {
        showToast('QR Code tidak ditemukan');
        return;
    }

    const qrSvg = qrWrapper.innerHTML;

    if (!qrSvg || qrSvg.trim() === '') {
        showToast('QR Code tidak ditemukan');
        return;
    }

    openFundraiserModal(referralCode, referralUrl, qrSvg);
}


/* ============================================================
   10. QR CODE FULLSCREEN (dari dalam modal fundraiser)
============================================================ */
function openQRFullscreen() {
    const qrContainer = document.getElementById('modalQrContainer');
    const overlay = document.getElementById('qrFullscreenOverlay');
    const fullscreenContainer = document.getElementById('qrFullscreenContainer');

    if (!qrContainer || !overlay || !fullscreenContainer) return;

    const qrSvg = qrContainer.innerHTML;

    if (!qrSvg || qrSvg.trim() === '') {
        showToast('QR Code belum tersedia');
        return;
    }

    fullscreenContainer.innerHTML = qrSvg;
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    const btnDownload = document.getElementById('btnDownloadQrFullscreen');

    if (btnDownload) {
        btnDownload.onclick = function () {
            const svg = fullscreenContainer.querySelector('svg');

            if (!svg) return;

            const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
            const link = document.createElement('a');

            link.href = URL.createObjectURL(blob);
            link.download = 'qr-referral-' + (modalReferralCode || 'orangbaik') + '.svg';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    }
}

function closeQRFullscreen() {
    const overlay = document.getElementById('qrFullscreenOverlay');

    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}


/* ============================================================
   11. COPY & SHARE REFERRAL
============================================================ */
function copyModalReferralLink() {
    const input = document.getElementById('modalReferralLink');

    if (!input) return;

    input.select();
    document.execCommand('copy');
    showToast('Link referral berhasil disalin!');
}

function copyModalReferral() {
    const codeElement = document.getElementById('modalReferralCode');

    if (!codeElement) return;

    const code = codeElement.textContent;

    navigator.clipboard.writeText(code).then(() => {
        showToast('Kode referral berhasil disalin!');
    }).catch(() => {
        const input = document.createElement('input');

        input.value = code;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);

        showToast('Kode referral berhasil disalin!');
    });
}

function shareReferralLink() {
    if (navigator.share) {
        navigator.share({
            title: 'Donasi Campaign di OrangBaik.id',
            text: 'Yuk donasi! Gunakan referral code saya: ' + modalReferralCode,
            url: modalReferralUrl
        }).catch(() => { });
    } else {
        navigator.clipboard.writeText(modalReferralUrl).then(() => {
            showToast('Link referral berhasil disalin!');
        }).catch(() => {
            prompt('Salin link ini:', modalReferralUrl);
        });
    }
}


/* ============================================================
   12. KEYBOARD & KLIK DI LUAR MODAL
============================================================ */
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;

    const qrOverlay = document.getElementById('qrFullscreenOverlay');
    const fundraiserModal = document.getElementById('fundraiserModal');
    const lightboxOverlay = document.getElementById('lightboxOverlay');

    if (qrOverlay && qrOverlay.classList.contains('active')) {
        closeQRFullscreen();
    } else if (fundraiserModal && fundraiserModal.classList.contains('active')) {
        closeFundraiserModal();
    } else if (lightboxOverlay && lightboxOverlay.classList.contains('active')) {
        closeLightbox();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const fundraiserModal = document.getElementById('fundraiserModal');

    if (fundraiserModal) {
        fundraiserModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeFundraiserModal();
            }
        });
    }

    const qrOverlay = document.getElementById('qrFullscreenOverlay');

    if (qrOverlay) {
        qrOverlay.addEventListener('click', function (e) {
            if (e.target === this) {
                closeQRFullscreen();
            }
        });
    }
});


/* ============================================================
   13. DOWNLOAD QR CODE (dari sidebar)
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnDownloadQr');

    if (!btn) return;

    const section = btn.closest('.referral-qr-section');
    const svg = section ? section.querySelector('svg') : null;

    if (!svg) return;

    const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
    btn.href = URL.createObjectURL(blob);
});


/* ============================================================
   14. RICH TEXT — PREVIEW "LIHAT SELENGKAPNYA" (MOBILE)
   - Hanya aktif di layar ≤ 768px.
   - Teks yang lebih panjang dari batas (diatur CSS, ± 7 baris)
     dipotong dengan efek pudar + tombol "Lihat selengkapnya".
   - Teks pendek tidak diubah dan tidak diberi tombol.
   - Tombol dibuat otomatis: markup blade TIDAK perlu diubah.
   Target: ".description-section .rich-text-output"
           atau elemen apa pun dengan atribut data-rich-collapse.
============================================================ */
(function () {

    const MOBILE = window.matchMedia('(max-width: 768px)');
    const SELECTOR = '.description-section .rich-text-output, [data-rich-collapse]';

    const LABEL_MORE = 'Lihat selengkapnya';
    const LABEL_LESS = 'Lihat lebih sedikit';

    const items = [];


    function setup(el) {
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'rich-toggle';
        button.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        button.innerHTML =
            '<span class="rich-toggle-text">' + LABEL_MORE + '</span>' +
            '<i class="bi bi-chevron-down" aria-hidden="true"></i>';

        el.insertAdjacentElement('afterend', button);

        const item = { el: el, button: button, expanded: false };

        button.addEventListener('click', function () {
            toggle(item);
        });

        // tinggi konten berubah setelah gambar selesai dimuat
        el.querySelectorAll('img').forEach(function (img) {
            if (!img.complete) {
                img.addEventListener('load', function () {
                    evaluate(item);
                }, { once: true });
            }
        });

        items.push(item);
    }

    // Tentukan apakah teks perlu dipotong, lalu atur tampilan & tombol.
    function evaluate(item) {
        const el = item.el;
        const button = item.button;

        // desktop: tampil penuh, tanpa tombol
        if (!MOBILE.matches) {
            el.classList.remove('is-collapsed');
            button.hidden = true;
            return;
        }

        // sedang dibuka penuh: tombol tetap tersedia untuk menutup
        if (item.expanded) {
            button.hidden = false;
            return;
        }

        // pasang batas dulu, baru ukur apakah isinya melebihi batas
        el.classList.add('is-collapsed');

        const overflowing = el.scrollHeight - el.clientHeight > 8;

        if (!overflowing) {
            el.classList.remove('is-collapsed');
        }

        button.hidden = !overflowing;
    }

    function toggle(item) {
        item.expanded = !item.expanded;

        item.el.classList.toggle('is-collapsed', !item.expanded);
        item.button.setAttribute('aria-expanded', String(item.expanded));
        item.button.querySelector('.rich-toggle-text').textContent =
            item.expanded ? LABEL_LESS : LABEL_MORE;

        // setelah menutup, kembali ke awal bagian agar tidak "tersesat" di tengah halaman
        if (!item.expanded) {
            const section = item.el.closest('.description-section') || item.el;
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function evaluateAll() {
        items.forEach(evaluate);
    }

    function init() {
        document.querySelectorAll(SELECTOR).forEach(setup);
        evaluateAll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ukur ulang setelah semua gambar & font termuat
    window.addEventListener('load', evaluateAll);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(evaluateAll);
    }

    // rotasi layar / resize melewati batas 768px
    if (MOBILE.addEventListener) {
        MOBILE.addEventListener('change', evaluateAll);
    } else if (MOBILE.addListener) {
        MOBILE.addListener(evaluateAll);
    }

})();