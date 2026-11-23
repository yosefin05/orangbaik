# Audit Review OrangBaik — 23 November 2026

## Status pemeriksaan

Ini adalah audit statis berbasis source code branch `main` dan dokumentasi QA yang tersedia. Pemeriksaan mencakup alur donasi/pembayaran, webhook, route dan otorisasi, dashboard role, pencarian, berita/rich text, header/footer, serta halaman admin donasi.

**Batas pemeriksaan:** browser visual/responsive, screen reader, transaksi gateway live, versi database staging, backup/restore, dan smoke test staging belum diverifikasi. Status temuan di bawah bukan klaim bahwa seluruh aplikasi sudah lulus QA.

## Perubahan yang sedang disiapkan pada branch ini

1. Tambah aksi admin untuk menandai pembayaran transfer manual berstatus `pending` menjadi `expired`.
2. Aksi memakai `ManualTransferService` dan row lock yang sama dengan approve/reject; hanya gateway `manual` dan status `pending` yang dapat diubah.
3. Simpan metadata audit: waktu kedaluwarsa, ID admin, dan alasan/catatan tindak lanjut.
4. Tampilkan tombol hanya untuk transfer manual pending, dengan dialog konfirmasi dan catatan opsional.
5. Samakan label/filter/export untuk status `expired` dan legacy `expire`.
6. Batasi route Kelola Donasi pada halaman yang benar-benar tersedia (`index`, `show`) dan aksi pembayaran eksplisit. Link Edit/Create yang merujuk view yang tidak ada dan aksi hapus permanen transaksi dihilangkan dari UI/route agar riwayat finansial tidak mudah terhapus.

## Temuan prioritas

| Prioritas | Area | Temuan | Dampak / rekomendasi |
|---|---|---|---|
| P1 | Webhook gateway kustom | `PaymentWebhookController::handle(Request, string $gateway)` mengharapkan parameter gateway, tetapi route saat ini hanya mendaftarkan `POST /payment/webhook` tanpa parameter dan tanpa nama route. Sementara `GenericApiDriver` membangun callback dengan route name `payment.gateway.webhook`, yang tidak ditemukan pada `routes/web.php`. | Callback gateway kustom tidak konsisten/berpotensi gagal dibuat. Sebelum fitur gateway kustom diaktifkan, betulkan kontrak route dan tambahkan autentikasi webhook fail-closed, amount validation, serta status-transition guard. |
| P1 | Keamanan konten rich text | `App\\Support\\RichText::clean` memakai `strip_tags` dan regex buatan sendiri, tetapi konten berita/update ditampilkan memakai raw Blade `{!! ... !!}`. Filter URL hanya memeriksa string literal `javascript:`; entity-encoded atau variasi URL berbahaya belum ditangani dengan sanitizer HTML berbasis allowlist yang teruji. | Risiko stored XSS pada halaman berita/update/deskripsi jika input melewati filter sederhana. Ganti dengan HTML sanitizer teruji dan allowlist tag/atribut/protokol; tambah regression tests untuk payload XSS. |
| P1 | Webhook gateway kustom | `GenericApiDriver::handleWebhook` menerima order ID dan status dari payload tanpa memeriksa `webhook_token`/signature, tanpa amount validation, dan dapat memetakan status payload menjadi settlement. | Jika route generic diperbaiki tanpa hardening, pihak yang dapat mengirim callback berpotensi memalsukan status transaksi. Jangan aktifkan generic gateway sampai verifikasi autentikasi wajib. |
| P1 | Riwayat transaksi donor | `RiwayatDonasiController::index` menghitung `totalNominal` dari seluruh transaksi, termasuk pending/failed/expired, bukan hanya settlement. Status expired juga digabung ke label `Gagal`. | Angka “Total Donasi” dapat menyesatkan donatur. Pisahkan nominal berhasil (settlement) dari nilai transaksi, dan tampilkan status “Kedaluwarsa” secara khusus. |
| P2 | Status pembayaran | Riwayat donasi dan endpoint e-kwitansi terautentikasi masih menerima `capture` sebagai status selesai, sementara aturan internal menyatakan `settlement` sebagai status canonical dan signed receipt sudah mensyaratkan settlement. | Samakan semua riwayat, tombol kwitansi, dan statistik pada satu aturan: settlement saja. Audit record legacy sebelum mengubah tampilan data lama. |
| P2 | Pencarian | `SearchController` default ke campaign jika parameter `type` tidak ada, sehingga tidak dapat mengetahui halaman asal berita tanpa konteks eksplisit. Hasil campaign/berita memakai `get()`, bukan pagination. | Pertahankan `type` dari form sumber atau tambahkan mekanisme konteks yang aman; gunakan pagination agar hasil besar tidak membebani memori dan halaman. |
| P2 | Header mobile | Ketika `$headerTitle` kategori terisi, komponen header mengganti search bar dengan judul kategori. | Bertentangan dengan kebutuhan search mobile yang tetap terlihat. Perlu desain ulang agar judul/filter kategori tidak menghilangkan akses pencarian. |
| P2 | Performa beranda | `HomeController` memuat semua berita dengan `Berita::all()`, mengambil testimoni dengan `inRandomOrder()`, serta eager-load relasi seluruh donasi dan pembayaran pada koleksi campaign hero/list. | Berpotensi meningkatkan query, transfer data, dan memori saat transaksi bertambah. Gunakan select/aggregate settlement di query, batasi relasi yang dibutuhkan, dan batasi/paginate berita. |
| P2 | Dashboard admin | `AdminNotificationComposer` hanya menghitung pembayaran manual pending yang sudah memiliki `bukti_transfer`. | Pending transfer tanpa bukti (donatur belum merespons) tidak muncul dalam hitungan notifikasi follow-up. Pertimbangkan pemisahan antrean “menunggu donatur” dan “menunggu verifikasi admin”. |
| P2 | Bukti transfer lama | Endpoint bukti transfer mengizinkan fallback ke disk `public` untuk file legacy. | Perlu inventarisasi file lama dan memastikan tidak ada URL publik langsung yang masih dapat diakses tanpa otorisasi. Migrasikan file legacy ke private storage dan uji akses langsung. |
| P2 | UX admin donasi | Sebelum perubahan branch ini, UI menampilkan Edit dan Hapus. View Edit/Create yang dipanggil controller tidak ada di tree repository, sedangkan Hapus menghapus record donasi beserta relasi pembayaran. | Link yang rusak dan penghapusan transaksi finansial berisiko. Branch ini menghapus route/UI yang tidak tersedia dan menghindari hard-delete melalui panel. |
| P3 | Copywriting / footer | Footer memiliki tautan YouTube `href="#"`; tombol WhatsApp footer membuka tab baru tanpa `rel="noopener noreferrer"`. Daftar donasi admin menampilkan tombol WA di kolom nomor dan kolom aksi sekaligus. | Perbaiki tautan sosial yang mati, hardening link tab baru, dan kurangi aksi duplikat agar tabel lebih ringkas. |
| P3 | Riwayat donor | Field organizer pada data riwayat saat ini memakai fallback literal “Orang Baik”, bukan nama penggalang dana campaign. | Tampilkan nama penggalang yang benar agar donatur mengenali penerima manfaat/pengelola campaign. |

