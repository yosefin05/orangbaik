# AI Handoff — Personalization & WhatsApp

Tanggal checkpoint: 2026-11-19

## Pekerjaan selesai

- Form donasi menerima sapaan `Kak`, `Pak`, atau `Bu` dengan default `Kak` untuk guest dan user login.
- `DonasiRequest` memakai whitelist; transaksi menyimpan sapaan di kolom `donasi.sapaan`.
- Migration additive memberi default `Kak`, sehingga rekaman lama tetap kompatibel.
- `DonorWhatsAppMessage` menangani normalisasi nomor Indonesia dan template follow-up/terima kasih.
- Accessor model memakai template tersebut; tombol WhatsApp admin yang sudah ada hanya tampil saat status `pending` atau `settlement` dan nomor lolos validasi.
- Pesan settlement memuat route e-kwitansi sementara bertanda tangan (30 hari), tetap memeriksa settlement dan dapat dibuka guest; route kwitansi akun tetap memeriksa owner/admin.

## Bukti pemeriksaan

- Seluruh Feature suite: **126 passed, 562 assertions**, setelah memperbarui request lama dan menambah test search expired/UI sapaan.
- `DonorWhatsAppPersonalizationTest`: 3 passed/21 assertions; `AdminDonasiTest`: 3 passed/22 assertions.
- Test donasi guest/login, whitelist, akses e-kwitansi guest settlement dan signature palsu: 1 passed/14 assertions.
- PHP lint pada seluruh file PHP berubah/untracked, route list, `git diff --check`, dan Vite production build: lulus.
- Migration berjalan melalui SQLite `RefreshDatabase` dan skema disposable lokal MariaDB `10.4.32`; record lama otomatis memperoleh default `Kak`. Dokumentasi deployment mensyaratkan MariaDB `10.5+`/MySQL `8.0+`, sementara versi staging tidak tersedia, jadi kompatibilitas target belum dipastikan. Skema QA lokal dibuat terpisah dan tidak dihapus.
- Test search expired dan render pilihan sapaan guest/login sudah ditambahkan. Edge ada di host, tetapi uji viewport headless tidak menghasilkan screenshot yang dapat diperiksa; visual mobile/desktop belum lulus uji browser.
- Gateway Midtrans/Flip live dan staging belum diuji.

## Penghalang deployment

Branch sumber `main` (`7fb041b`, tracking `origin/main`) masih memiliki 34 path lokal. Branch `release/orangbaik-evaluation-20261119` dibuat dari `7fb041b`, berisi manifest 33 path A+B, dan dipush ke `origin` pada commit `d7cb9cb` (kemudian checkpoint ini diperbarui). Worktree terpisah lulus 126 Feature tests/562 assertions dan `npm run build`. Worktree memakai `.env` test-only dan junction read-only ke `vendor`/`node_modules`; `.env` root tidak diubah. Tidak ada workflow run untuk branch rilis: workflow `.github/workflows/main.yml` hanya deploy saat push `main`, menarik seluruh `main` ke staging, dan tidak mencadangkan database/file, menjalankan migration, smoke test, atau rollback. Versi MySQL staging, backup, konfigurasi target terverifikasi, dan akses live belum tersedia. Staging belum dideploy; production tidak disentuh.

## Langkah berikutnya

1. Konfirmasi MySQL staging dan backup database/file sebelum migration.
2. Siapkan workflow rilis branch yang terisolasi, backup, migration, health check, serta rollback; workflow saat ini hanya menarik `main`.
3. Lakukan uji visual viewport mobile/desktop dan alur utama pada browser/staging.
4. Setelah prasyarat siap, commit/push branch rilis, pantau workflow, dan smoke test staging. Jangan push `main` karena berisi 34 path lokal.
