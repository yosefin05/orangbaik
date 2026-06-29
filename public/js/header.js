document.addEventListener('DOMContentLoaded', function () {
    const header = document.getElementById('siteHeader');

    if (!header) {
        return;
    }

    function handleHeaderScroll() {
        if (window.scrollY > 12) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }

    handleHeaderScroll();

    window.addEventListener('scroll', handleHeaderScroll);
});