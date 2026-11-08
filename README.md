# OrangBaik.id — Platform Crowdfunding & Kebaikan

Platform crowdfunding dan donasi terpercaya yang dikelola oleh **Dompet Al-Qur'an Sidoarjo**, memfasilitasi penggalangan dana kemanusiaan, sosial, keagamaan, dan pendidikan dengan sistem pembayaran multi-gateway modern, verifikasi legalitas ketat, serta perlindungan privasi donatur dan penggalang dana.

---

## 1. Fitur Utama

- **Ecosystem Crowdfunding & Zakat**:
  - Penggalangan dana reguler, darurat (*emergency*), dan berkelanjutan (*sustainable*).
  - Manajemen kabar terbaru campaign (*campaign updates*) dan berita resmi platform.
  - Dukungan relawan *fundraiser* dengan sistem kode referral dan tautan donasi khusus.
  - Kalkulator zakat otomatis dengan integrasi langsung ke form donasi.
- **Sistem Pembayaran Multi-Gateway & Opsi Transfer**:
  - **Midtrans**: QRIS, GoPay, ShopeePay, Kartu Kredit/Debit, dan Snap modal 1-klik.
  - **Flip**: Virtual Account (BCA, BNI, BRI, BSI, Mandiri, Permata, CIMB Niaga) & Bank Transfer otomatis.
  - **Transfer Manual**: Rekening bank resmi dengan fasilitas unggah bukti transfer aman dan verifikasi admin 1x24 jam.
  - **Donasi Anonim**: Opsi "Hamba Allah" untuk melindungi privasi identitas donatur.
  - **Resumable Payments**: Donatur terdaftar dapat melanjutkan pembayaran pending dari riwayat transaksi.
- **Keamanan Data Pribadi & Hak Akses (Hardened Security)**:
  - **Isolasi Dokumen KTP**: KTP dan dokumen identitas penggalang individu disimpan di private storage (`storage/app/private/`) dan dilarang muncul di publik.
  - **Fail-Closed Webhook**: Callback otomatis menolak request jika secret token tidak valid atau tidak dikonfigurasi.
  - **Anti-IDOR Authorization**: Endpoint sensitif dilindungi pengecekan kepemilikan dan token UUID guest.
  - **Anti-Tampering Amount Check**: Verifikasi presisi nominal donasi sebelum settlement disetujui.
- **Dashboard Terpisah**:
  - Dashboard Admin: Approval campaign, verifikasi penggalang, ekspor donasi, toggle legalitas, dan manajemen gateway.
  - Dashboard Penggalang: Pantau donatur, progres donasi, kelola update, dan tulis berita terkait campaign.

---

## 2. Tech Stack

- **Backend**: Laravel 12 (PHP 8.2 / 8.3)
- **Frontend**: Blade Templating, CSS Modular, Vanilla JS, Bootstrap Icons
- **Database**: MySQL 8.0+ / MariaDB 10.5+ (SQLite Memory untuk automated testing)
- **Authentication**: Laravel Breeze
- **Payment Gateway**: Midtrans Snap API, Flip Acceptance PWF API
- **Typography**: Inter (Google Fonts)

---

## 3. Persyaratan Sistem (Requirements)

- PHP >= 8.2 dengan ekstensi: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd`, `openssl`
- Composer 2.x
- MySQL 8.0+ atau MariaDB 10.4+
- Web server: Apache (dengan mod_rewrite) atau Nginx

---

## 4. Instalasi Lokal & Setup

1. **Clone Repository & Masuk ke Folder**:
   ```bash
   git clone <repository_url> orangbaik
   cd orangbaik
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Salin Environment File & Generate Key**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database pada `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=orangbaik
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan Migration & Storage Symlink**:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

6. **Jalankan Development Server**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser pada `http://127.0.0.1:8000`.

---

## 5. Pengujian (Testing)

Jalankan test suite menggunakan SQLite in-memory:
```bash
php artisan test
```

---

## 6. Dokumentasi Lengkap

Struktur dokumentasi teknis tersedia pada direktori `docs/`:
- [Arsitektur Aplikasi & Layering](docs/architecture/README.md)
- [Database Schema & ERD Rules](docs/database/README.md)
- [API & Spesifikasi Webhook](docs/api/README.md)
- [Security Policy & Klasifikasi Data](docs/security/README.md)
- [Standar Koding & Guideline Developer](docs/development/README.md)
- [Panduan Deployment Production & Gateway Readiness](docs/deployment/README.md)
- [Log Perubahan & Security Audit](CHANGELOG_AUDIT.md)

---

## 7. Kontribusi & Lisensi

Dikelola secara eksklusif untuk **Dompet Al-Qur'an Sidoarjo**. Semua hak cipta dilindungi undang-undang.
