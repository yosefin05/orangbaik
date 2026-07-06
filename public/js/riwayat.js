document.addEventListener("DOMContentLoaded", () => {
    const filterButtons = document.querySelectorAll("[data-filter]");
    const historyItems = document.querySelectorAll("[data-status]");
    const emptyFilter = document.querySelector("[data-empty-filter]");

    if (!filterButtons.length || !historyItems.length) return;

    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const selectedFilter = button.dataset.filter;
            let visibleCount = 0;

            filterButtons.forEach((item) => {
                item.classList.remove("active");
            });

            button.classList.add("active");

            historyItems.forEach((item) => {
                const itemStatus = item.dataset.status;

                const shouldShow =
                    selectedFilter === "semua" || itemStatus === selectedFilter;

                item.classList.toggle("hidden", !shouldShow);

                if (shouldShow) {
                    visibleCount += 1;
                }
            });

            if (emptyFilter) {
                emptyFilter.classList.toggle("show", visibleCount === 0);
            }
        });
    });
});