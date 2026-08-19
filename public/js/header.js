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

    const resetBtn = document.getElementById('resetFilterBtn');
    
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const form = document.getElementById('filterForm');
            
            // Reset semua select
            form.querySelectorAll('select').forEach(function(select) {
                select.selectedIndex = 0;
            });
            
            // Uncheck semua checkbox
            form.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
            
            // Reset text input
            form.querySelectorAll('input[type="text"], input[type="hidden"]').forEach(function(input) {
                input.value = '';
            });
            
            // Submit form
            form.submit();
        });
    }
});