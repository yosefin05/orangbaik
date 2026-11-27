# Security Architecture & Policies — OrangBaik.id

## 1. Klasifikasi Data & Dokumen

| Klasifikasi | Cakupan Data | Storage | Akses Publik | Akses Terotorisasi |
|---|---|---|---|---|
| **P0: PRIVATE / SENSITIVE** | KTP, NIK, NPWP pribadi, dokumen verifikasi individu/organisasi, bukti transfer, kredensial gateway | Private storage / encrypted database configuration | **DENY** | Admin berwenang sesuai kebutuhan operasional |
| **P1: INTERNAL / RESTRICTED** | Catatan verifikasi, data transaksi belum lunas, audit logs | Database & log yang dikontrol aksesnya | **DENY** | Admin & system internal sesuai least privilege |
| **P2: PUBLIC / APPROVED CONTENT** | Campaign yang disetujui/aktif, berita dan konten yang memang ditetapkan publik | Database / public assets | Diizinkan sesuai status dan policy konten | Publik, donatur, pengguna |

**Semua file dokumen verifikasi penggalang dana bersifat privat**, termasuk KTP individu, akta, SK, dan dokumen organisasi. Dokumen tidak boleh ditampilkan atau diunduh oleh pengunjung, donatur, maupun pemilik akun. Pemilik hanya dapat melihat status verifikasi. Nama dokumen, metadata penyimpanan, dan file identitas/legalitas tidak ditampilkan pada halaman pengguna; akses file hanya untuk admin yang berwenang. Tidak ada fitur untuk mengubah dokumen verifikasi menjadi publik.

Kolom database legacy seperti `is_public` tidak boleh dianggap sebagai izin akses. Pertahankan kompatibilitas skema sampai migrasi data dan dampak kode lama diaudit secara terpisah.

## 2. Authorization Matrix

| Entitas / Aksi | Guest | User biasa | Penggalang dana / Owner | Admin |
|---|---|---|---|---|
| Lihat campaign/berita yang dipublikasikan | ALLOW | ALLOW | ALLOW | ALLOW |
| Donasi melalui alur yang tersedia | ALLOW sesuai token | ALLOW | ALLOW | ALLOW |
| Lihat profil penggalang non-sensitif | ALLOW | ALLOW | ALLOW | ALLOW |
| Lihat metadata/status verifikasi milik sendiri | DENY | DENY | ALLOW | ALLOW |
| Buka/unduh file dokumen verifikasi | DENY (403) | DENY (403) | DENY (403) | ALLOW sesuai otorisasi |
| Ubah visibilitas dokumen verifikasi | DENY | DENY | DENY | DENY |
| Buat/edit campaign | DENY | Sesuai status verifikasi dan policy | Milik sendiri sesuai policy | ALLOW |
| Verifikasi/approval admin | DENY | DENY | DENY | ALLOW |

## 3. Webhook Security & Payment Integrity

### A. Flip Webhook
- Fail closed when the expected callback token is missing or invalid.
- Compare secret tokens using a timing-safe comparison.
- Verify callback amount against the stored donation amount before settlement.
- Enforce idempotency and disallow transitions that roll a settled payment back to a non-settled state.
- Never log callback tokens, API keys, or unredacted sensitive payloads.

### B. Midtrans Webhook
- Fail closed when the server key is not configured.
- Verify the provider signature using the documented SHA-512 construction and a timing-safe comparison.
- Validate callback gross amount against the stored donation amount before settlement.
- Enforce idempotency and prevent settled-payment rollback.

### C. Generic/custom gateway
Do not activate a generic gateway unless its adapter implements mandatory provider authentication/signature verification, exact amount validation, idempotency, replay handling, and allowed state transitions. A registered route alone is not evidence of secure webhook support.

## 4. Document & Upload Security

1. Verification files and manual-transfer proofs must use private storage and must not be served directly from a public storage URL.
2. File streaming endpoints must authorize the administrator before resolving or opening the file.
3. Validate resolved file paths against the configured storage root to prevent path traversal.
4. Validate file size, MIME type, and extension using server-side content inspection; generate non-guessable filenames.
5. Legacy files stored on a public disk must be inventoried and migrated with checksums and reference reconciliation before any old fallback is removed. Do not delete originals until backup and verification are complete.
6. Responses containing private files must use restrictive cache headers and `X-Content-Type-Options: nosniff`.

## 5. Secrets and Configuration

- Encrypt gateway credentials at rest.
- Do not serialize decrypted API keys or webhook tokens into the initial admin HTML.
- Credential reveal, if supported, must be a separate authenticated and authorized action with CSRF protection, rate limiting, no-store response headers, and audit logging.
- Never commit `.env`, production secrets, private documents, or database credentials.
