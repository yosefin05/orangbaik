<link rel="stylesheet" href="{{ asset('css/logout-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/global.css') }}">

<div class="modal-overlay" id="logoutModal">
    <div class="logout">
        <h2>Keluar dari Akun</h2>

        <p>Apakah Anda yakin ingin keluar?</p>

        <div class="modal-buttons">
            <button type="button" id="confirmLogout" class="btn-keluar">
                Ya, Keluar
            </button>

            <button type="button" id="cancelLogout" class="btn-batal">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

    const logoutModal = document.getElementById("logoutModal");
    const cancelLogout = document.getElementById("cancelLogout");
    const confirmLogout = document.getElementById("confirmLogout");

    let currentForm = null;

    document.querySelectorAll(".logout-trigger").forEach(button => {

        button.addEventListener("click", function (e) {
            e.preventDefault();

            currentForm = this.closest("form");
            logoutModal.classList.add("show");
        });

    });

    cancelLogout.addEventListener("click", function () {
        logoutModal.classList.remove("show");
    });

    confirmLogout.addEventListener("click", function () {
        if (currentForm) {
            currentForm.submit();
        }
    });

    logoutModal.addEventListener("click", function (e) {
        if (e.target === logoutModal) {
            logoutModal.classList.remove("show");
        }
    });

});
</script>