# QA Matrix — Finalisasi OrangBaik

Tanggal pemeriksaan: 2026-11-19

Status `PASS` berarti ada bukti test otomatis atau pemeriksaan runtime; `NOT TESTED` berarti belum ada bukti yang memadai.

| Fitur evaluasi | Status | Bukti / batas pemeriksaan |
|---|---|---|
| Registrasi, auto-login, duplicate email | PASS | Full Feature suite; `AuthFlowHardeningTest`, `RegistrationTest`, dan `FullFormAuditTest` lulus |
| Login, logout, reset/password update | PASS | Authentication, password, dan auth hardening tests lulus |
| Proteksi halaman/aksi admin | PASS | Admin dan security Feature tests lulus |
| Keamanan campaign / ownership / campaign berakhir saat donasi | PASS | Hardening tests lulus; campaign tidak aktif/expired ditolak pada GET/POST |
| Penyaringan campaign berakhir pada hasil search | PASS | Feature test memastikan campaign aktif tampil dan campaign expired tidak tampil |
| Donasi anonim dan redaksi data publik | PASS | Anonymous privacy dan donation Feature tests lulus |
| Status settlement dan agregat campaign | PASS | Midtrans/Flip webhook, amount matching, idempotency, dan status tests lulus |
| Integrasi gateway live Midtrans/Flip | NOT TESTED | Test memakai fixture/mock/test credentials; tidak membuat transaksi eksternal |
| Donasi manual dan upload bukti privat/otorisasi | PASS | Recovery, upload guest token, status terminal, dan security tests lulus |
| Admin kelola donasi dan tombol WhatsApp pending/settlement | PASS | Feature test merender admin detail untuk status pending/settlement dan memeriksa URL pesan masing-masing |
| WhatsApp failed/expired disembunyikan | PASS | Feature test memastikan URL null untuk failed/expired/expire |
| Sapaan Kak/Pak/Bu, default guest/login, persistensi dan whitelist | PASS | Form guest/login merender tiga pilihan dan default Kak; Guest Kak dan login Bu tersimpan; nilai di luar whitelist ditolak |
| Normalisasi nomor 08/628/+628 dan URL encoding | PASS | Feature tests untuk tiga format dan nomor kosong/invalid |
| E-kwitansi settlement dan otorisasi | PASS | Guest dapat membuka signed receipt settlement; signature palsu ditolak; owner/admin/non-owner dan non-settlement tests lulus |
| Pendaftaran penggalang dana dan upload dokumen | PASS | Feature form dan dokumen/privacy authorization tests lulus |
| Beranda, berita, kalkulator, profil dan admin content | PASS | Feature tests yang tersedia lulus |
| Search campaign/berita end-to-end di browser | NOT TESTED | Tidak ada browser automation dalam pemeriksaan ini |
| Responsive mobile/desktop dan bottom navigation | NOT TESTED | CSS ada, tetapi tidak ada pengujian browser/device/visual |
| Seluruh Feature suite | PASS | `php artisan test --testsuite=Feature --no-coverage`: 126 passed, 562 assertions |
| PHP lint file PHP berubah/untracked | PASS | Lint ulang termasuk test search baru dijalankan setelah seluruh test |
| Route registration | PASS | `php artisan route:list --except-vendor` berhasil; signed receipt route terdaftar |
| `git diff --check` | PASS | Lulus; hanya peringatan CRLF dari file lama yang berubah |
| Migration pada SQLite test database | PASS | Migration diaplikasikan oleh `RefreshDatabase` di Feature suite |
| Migration pada MariaDB lokal | PASS | `10.4.32`; migration additive dijalankan pada schema QA disposable, record legacy menjadi `Kak` |
| Migration pada MySQL/MariaDB staging | NOT TESTED | Dokumentasi menyebut MySQL 8.0+/MariaDB 10.5+, tetapi versi target tidak diketahui |
| Build aset production | PASS | `npm run build` lulus di worktree rilis |
| Branch rilis / GitHub Actions | PASS / NOT RUN | `release/orangbaik-evaluation-20261119` dipush; tidak ada workflow run karena file workflow hanya trigger `main` |
| Backup, rollback, staging deployment, live smoke test | NOT TESTED | Tidak ada bukti backup/rollback atau akses target; push branch rilis tidak deploy ke staging |

## Catatan regresi

Empat Feature test awal gagal karena request test tidak menyertakan field `sapaan` yang kini required. Fixture/request test diperbarui ke default `Kak`; regresi penuh berikutnya lulus 126/126. Validasi backend tetap strict.

