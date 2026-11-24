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
### [2026-11-19] Personalisasi Sapaan Donatur & WhatsApp
- **Status**: IMPLEMENTED; targeted tests verified. Deployment blocked by release prerequisites.
- Menambahkan pilihan sapaan Kak/Pak/Bu (default Kak), validasi whitelist, dan penyimpanan pada record transaksi.
- Menambahkan migration additive `2026_11_19_000001_add_sapaan_to_donasi_table.php` dengan default Kak agar data lama kompatibel.
- Memisahkan template WhatsApp ke `DonorWhatsAppMessage`, menormalisasi nomor Indonesia, dan hanya menghasilkan URL untuk pending/settlement dengan nomor valid.
- Follow-up meminta penyelesaian pembayaran; settlement menyertakan ucapan terima kasih, doa, nominal, campaign, dan route e-kwitansi resmi bertanda tangan sementara (30 hari), termasuk untuk guest serta tetap memeriksa settlement.
- Tombol admin yang tersedia dikembangkan, tidak diduplikasi.
- Test aktual: seluruh Feature suite `126 passed/562 assertions`; mencakup WhatsApp, sapaan, guest/login, search campaign expired, settlement receipt, keamanan, autentikasi, fundraiser, dan pembayaran. Di worktree rilis, suite yang sama lulus; lint, route list, `git diff --check`, dan `npm run build` lulus.
- Empat test request di `Batch1And2EvaluationHardeningTest`, `Batch2PendingPaymentRecoveryTest`, `PaymentChannelTest`, dan `PreLaunchHardeningTest` dilengkapi sapaan `Kak` setelah regresi awal membuktikan request test lama gagal karena field baru wajib.
- Migration diuji dengan SQLite `RefreshDatabase` dan skema disposable MariaDB lokal `10.4.32`; record lama mendapat default Kak. Target staging tidak memiliki versi DB yang terdokumentasi; kebutuhan minimum proyek MariaDB 10.5+/MySQL 8.0+.
- Test viewport browser tidak dapat diselesaikan: Edge headless tidak menghasilkan screenshot yang bisa dinilai. Aplikasi lokal merender home/form via XAMPP dan Feature tests memeriksa opsi form/link status. Gateway live belum dites.
- Branch `release/orangbaik-evaluation-20261119` dari `7fb041b` berisi manifest 33 path A+B; CSS kosmetik login dikecualikan. Commit `d7cb9cb` dibuat dan dipush ke origin; checkpoint dokumentasi ini kemudian diperbarui.
- GitHub Actions tidak melaporkan run untuk commit branch release. Staging tidak dijalankan karena workflow hanya memicu `main`, menarik seluruh branch, dan tidak memiliki backup, migration, health check, atau rollback. Versi DB/backup staging belum tersedia; production tidak disentuh.


### [2026-11-23] Manual Transfer Expiration & Comprehensive First-Pass Audit
- **Status**: IMPLEMENTED ON REVIEW BRANCH; TEST EXECUTION PENDING.
- Menambahkan aksi admin untuk menandai pembayaran transfer manual `pending` menjadi `expired` setelah donatur tidak merespons tindak lanjut.
- Transisi memakai `ManualTransferService` dan row lock; hanya pembayaran dengan gateway `manual` dan status `pending` yang dapat diubah. Settlement, failed, expired, dan transaksi gateway lain ditolak.
- Metadata audit disimpan pada `pembayaran.expired_at`, `expired_by`, dan `expiration_reason`; migration additive disediakan.
- Admin detail menampilkan aksi konfirmasi, catatan tindak lanjut, waktu kedaluwarsa, dan alasan. Status legacy `expire` dan canonical `expired` dinormalisasi pada daftar dan export.
- Route/UI Edit dan Hapus transaksi admin yang tidak memiliki view edit/create terpasang di repository dihilangkan; route Kelola Donasi dibatasi ke index/detail dan aksi pembayaran eksplisit untuk melindungi jejak finansial.
- Menambahkan `tests/Feature/ManualTransferExpirationTest.php` untuk akses admin, audit metadata, penolakan gateway non-manual, status terminal, dan akses non-admin. Test belum dieksekusi pada branch ini.
- Audit awal code-level didokumentasikan di `AUDIT_REVIEW_2026-11-23.md`. Temuan prioritas meliputi generic webhook yang belum aman/route tidak cocok, sanitizer rich text custom yang belum cukup kuat untuk konten raw HTML, inkonsistensi status capture, total nominal riwayat donor yang mencakup transaksi non-settlement, pagination/performa pencarian, dan header mobile kategori yang menyembunyikan search.
- Belum ada merge ke main, deployment staging, migration staging, atau perubahan database runtime.

---

## 24 November 2026 — Payment Gateway Hardening (WIP / PR #8)

