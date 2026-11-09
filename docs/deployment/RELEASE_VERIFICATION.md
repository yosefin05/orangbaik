# OrangBaik.id — Final Release Verification

Tanggal: 2026-11-09

## Recovery progres sebelumnya

- Tiga sesi bertema final release check tersedia. Dua sesi pada 2026-11-08 memiliki transcript kosong; riwayat langkah dan hasil tidak dapat dipulihkan.
- `git status` bersih. Tidak ada `RELEASE_VERIFICATION.md`, test log terpisah, atau hasil CI.
- `CHANGELOG_AUDIT.md` mengklaim PHASE 0–4 `VERIFIED`/`FIXED`, tetapi tanpa transcript/output test klaim ini dicatat sebagai histori, bukan bukti final.
- Dokumentasi dan test tersedia di `docs/` dan `tests/`, tetapi keberadaan file bukan bukti pemeriksaan selesai.

Status: `VERIFIED` = bukti aktual; `FAILED` = masalah ditemukan; `BLOCKED` = dibatasi prasyarat; `NOT IMPLEMENTED` = belum tersedia; `NOT YET CHECKED` = belum diperiksa.

## Pemeriksaan sebelumnya vs baru

| Area | Sebelumnya | Baru |
|---|---|---|
| Storage/payment | Changelog mengklaim hardening private disk, webhook, amount, anti-rollback | Smoke test terbatas lulus; fallback public disk, raw logging, optional Flip amount ditemukan. |
| Regression | Changelog mengklaim feature test selesai | Tidak ada output historis; full regression diblokir karena larangan migration. |
| Production | Checklist deployment tersedia | Tidak ada verifikasi live/staging; diblokir. |

## Hasil audit baru

### Security dan payment

| Pemeriksaan | Status | Bukti aktual |
|---|---|---|
| KTP individu tidak publik | VERIFIED | `SecurityHardeningAuditTest::test_dokumen_individu_dilarang_publik` lulus. |
| Otorisasi dokumen owner/admin/non-owner | VERIFIED | Dua test authorization dokumen lulus. |
| Bukti transfer baru private | VERIFIED secara statis | `ManualTransferService.php` dan `config/filesystems.php` memakai/menyediakan disk private. |
| Bukti transfer lama aman | FAILED | `app/Http/Controllers/User/DonasiController.php:337` fallback ke `Storage::disk('public')`; file lama di public disk dapat diakses langsung. |
| Inventaris storage aktual | BLOCKED | Tidak mengakses production dan tidak menjalankan migration. |
| Midtrans key kosong ditolak | VERIFIED | Test fail-closed lulus. |
| Midtrans signature salah ditolak | VERIFIED | Test signature invalid lulus. |
| Flip token kosong/salah ditolak | VERIFIED | Dua test fail-closed/token mismatch lulus. |
| Raw webhook payload tidak dilog | FAILED | `app/Services/MidtransService.php:94` dan `app/Services/FlipService.php:353` menulis `$payload` penuh saat data tidak lengkap. |
| Flip settlement wajib punya nominal | FAILED | `app/Services/FlipService.php:423` hanya validasi bila amount tersedia; callback tanpa amount dapat lolos. |
| Route universal webhook | FAILED | `routes/web.php:56` memakai `/payment/webhook`, tetapi `PaymentWebhookController::handle` mewajibkan `$gateway`; route tidak memiliki `{gateway}`. |
| Settlement terminal | VERIFIED | Security smoke test settlement/resume lulus. |
| End-to-end amount/duplicate/settlement | BLOCKED | `Batch2PendingPaymentRecoveryTest` memakai `RefreshDatabase`; tidak dijalankan karena larangan migration. |

### Test dan production readiness

- `php artisan test tests/Feature/SecurityHardeningAuditTest.php`: **VERIFIED**, 9 passed, 14 assertions, 34.01s.
- `php artisan route:list --except-vendor`: **VERIFIED**, 169 route; mengonfirmasi route universal tidak cocok dengan controller.
- PHP lint: **FAILED** pada `tests/Feature/Batch2PendingPaymentRecoveryTest.php`; namespace bukan statement pertama pada line 3.
- Full regression admin/fundraiser/campaign/donasi manual: **BLOCKED**, suite memakai `RefreshDatabase`.
- Production config, filesystem permissions, dashboard webhook, live smoke test: **BLOCKED**; tidak mengakses production/staging atau `.env`.

## CODEBASE QUALITY

Pemeriksaan statis; tidak ada refactor.

### CRITICAL

Belum ada temuan code-quality terpisah berstatus CRITICAL. Temuan HIGH di bawah tetap memblokir release.

### HIGH

1. **Route universal webhook tidak sesuai signature controller** — `routes/web.php` dan `app/Http/Controllers/PaymentWebhookController.php`. Dampak: dynamic gateway path tidak bekerja sesuai kontrak. Rekomendasi: tetapkan satu kontrak route/controller, tambahkan `{gateway}` atau hapus endpoint setelah caller dipetakan.
2. **Payload webhook mentah masuk log** — `app/Services/MidtransService.php` dan `app/Services/FlipService.php`. Dampak: data callback tersimpan di log. Rekomendasi: log allow-list field non-secret.
3. **Fallback public disk mempertahankan risiko dokumen lama** — `app/Http/Controllers/User/DonasiController.php`. Dampak: dokumen lama mungkin tetap dapat diakses langsung. Rekomendasi: inventory/migrasi terkontrol, lalu hapus fallback setelah validasi.

### MEDIUM

1. **Duplikasi endpoint Midtrans** — `routes/web.php` memiliki `/payment/midtrans/webhook` dan `/midtrans/notification`; `routes/api.php` memiliki `/api/midtrans/notification`. Dampak: konfigurasi/middleware dapat berbeda. Rekomendasi: pilih endpoint canonical dan dokumentasikan alias legacy.
2. **Dua orchestration path webhook** — `PaymentWebhookController`, `MidtransController`, dan `FlipController`. Dampak: validasi, response, dan logging mudah drift. Rekomendasi: satu orchestration path dengan adapter gateway.
3. **View/asset tersebar** — 85 Blade files, 1 CSS, 2 JS, 230 pemakaian layout/component/partial. Dampak: selector/style dan behavior sulit dilacak. Rekomendasi: inventaris boundary sebelum refactor.

### LOW

1. **Indentasi `AppServiceProvider::boot`** — `app/Providers/AppServiceProvider.php:29`, `app()->setLocale('id')` tidak sejajar. Dampak kosmetik; rapikan pada perubahan provider berikutnya.
2. **Penamaan campuran** — `Penggalang_Dana_Dokumen`, parameter `{id}`, `{penggalangDana}`, `{beritum}`, serta route snake_case/kebab-case bercampur. Dampak: onboarding dan binding lebih sulit; tetapkan konvensi dan ubah bertahap setelah baseline test.

## Keputusan release

- Security smoke test: **VERIFIED terbatas**.
- Final release verification: **FAILED / BLOCKED**. Belum release-ready karena raw logging, optional Flip amount, legacy public-storage fallback, route universal yang rusak, lint failure, dan regression end-to-end yang belum terbukti.
- Tidak ada kode yang diubah. Refactoring menunggu persetujuan Anda.
