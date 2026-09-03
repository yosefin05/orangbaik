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
                <a href="https://www.facebook.com/givingisamazingcom" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>

                <a href="https://www.instagram.com/orangbaikofficial/" aria-label="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>

                <a href="#" aria-label="Youtube">
                    <i class="bi bi-youtube"></i>
                </a>

                <a href="https://www.tiktok.com/@gayahiduppeduli" aria-label="Tiktok">
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
            <h4>Jejaring Layanan</h4>
            <a href="https://dompetalquran.or.id/">Dompet Al-Qur'an Indonesia</a>
            <a href="https://wakafdq.com/">Wakaf Quran</a>
        </div>

        <div class="footer-col">
            <h4>Pusat Informasi</h4>
            <a href="{{ url('/tentang') }}">Tentang Kami</a>
            <a href="{{ url('/syarat-ketentuan') }}">Syarat & Ketentuan</a>
            <a href="{{ url('/laporan-keuangan') }}">Laporan Keuangan</a>
        </div>

        <div class="footer-col">
            <h4>Kontak Kami</h4>

            <a href="tel:+628994991155">
                <i class="bi bi-telephone"></i>
                +62 899-4991-155
            </a>

            <a href="https://wa.me/6281385002300" target="_blank">
                <i class="bi bi-whatsapp"></i>
                +62 813-8500-2300
            </a>

            <a href="mailto:info@dompetalquran.or.id">
                <i class="bi bi-envelope"></i>
                info@dompetalquran.or.id
            </a>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <p style="margin:0;">Copyright © {{ date('Y') }} OrangBaik.id (Yayasan Dompet Al-Qur'an Indonesia). All Rights Reserved.</p>
            <div class="footer-trust-badges" style="display:flex; align-items:center; gap:1.25rem; font-size:0.8125rem; color:#94a3b8;">
                <span><i class="bi bi-shield-check text-success"></i> Pembayaran Aman Terverifikasi</span>
                <span><i class="bi bi-lock-fill text-blue"></i> 256-bit SSL Enkripsi</span>
            </div>
        </div>
    </div>
</footer>