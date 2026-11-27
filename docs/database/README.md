# Database Architecture & Entity Relationships — OrangBaik.id

## 1. Schema Utama & Integritas Data

```
users (1) ─────────── (0..1) penggalang_dana (1) ─── (0..*) campaign (1)
                                      │                             │
                                      ▼                             ▼
                            penggalang_dana_dokumen (N)         donasi (N)
                                                                    │
                                                                    ▼
                                                             pembayaran (1)
```

## 2. Definisi Entitas & Aturan Bisnis

### A. Tabel `pembayaran`
- **`transaction_status`**: sumber status transaksi; status terminal dan aturan transisi harus mengikuti service pembayaran.
- **`payment_token`**: UUID acak untuk otorisasi guest donor tanpa mengekspos ID berurutan.
- **`bukti_transfer`**: path bukti transfer; file baru disimpan pada private storage.
- **`paid_at`**: timestamp ketika pembayaran dikonfirmasi settlement.

### B. Tabel `donasi`
- **`nominal`**: nilai donasi.
- **`is_anonim`**: boolean anonimitas donatur.
- **`fundraiser_id`**: foreign key ke fundraiser jika donasi berasal dari referral relawan.

### C. Tabel `campaign`
- **`penggalang_dana_id`**: FK ke profil penggalang pemilik campaign.
- **`target_donasi` & `minimal_donasi`**: batas nominal campaign.
- **`approval_status`**: status persetujuan sesuai jenis campaign dan policy.
- **`is_active`**: status campaign aktif.

### D. Tabel `penggalang_dana_dokumen`
- **`penggalang_dana_id`**: FK ke penggalang dana.
- **`nama_dokumen`**: metadata jenis dokumen (misalnya KTP, akta, atau SK).
- **`file_dokumen`**: path file atau referensi legacy; bukan URL publik yang boleh ditampilkan.
- **`is_public`**: kolom legacy/kompatibilitas. Nilainya tidak memberikan hak akses publik dan tidak boleh dipakai untuk menampilkan file.
- **`storage_disk`**: disk penyimpanan. Semua file verifikasi baru wajib menggunakan disk `private`.

**Invariant privasi:** semua dokumen verifikasi, baik individu maupun organisasi, hanya dapat dibuka/diunduh oleh admin yang berwenang. Pemilik hanya melihat metadata/status verifikasi. Guest dan pengguna biasa tidak boleh mengakses file. Jangan menghapus kolom atau mengubah data legacy sebelum seluruh pemakaian, migrasi, dan foreign-key dependency diaudit.