## Pemeriksaan per peran

- **Guest:** form donasi, instruksi transfer, token guest, upload/lihat bukti, signed receipt sudah memiliki beberapa guard/test historis. Perlu browser QA alur utuh dari campaign sampai settlement/expired.
- **Donatur login:** riwayat, resume, e-kwitansi, anonim, dan sapaan tersedia. Temuan total nominal dan label expired perlu diperbaiki.
- **Fundraiser peserta campaign:** dashboard menghitung transaksi settlement dan nominal dari relasi donasi referral. Perlu QA browser untuk gabung, alias, QR, berhenti, dan atribusi settlement.
- **Penggalang Dana:** dashboard dan policy ownership tersedia; perlu uji end-to-end pendaftaran, revisi dokumen, persetujuan, perubahan profil, campaign, serta privasi dokumen di staging.
- **Admin:** modul donasi, approval, berita, campaign, legalitas, payment gateway tersedia. Kelola donasi punya ketidaksesuaian route/view yang sedang ditutup di branch ini; generic gateway dan notifikasi pending perlu audit lanjutan.

## QA dan release gate

| Pemeriksaan | Status |
|---|---|
| Feature suite historis pada dokumentasi QA 19 Nov | Tercatat 126 passed / 562 assertions; bukan hasil run branch ini |
| Test baru manual expiration | Ditambahkan pada branch; belum dieksekusi |
| PHP lint / route:list / migration | Belum dieksekusi pada branch ini |
| Build aset production | Belum dieksekusi pada branch ini |
| Browser visual desktop/mobile | Belum diuji |
| Gateway Midtrans/Flip live | Belum diuji |
| Generic gateway callback | Belum aman/terverifikasi; jangan diaktifkan |
| Migration pada database staging | Belum diuji; perlu backup dan verifikasi versi DB |
| Backup/rollback dan staging smoke test | Belum dilakukan |

## Rekomendasi urutan penyelesaian

1. Selesaikan dan uji aksi expired transfer manual, termasuk migrasi additive.
2. Hardening rich text dan generic webhook sebelum menambah fitur gateway/konten.
3. Perbaiki angka/status riwayat donor dan konsistensi settlement.
4. Perbaiki pencarian mobile, pagination, dan performa query beranda.
5. Jalankan browser QA berdasarkan role dan viewport; audit aksesibilitas, error/empty/loading states, serta copywriting.
6. Lakukan backup, migration staging terkontrol, smoke test, dan rencana rollback sebelum rilis.
