# Security Architecture & Policies — OrangBaik.id

## 1. Klasifikasi Data & Dokumen (Data Classification)

| Klasifikasi | Cakupan Data | Storage Disk | Akses Publik | Akses Terotorisasi |
|-------------|--------------|--------------|--------------|-------------------|
| **P0: PRIVATE / SENSITIVE** | KTP Penggalang Individu, NIK, NPWP pribadi, nomor rekening/bank transfer donatur | `private` (`storage/app/private/`) | **DILARANG KERAS** | Owner profil / donatur & Admin via `dokumen.show` / `donasi.bayar.bukti_transfer` |
| **P1: INTERNAL / RESTRICTED** | Catatan verifikasi internal, data transaksi belum lunas, audit logs | Database & Log file | **DILARANG** | Admin & System Internal |
| **P2: PUBLIC / TRANSPARENT** | Dokumen Legalitas Organisasi Terverifikasi (SK Kemenkumham, Akta Notaris), Campaign, Kabar Terbaru (Berita) | `public` / URL resmi | Diizinkan jika `is_public = true` & organisasi terverifikasi | Publik, Donatur, Tamu |

---

## 2. Authorization Matrix (Matriks Otorisasi)

| Entitas / Aksi | Tamu (Guest) | User Biasa | Penggalang Dana | Admin |
|----------------|--------------|------------|-----------------|-------|
| Lihat Campaign Publik & Berita | ALLOW | ALLOW | ALLOW | ALLOW |
| Donasi (Midtrans / Flip / Manual) | ALLOW (Guest Token) | ALLOW | ALLOW | ALLOW |
| Lihat Profil Penggalang Publik | ALLOW (Non-KTP only) | ALLOW (Non-KTP only) | ALLOW (Non-KTP only) | ALLOW (Full Docs) |
| Unduh/Lihat Dokumen Privat KTP | DENY (403) | DENY (403) | ALLOW (Milik Sendiri) | ALLOW |
| Buat / Edit Campaign | DENY (Redirect Auth) | DENY (Harus Terverifikasi) | ALLOW (Milik Sendiri) | ALLOW |
| Update Kabar Terbaru Campaign | DENY | DENY | ALLOW (Milik Sendiri) | ALLOW |
| Tambah / Edit Berita | DENY | DENY | ALLOW (Milik Sendiri) | ALLOW (Semua) |
| Verifikasi / Approval Admin | DENY (403) | DENY (403) | DENY (403) | ALLOW |
| Akses Dashboard Admin (`/admin/*`)| DENY (403) | DENY (403) | DENY (403) | ALLOW |

---

## 3. Webhook Security & Payment Integrity

### A. Flip Webhook
- **Fail-Closed**: Jika `FLIP_WEBHOOK_TOKEN` tidak diset di `.env`, seluruh webhook otomatis ditolak (`400/false`).
- **Verifikasi Token**: Menggunakan fungsi tahan timing-attack `hash_equals($expectedToken, $receivedToken)`.
- **Integrity Check**:
  - `settlement` memeriksa kesesuaian nominal callback dengan nominal donasi di database (`$expectedNominal === $callbackAmount`).
  - Idempotency lock: status `settlement` tidak dapat di-rollback kembali ke `pending`, `failed`, atau `expired`.
- **Sanitized Logging**: Nilai token rahasia dilarang dicatat ke log sistem.

### B. Midtrans Webhook
- **Fail-Closed**: Jika `MIDTRANS_SERVER_KEY` belum terpasang, webhook ditolak seketika.
- **Signature Verification**:
  ```php
  $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
  hash_equals($expectedSignature, $signatureKey);
  ```
- **Amount Validation**: `gross_amount` dari callback divalidasi presisi terhadap `donasi.nominal`.
- **Anti-Rollback**: Transaksi berstatus `settlement` tidak dapat diturunkan statusnya oleh callback delayed/duplikat.

---

## 4. Document & Upload Security

1. **Storage Terisolasi**:
   - Bukti transfer manual dan KTP disimpan pada private storage (`Storage::disk('private')`), terletak di luar jangkauan direktori publik `/storage/`.
2. **Anti-Path Traversal**:
   - Endpoint pengunduhan memvalidasi `realpath()` memastikan file target tidak melompat keluar dari base directory penyimpanan.
3. **MIME & Extension Enforcement**:
   - Unggahan gambar dibatasi pada `image/jpeg`, `image/png`, `image/webp` dengan batas ukuran maksimum ketat.
   - Penamaan file menggunakan `Str::uuid()` acak.
