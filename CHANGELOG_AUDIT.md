# CHANGELOG AUDIT & HARDENING — ORANGBAIK.ID

Dokumen audit, perbaikan keamanan, hardening arsitektur, dan status testing untuk platform crowdfunding **OrangBaik.id** (Dompet Al-Qur'an Sidoarjo).

---

## Ringkasan Eksekutif & Baseline Status

- **Framework**: Laravel 12 / PHP 8.2+
- **Database**: MySQL / MariaDB (production/local) & SQLite Memory (testing)
- **Status Awal Git**: Bersih (`clean working tree`), branch tracking aktif
- **Status Pengerjaan**:
  - PHASE 0 (Baseline Audit): **VERIFIED**
  - PHASE 1 (P0 Critical Security & Document Protection): **FIXED & VERIFIED**
  - PHASE 2 (Backend Refactoring & Authorization Hardening): **FIXED**
  - PHASE 3 (Database Additive Migration & Isolation): **FIXED**
  - PHASE 4 (Testing & Complete Technical Documentation): **FIXED**

---

## Log Aktivitas & Perubahan

### [2026-11-08] Baseline Audit & Identifikasi Kerentanan Kritis (PHASE 0)
- **Modul**: Security Architecture & Data Protection
- **Status**: FIXED
- **Temuan Risiko Tinggi (P0)**:
  1. **Kebocoran Dokumen KTP pada Profil Publik**:
     - `resources/views/pages/profil-penggalang.blade.php` memuat relasi `penggalangDanaDokumen` tanpa filter visibilitas.
     - Link `file_dokumen` terpampang secara publik ke seluruh pengunjung, termasuk dokumen KTP penggalang dana individu.
     - Tidak ada pemisahan eksplisit antara dokumen publik (legalitas organisasi) dan privat (KTP/identitas).
  2. **Webhook Flip Fail-Open saat Secret Kosong**:
     - `app/Services/FlipService.php` (`handleWebhook`): saat `config('payment.flip.webhook_token')` kosong, pengecekan `hash_equals` dilewati (`if (!empty($expectedToken))`). Siapa pun dapat mengirim callback palsu yang mengubah status transaksi menjadi `settlement`.
     - Log mencatat token expected sebagian (`substr(..., 0, 4)`).
  3. **Pencatatan Payload Sensitif pada Controller Webhook**:
     - `app/Http/Controllers/PaymentWebhookController.php` mencatat seluruh HTTP headers (berpotensi membocorkan header otentikasi) dan body mentah.
     - `app/Http/Controllers/MidtransController.php` mencatat payload mentah langsung ke file log.
  4. **Penyimpanan Bukti Transfer Manual di Disk Publik**:
     - `app/Services/ManualTransferService.php` menyimpan bukti transfer donatur ke disk `'public'` (`bukti-transfer/`), sehingga bukti transaksi perbankan donatur dapat diakses langsung via URL publik tanpa otorisasi.
  5. **Endpoint Akses Dokumen Belum Menggunakan Otorisasi & Private Disk**:
     - Pengunggahan dokumen verifikasi penggalang dana dan bukti transfer harus menggunakan storage terisolasi dengan endpoint bertingkat otorisasi (Owner & Admin saja).
  6. **Celah Otorisasi / IDOR pada Campaign Management**:
     - `User/CampaignController@update`: Tidak ada pengecekan otorisasi sama sekali, memungkinkan user terautentikasi memodifikasi campaign milik penggalang dana lain.
     - `User/CampaignController@edit` & `@destroy`: Potensi fatal error jika relasi `penggalangDana` null dan belum mengizinkan peran Admin.

---

### [2026-11-08] Penanganan P0 — Perlindungan Dokumen & Data Sensitif (PHASE 1)
- **Modul**: Dokumen Penggalang Dana, Bukti Transfer, dan Webhook
- **Status**: FIXED & VERIFIED
- **Tindakan Perbaikan**:
  1. **Isolasi Dokumen & Klasifikasi Data**:
     - Membuat migration additive `database/migrations/2026_11_08_000001_add_is_public_and_storage_disk_to_penggalang_dana_dokumen_table.php` menambahkan kolom `is_public` (default `false`) dan `storage_disk` (default `'private'`).
     - Mengupdate model `Penggalang_Dana_Dokumen` dengan scope `publicOnly`, helper `isPublicForVisitor`, `canBeAccessedBy`, dan `getSecureViewUrlAttribute`. Dokumen penggalang individu diisolasi 100% PRIVATE.
     - Mengubah `PenggalangDanaController@profile` agar TIDAK memuat relasi dokumen identitas KTP individu ke profil publik.
     - Memperbarui `pages/profil-penggalang.blade.php`: seksi "Informasi Legalitas" disembunyikan total untuk penggalang individu, dan untuk organisasi hanya merender dokumen berstatus publik yang aman.
  2. **Controller Pengambilan Dokumen Terproteksi**:
     - Membuat `DokumenPenggalangController` (`route('dokumen.show')`) dengan otorisasi berbasis kepemilikan dan peran admin, validasi skema URL http/https, mitigasi path traversal, serta header `X-Content-Type-Options: nosniff`.
  3. **Pengamanan Bukti Transfer Manual Donatur**:
     - Menambahkan konfigurasi disk `'private'` pada `config/filesystems.php`.
     - Mengubah `ManualTransferService@saveBuktiTransfer` agar menyimpan file bukti transfer ke disk `private`.
     - Menambahkan endpoint aman `DonasiController@viewBuktiTransfer` (`route('donasi.bayar.bukti_transfer')`) dengan verifikasi kepemilikan donasi dan `payment_token` guest anti-IDOR.
     - Mengubah view `pages/donasi-instruksi.blade.php` dan `admin/donasi/show.blade.php` untuk menggunakan endpoint aman tersebut.
  4. **Hardening Webhook & Financial Integrity**:
     - `FlipService@handleWebhook`: Dibuat **Fail-Closed**. Jika `FLIP_WEBHOOK_TOKEN` kosong atau tidak cocok, webhook ditolak seketika (`return false`). Log yang membocorkan substring token dihapus.
     - `MidtransService@handleWebhook`: Dibuat **Fail-Closed** jika server key kosong. Fallback config ditambahkan untuk membaca `config('payment.midtrans.server_key')`.
     - `PaymentWebhookController` dan `MidtransController`: Log disanitasi agar tidak mencatat authorization header atau raw payload.
  5. **Penutupan IDOR pada Campaign & Campaign Update**:
     - Memperbaiki `CampaignController@edit`, `@update`, dan `@destroy` dengan otorisasi kepemilikan (`isOwner`) dan bypass untuk Admin.
     - Memperbaiki `CampaignUpdateController@store`, `@edit`, `@update`, dan `@destroy` agar konsisten menggunakan helper `authorizedCampaign($slug)`.
  6. **Perbaikan Layout Admin**:
     - Menghapus parameter berlebih `$penggalang->id` pada `route('campaign.create')` di `resources/views/layouts/admin.blade.php`.

---

### [2026-11-08] Dokumentasi & Testing (PHASE 4)
- **Modul**: Dokumentasi Teknis & Feature Test
- **Status**: FIXED & VERIFIED
- **Deliverables**:
  - `README.md`: Deskripsi lengkap proyek OrangBaik.id, tech stack, instalasi lokal, dan panduan kontribusi.
  - `docs/security/README.md`: Data classification, authorization matrix, document storage policy, payment security, and webhook security.
  - `docs/architecture/README.md`: Arsitektur MVC, Action/Service layering, flow diagram, dan dependency mapping.
  - `docs/database/README.md`: Schema tables, relasi ERD, integrity rules, dan transaction status definition.
  - `docs/api/README.md`: Spesifikasi webhook Midtrans & Flip serta public/user endpoints.
  - `docs/development/README.md`: Standar coding, penamaan, PSR compliance, dan testing guidelines.
  - `docs/deployment/README.md`: Checklist kesiapan live production, konfigurasi .env, HTTPS webhook requirement, dan panduan `composer dump-autoload -o` untuk Linux.
  - `tests/Feature/SecurityHardeningAuditTest.php`: 10 skenario feature test mencakup isolasi KTP, otorisasi IDOR dokumen, fail-closed webhook Flip & Midtrans, anti-rollback settlement, dan perlindungan kepemilikan campaign.
