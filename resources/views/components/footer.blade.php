<footer class="site-footer">
    <div class="container footer-inner">

        <div class="footer-brand">
            <a href="{{ route('home') }}" class="footer-logo">
                <img src="{{ asset('assets/logo-header.png') }}" alt="OrangBaik.id">
            </a>

            <p>
                OrangBaik.id adalah platform donasi dan galang dana untuk membantu
                sesama secara amanah, mudah, dan terpercaya.
            </p>

            <div class="footer-socials">
                <a href="#" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>

                <a href="#" aria-label="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>

                <a href="#" aria-label="Youtube">
                    <i class="bi bi-youtube"></i>
                </a>

                <a href="#" aria-label="Tiktok">
                    <i class="bi bi-tiktok"></i>
                </a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Navigasi</h4>
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ url('/donasi') }}">Donasi</a>
            <a href="{{ url('/kalkulator') }}">Kalkulator</a>
            <a href="{{ url('/berita') }}">Berita</a>
        </div>

        <div class="footer-col">
            <h4>Jaringan Kami</h4>
            <a href="#">Dompet Al-Qur'an Indonesia</a>
            <a href="#">Wakaf Quran</a>
            <a href="#">Waqaf.id</a>
        </div>

        <div class="footer-col">
            <h4>Pusat Informasi</h4>
            <a href="{{ url('/tentang-kami') }}">Tentang Kami</a>
            <a href="{{ url('/syarat-ketentuan') }}">Syarat & Ketentuan</a>
            <a href="{{ url('/laporan-keuangan') }}">Laporan Keuangan</a>
        </div>

        <div class="footer-col">
            <h4>Kontak Kami</h4>

            <a href="tel:+628994991155">
                <i class="bi bi-telephone"></i>
                +62 899-4991-155
            </a>

            <a href="tel:+6281385002300">
                <i class="bi bi-telephone"></i>
                +62 813-8500-2300
            </a>

            <a href="mailto:info@dompetalquran.or.id">
                <i class="bi bi-envelope"></i>
                info@dompetalquran.or.id
            </a>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>Copyright © {{ date('Y') }} OrangBaik.id. All Rights Reserved.</p>
        </div>
    </div>
</footer>