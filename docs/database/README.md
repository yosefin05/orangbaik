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

---

## 2. Definisi Entitas & Aturan Bisnis

### A. Tabel `pembayaran`
- **`transaction_status`**: Sumber kebenaran status pembayaran (`pending`, `settlement`, `failed`, `expired`).
- **`payment_token`**: UUID acak untuk otorisasi guest donor tanpa mengekspos ID berurutan (mencegah IDOR).
- **`bukti_transfer`**: Path file bukti transfer fisik (tersimpan di `storage/app/private/bukti-transfer/`).
- **`paid_at`**: Timestamp resmi ketika status berubah menjadi `settlement`.

### B. Tabel `donasi`
- **`nominal`**: Nilai donasi yang disetorkan.
- **`is_anonim`**: Boolean anonimitas donatur (nama ditampilkan sebagai "Hamba Allah" jika bernilai `true`).
- **`fundraiser_id`**: Foreign key ke `campaign_fundraiser` jika donasi berasal dari referral relawan.

### C. Tabel `campaign`
- **`penggalang_dana_id`**: Relasi ke profil penggalang pemilik campaign.
- **`target_donasi` & `minimal_donasi`**: Batasan nominal.
- **`approval_status`**: Status persetujuan oleh admin (`pending`, `approved`, `rejected`) untuk tipe campaign `emergency` & `sustainable`.
- **`is_active`**: Boolean visibilitas campaign.

### D. Tabel `penggalang_dana_dokumen`
- **`penggalang_dana_id`**: FK ke `penggalang_dana`.
- **`nama_dokumen`**: Keterangan dokumen (e.g. "KTP", "Akta Pendirian").
- **`file_dokumen`**: Path storage atau URL dokumen.
- **`is_public`**: Boolean (default `false`). Dokumen individu selalu `false` (privat).
- **`storage_disk`**: Disk penyimpanan dokumen (`private` vs `public`).
