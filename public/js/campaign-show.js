
        // ============================================================
        // SHARE CAMPAIGN
        // ============================================================
        function shareCampaign() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $campaign->judul }}',
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

        // ============================================================
        // COPY REFERRAL LINK (dari sidebar)
        // ============================================================
        function copyReferralLink() {
            const input = document.getElementById('referralLink');
            if (!input) return;
            input.select();
            document.execCommand('copy');
            showToast('Link referral berhasil disalin!');
        }

        // ============================================================
        // OWNER "KELOLA" DROPDOWN MENU
        // ============================================================
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

        // ============================================================
        // TOGGLE UPDATE DETAIL
        // ============================================================
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
        } const detailContent = document.getElementById(`detailContent-${id}`);
        const previewText = document.getElementById(`previewText-${id}`);
        const toggleText = document.getElementById(`toggleText-${id}`);
        const toggleIcon = document.getElementById(`toggleIcon-${id}`);

        if (!detailContent) return;

        const isHidden = window.getComputedStyle(detailContent).display === "none";

        if (isHidden) {
            detailContent.style.display = "block"; // Tampilkan paragraf panjang
            if (previewText) previewText.style.display = "none"; // Sembunyikan potongan preview

            toggleText.textContent = "Tutup Selengkapnya";
            if (toggleIcon) {
                toggleIcon.classList.remove("bi-chevron-down");
                toggleIcon.classList.add("bi-chevron-up");
            }
        } else {
            detailContent.style.display = "none"; // Sembunyikan paragraf panjang
            if (previewText) previewText.style.display = "block"; // Tampilkan kembali preview

            toggleText.textContent = "Baca Selengkapnya";
            if (toggleIcon) {
                toggleIcon.classList.remove("bi-chevron-compact-up");
                toggleIcon.classList.add("bi-chevron-compact-down");
            }
        }

        // ============================================================
        // LIGHTBOX
        // ============================================================
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

            img.src = lightboxImages[currentImageIndex];
            counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;

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

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', lightboxKeydown);
        }

        function closeLightbox() {
            document.getElementById('lightboxOverlay').classList.remove('active');
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
            img.src = lightboxImages[currentImageIndex];
            counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentImageIndex);
            });
        }

        function lightboxKeydown(e) {
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'Escape') closeLightbox();
        }

        let touchStartX = 0,
            touchEndX = 0;
        document.addEventListener('DOMContentLoaded', function () {
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
        });

        document.getElementById('lightboxOverlay').addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });

        // ============================================================
        // AUTO-WIRE LIGHTBOX UNTUK GAMBAR DI KONTEN
        // (deskripsi campaign & isi update masing-masing jadi galeri sendiri)
        // ============================================================
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

        // ============================================================
        // TOAST NOTIFICATION
        // ============================================================
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.textContent = message;
            toast.classList.add('show');
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // ============================================================
        // MODAL FUNDRAISER - CORE FUNCTIONS
        // ============================================================
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

        // ============================================================
        // OPEN MODAL FROM SIDEBAR QR
        // ============================================================
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
                    referralCode = urlParams.get('ref') || 'REF-' + Math.random().toString(36).substring(2, 10).toUpperCase();
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

        // ============================================================
        // QR CODE FULLSCREEN (dari dalam modal fundraiser)
        // ============================================================
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

        // ============================================================
        // COPY FUNCTIONS
        // ============================================================
        function copyModalReferralLink() {
            const input = document.getElementById('modalReferralLink');
            input.select();
            document.execCommand('copy');
            showToast('Link referral berhasil disalin!');
        }

        function copyModalReferral() {
            const code = document.getElementById('modalReferralCode').textContent;
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

        // ============================================================
        // SHARE REFERRAL LINK
        // ============================================================
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

        // ============================================================
        // KEYBOARD SHORTCUTS
        // ============================================================
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
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
            }
        });

        // Click outside to close
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

        // ============================================================
        // DOWNLOAD QR CODE (dari sidebar)
        // ============================================================
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btnDownloadQr');
            if (!btn) return;
            const svg = btn.closest('.referral-qr-section').querySelector('svg');
            if (!svg) return;
            const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
            btn.href = URL.createObjectURL(blob);
        });

        