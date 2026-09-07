document.addEventListener('DOMContentLoaded', function () {
    function createSlider(config) {
        const wrapper = document.querySelector(config.wrapper);

        if (!wrapper) return;

        const slides = wrapper.querySelectorAll(config.slide);

        if (!slides.length) return;

        let currentIndex = 0;
        let interval = null;

        const dotsWrapper = config.dots ? wrapper.querySelector(config.dots) : null;

        function showSlide(index) {
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('active', slideIndex === index);
            });

            if (dotsWrapper) {
                const dots = dotsWrapper.querySelectorAll('button');

                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('active', dotIndex === index);
                });
            }

            currentIndex = index;
        }

        function nextSlide() {
            const nextIndex = (currentIndex + 1) % slides.length;
            showSlide(nextIndex);
        }

        function prevSlide() {
            const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlide(prevIndex);
        }

        function buildDots() {
            if (!dotsWrapper) return;

            dotsWrapper.innerHTML = '';

            slides.forEach((_, index) => {
                const dot = document.createElement('button');

                dot.type = 'button';
                dot.className = config.dotClass;

                if (index === 0) {
                    dot.classList.add('active');
                }

                dot.addEventListener('click', function () {
                    showSlide(index);
                    restartAutoplay();
                });

                dotsWrapper.appendChild(dot);
            });
        }

        function startAutoplay() {
            if (slides.length <= 1) return;

            interval = setInterval(nextSlide, config.delay || 2500);
        }

        function stopAutoplay() {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }

        function restartAutoplay() {
            stopAutoplay();
            startAutoplay();
        }

        wrapper.addEventListener('mouseenter', stopAutoplay);
        wrapper.addEventListener('mouseleave', startAutoplay);

        // ============================
        // SWIPE / DRAG SUPPORT
        // ============================
        if (slides.length > 1) {
            let startX = 0;
            let isDragging = false;
            const swipeThreshold = 40; // px minimal geser supaya dianggap swipe

            function onDragStart(x) {
                isDragging = true;
                startX = x;
                stopAutoplay();
            }

            function onDragEnd(x) {
                if (!isDragging) return;
                isDragging = false;

                const diff = x - startX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff < 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }

                startAutoplay();
            }

            // Touch (mobile)
            wrapper.addEventListener('touchstart', function (e) {
                onDragStart(e.touches[0].clientX);
            }, { passive: true });

            wrapper.addEventListener('touchend', function (e) {
                onDragEnd(e.changedTouches[0].clientX);
            });

            // Mouse (desktop drag)
            wrapper.addEventListener('mousedown', function (e) {
                onDragStart(e.clientX);
                e.preventDefault();
            });

            wrapper.addEventListener('mouseup', function (e) {
                onDragEnd(e.clientX);
            });

            wrapper.addEventListener('mouseleave', function () {
                isDragging = false;
            });

            // Cegah link ikut ke-klik kalau ternyata itu swipe (bukan tap biasa)
            wrapper.addEventListener('click', function (e) {
                if (Math.abs(e.clientX - startX) > swipeThreshold) {
                    e.preventDefault();
                }
            }, true);
        }

        buildDots();
        showSlide(0);
        startAutoplay();
    }

    // Slider gambar utama
    createSlider({
        wrapper: '.hero-main-slider',
        slide: '.hero-slide',
        dots: '.hero-dots',
        dotClass: 'hero-dot',
        delay: 2500,
    });

    // Slider berita kanan
    createSlider({
        wrapper: '.hero-side-slider',
        slide: '.hero-side-card',
        dots: '.hero-side-dots',
        dotClass: 'hero-side-dot',
        delay: 2500,
    });

    // Slider testimoni
    createSlider({
        wrapper: '.testimonial-wrapper',
        slide: '.testimonial-item',
        dots: null,
        dotClass: '',
        delay: 3500,
    });
});