**Branch:** `feat/payment-gateway-hardening`  
**Pull request:** https://github.com/yosefin05/orangbaik/pull/8  
**Scope:** staging-safe code changes only. No production deployment, live credentials, or production database changes.

### Changes implemented
- Encrypt new and existing Midtrans/Flip secrets at rest using Laravel Crypt; retain backwards-compatible decryption for legacy values.
- Keep secrets out of admin page payloads and logs; secret inputs stay blank and preserve existing values unless replaced.
- Separate Sandbox and Production credentials for Midtrans (Server Key, Client Key, Signature Secret Key) and Flip (API Secret Key, webhook token).
- Resolve credentials according to the transaction's environment. Local, testing, and staging environments are forced to Sandbox; the selected environment is retained in the payment record for webhook verification.
- Verify Midtrans notifications using the separate Signature Secret Key, with a legacy fallback for older Midtrans configurations where Server Key and signature secret were the same.
- Reject unsupported provider adapters instead of silently using a generic API driver.
- Align the dynamic payment webhook route and return generic errors to webhook callers.
- Tighten callback checks for gateway ownership, signature/token, known statuses, amount integrity, and terminal-state idempotency.
- Remove automatic retries for Flip bill creation because ambiguous network failures could otherwise create duplicate bills.
- Add manual transfer account-holder and account-number fields, a channel active toggle, and safeguards against changing bank/provider identity after transaction history exists.
- Hide unconfigured payment channels from donor checkout and enforce Flip's documented Rp10.000 minimum.
- Expand CI to run regression checks on feature branch pushes as well as pull requests.

### Verification
- Latest GitHub Actions run on commit `fe2c304e2a71a23665283bac66e6fb2d6e122484`: frontend build, manual-transfer expiry, fundraiser referral, donor WhatsApp expiry, gateway security, payment channel, and webhook regression groups all passed.
- These are automated regression tests, not an end-to-end provider certification. No merchant Sandbox or Live credentials have been tested.

### Remaining before merge / production
- Flip Accept Payment v3 is documented but currently only available in Flip's staging environment; obtain staging access from the Flip PIC/B2B integration team before implementing and testing the v3 adapter. Keep the existing v2 flow isolated until that migration can be validated.
- Add provider-specific connectivity tests and verify exact callback setup with the merchant dashboards.
- Run Midtrans Sandbox and Flip's supported test-mode flows, including callback delivery, duplicate callbacks, expiry, amount mismatch, and settlement verification.
- Finish audit logging for gateway/channel changes and complete final admin UI review.
- Obtain the authorized merchant/account owner's review and approval before entering Live credentials or enabling Production payments.

**Status:** WIP — do not merge or enable real-money payments yet.


## 24 November 2026 — Payment Method Routing (PR #12)

- Pisahkan kategori metode donasi menjadi QRIS, E-Wallet, Virtual Account, dan Transfer Manual.
- Tambahkan deklarasi kapabilitas metode pada provider agar routing hanya dapat diarahkan ke adapter yang mendukung tipe pembayaran terkait.
- Tambahkan pengaturan routing provider pada detail Payment Gateway.
- Simpan snapshot gateway pada setiap transaksi dan backfill transaksi lama dari channel yang digunakan, sehingga perubahan routing tidak mengubah kepemilikan webhook transaksi lama.
- Tambahkan regression test untuk snapshot gateway transaksi dan kapabilitas provider.
- Default yang dipertahankan: QRIS/E-Wallet melalui Midtrans, Virtual Account melalui Flip, Transfer Manual melalui rekening manual.
- Halaman donasi selalu menampilkan keempat section dan otomatis membuka kategori pertama yang memiliki metode aktif.
- Belum dilakukan pengujian koneksi API provider atau transaksi Sandbox/Production.


### [2026-11-24] Admin Payment Configuration UI Polish
- **Status**: IMPLEMENTED ON REVIEW BRANCH; CI / visual verification pending.
- Audit menemukan selector halaman gateway yang terlalu generik, routing form tanpa layout responsif khusus, serta baris metode pembayaran yang menumpuk kontrol routing dan status.
- Memperbarui tampilan ringkasan Payment Gateway dengan selector yang terisolasi, hierarki status yang lebih jelas, kartu konsisten, dan tata letak mobile.
- Memperbaiki halaman detail provider: ringkasan konfigurasi, panel kredensial, daftar metode, kontrol routing, dan aksi status memiliki jarak serta susunan responsif.
- Memperbaiki daftar rekening transfer manual agar form rekening lebih konsisten, field tidak overflow, dan kontrol mobile tersusun vertikal.
- Perubahan hanya pada Blade/CSS; tidak mengubah route, model, database, credential, maupun perilaku transaksi.
- Belum dilakukan inspeksi visual pada browser staging; perlu verifikasi setelah CI/deploy dan review viewport desktop/mobile.