Render HTML aplikasi lokal melalui XAMPP berhasil pada beranda dan form donasi. Pemeriksaan viewport browser tidak selesai: Edge tersedia, tetapi headless invocation tidak menghasilkan screenshot yang dapat diverifikasi dan tidak ada harness browser automation. Ini tidak dihitung sebagai QA visual responsive.

## Audit path lokal (34)

`A` = wajib untuk evaluasi/rilis, `B` = pekerjaan sebelumnya yang mendukung fitur evaluasi/keamanan, `C` = tidak terkait atau tidak perlu dirilis. Candidate manifest saat ini mencakup A+B; `public/css/login.css` dikecualikan.

| # | Path | Kelas | Keputusan / dependency |
|---:|---|:---:|---|
| 1 | `CHANGELOG_AUDIT.md` | A | Catatan implementasi dan hasil QA |
| 2 | `app/Http/Controllers/Auth/RegisteredUserController.php` | A | Pesan validasi registrasi; dipakai auth flow |
| 3 | `app/Http/Controllers/User/DonasiController.php` | A | Validasi kelayakan campaign dan simpan sapaan |
| 4 | `app/Http/Controllers/User/PenggalangDanaController.php` | B | Otorisasi profil pending/rejected dan privasi dokumen |
| 5 | `app/Http/Controllers/User/RiwayatDonasiController.php` | A | Otorisasi kwitansi pemilik/admin dan signed guest receipt |
| 6 | `app/Http/Controllers/admin/PenggalangDanaController.php` | B | Search/filter penggalang dan data kategori |
| 7 | `app/Http/Requests/DonasiRequest.php` | A | Whitelist sapaan |
| 8 | `app/Models/Campaign.php` | B | Alias tanggal berakhir yang dipakai view lama |
| 9 | `app/Models/Donasi.php` | A | Sapaan fallback, display aman, nomor dan URL WhatsApp status-specific |
| 10 | `bootstrap/app.php` | A | Fallback redirect untuk respons 419 |
| 11 | `public/css/login.css` | C | Perubahan weight font kosmetik; tidak dibutuhkan evaluasi |
| 12 | `resources/views/admin/donasi/index.blade.php` | A | Tombol WhatsApp pada daftar admin |
| 13 | `resources/views/admin/donasi/show.blade.php` | A | Detail admin, tombol WhatsApp dan aksi transfer |
| 14 | `resources/views/admin/penggalang_dana/index.blade.php` | B | Form filter kategori/status penggalang |
| 15 | `resources/views/admin/penggalang_dana/show.blade.php` | B | Label kategori penggalang |
| 16 | `resources/views/pages/campaign/show.blade.php` | B | Anonimitas donatur dan CTA campaign nonaktif |
| 17 | `resources/views/pages/donasi-bayar.blade.php` | A | Pilihan sapaan guest/login |
| 18 | `resources/views/pages/kwitansi.blade.php` | A | Data kuitansi transaksi; tidak mengubah tata desain |
| 19 | `resources/views/pages/profil-penggalang.blade.php` | B | Redaksi data kontak/dokumen berdasarkan akses |
| 20 | `resources/views/pages/search.blade.php` | A | Filter tanggal berakhir pada hasil campaign |
| 21 | `routes/web.php` | A | Route signed receipt |
| 22 | `tests/Feature/Batch2PendingPaymentRecoveryTest.php` | A | Fixture sapaan untuk regresi guest/payment |
| 23 | `tests/Feature/FullFormAuditTest.php` | A | Form, persistensi guest/login, WhatsApp admin, e-kwitansi |
| 24 | `tests/Feature/PaymentChannelTest.php` | A | Fixture sapaan untuk payment channel tests |
| 25 | `tests/Feature/PreLaunchHardeningTest.php` | A | Fixture sapaan untuk anonim/custom nominal |
| 26 | `AI_HANDOFF.md` | A | Checkpoint handoff |
| 27 | `QA_MATRIX.md` | A | QA dan manifest path |
| 28 | `app/Services/DonorWhatsAppMessage.php` | A | Template dan normalisasi WhatsApp |
| 29 | `database/migrations/2026_11_19_000001_add_sapaan_to_donasi_table.php` | A | Kolom sapaan default Kak |
| 30 | `public/css/admin/donasi/show.css` | A | Dependency stylesheet detail admin donasi |
| 31 | `resources/views/errors/409.blade.php` | B | Halaman error session conflict |
| 32 | `resources/views/errors/419.blade.php` | A | Error token/session expired; dipasangkan dengan bootstrap handler |
| 33 | `tests/Feature/Batch1And2EvaluationHardeningTest.php` | A | Security/evaluation regression dan search test |
| 34 | `tests/Feature/DonorWhatsAppPersonalizationTest.php` | A | Nomor, template, receipt signature, akses admin |
