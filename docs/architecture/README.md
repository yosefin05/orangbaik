# Architecture & Design System — OrangBaik.id

## 1. Ikhtisar Arsitektur

OrangBaik.id dibangun menggunakan **Laravel 12** dan PHP 8.2+ dengan pola arsitektur **Clean MVC + Action/Service Layer**:

```
[ HTTP Requests ]
       │
       ▼
[ Routes (web.php / admin.php / api.php) ]
       │
       ▼
[ Middleware & Policy (AdminMiddleware, Auth, Verified) ]
       │
       ▼
[ Controllers (User & Admin) ]
       │
       ▼
[ Services & Managers (PaymentGatewayManager, MidtransService, FlipService, ManualTransferService) ]
       │
       ▼
[ Eloquent Models & DB Transactions ]
       │
       ▼
[ MySQL Database & Isolated Storage (Public vs Private) ]
```

---

## 2. Layer & Tanggung Jawab

### A. Routing Layer
- **`routes/web.php`**: Route publik donatur, alur guest token, dashboard penggalang dana, dan manajemen berita penggalang dana.
- **`routes/admin.php`**: Terproteksi ketat dengan `['auth', 'admin']` middleware (`AdminMiddleware`), menangani approval campaign, verifikasi legalitas, manajemen payment gateway/channel, dan transaksi donasi.
- **Wildcard Safety**: Route wildcard `/{slug}` diposisikan pada baris paling akhir file untuk mencegah tabrakan dengan reserved route prefixes (`/campaign`, `/donasi`, `/berita`, `/admin`, dsb).

### B. Service & Payment Layer
- **`PaymentGatewayManager`**: Mengimplementasikan Driver Pattern untuk memfasilitasi modularitas gateway.
- **`MidtransService`**: Integrasi Snap Token, webhook signature verification, status mapping, dan fail-closed security.
- **`FlipService`**: Integrasi Virtual Account / Bill Payment, PWF callback handling, dan token verification.
- **`ManualTransferService`**: Alur verifikasi bukti transfer offline, penyimpanan bukti ke private disk, dan approval manual oleh admin.

### C. Data & Model Layer
- **`Campaign`**: Model pusat penggalangan dana. Menghitung total donasi terkumpul hanya dari relasi `pembayaran` dengan status `settlement`.
- **`Penggalang_Dana`**: Profil entitas penggalang (individu vs organisasi), status approval, verifikasi badge.
- **`Penggalang_Dana_Dokumen`**: Entitas dokumen verifikasi privat. File hanya dapat diakses admin berwenang; pemilik hanya melihat metadata/status. Kolom legacy `is_public` tidak memberikan akses publik.
- **`Donasi`**: Data niat donatur, nominal, anonimitas, dan relasi fundraiser.
- **`Pembayaran`**: Sumber kebenaran status transaksi (`settlement`, `pending`, `failed`, `expired`). Memiliki `payment_token` UUID untuk keamanan akses tamu.


---

## 3. Blade View Organization

Gunakan struktur yang sudah ada berdasarkan tanggung jawab, bukan memindahkan file hanya demi menyeragamkan nama folder.

| Jenis | Lokasi saat ini | Aturan |
|---|---|---|
| Layout | `resources/views/layouts/` | Kerangka halaman, navigation shell, named sections/stacks |
| Component | `resources/views/components/` | UI reusable dengan input/props dan perilaku yang jelas |
| Page | `resources/views/pages/` | Komposisi tingkat route dan konten halaman spesifik |
| Admin feature views | `resources/views/admin/<feature>/` | Halaman admin dikelompokkan per domain/fitur |
| Partial | `resources/views/profile/partials/` dan partial feature lain | Potongan tampilan yang terikat konteks halaman/fitur |

### Aturan klasifikasi

- **Component** jika dipakai lintas halaman atau memiliki kontrak input/behavior yang stabil. Contoh: campaign card, modal, input controls.
- **Partial** jika fragmen hanya dipakai oleh satu halaman/fitur dan tidak membutuhkan kontrak reusable tersendiri.
- **Layout** untuk shell halaman, bukan untuk konten satu halaman.
- **Page** tetap menjadi komposisi route-level; jangan menjadikan satu file page sebagai component.
- Jangan membuat component generik hanya untuk satu baris markup atau memecah view menjadi terlalu banyak file tanpa manfaat reuse.

### Prosedur sebelum rename/move

1. Cari semua pemanggilan `view()`, `@include`, Blade component `<x-...>`, component aliases, dynamic view names, dan referensi test.
2. Catat route/fitur yang merender view.
3. Pindahkan satu domain/fitur dalam satu perubahan kecil.
4. Pertahankan nama view lama melalui alias/compatibility wrapper bila ada caller eksternal atau dinamis.
5. Tambahkan rendering/feature tests sebelum menghapus file lama.
6. Jangan melakukan mass rename file, route name, atau parameter publik dalam refactor cleanup tanpa compatibility plan.

## 4. Security Boundaries

- Admin routes berada dalam middleware `auth` dan `admin`.
- Dokumen verifikasi individu maupun organisasi tidak pernah dipublikasikan. Endpoint file hanya untuk admin berwenang.
- Credential payment gateway tidak boleh disertakan pada HTML awal. Reveal harus melalui POST admin terproteksi, CSRF, throttling, no-store response, dan audit log tanpa nilai secret.
- Generic payment driver tetap disabled sampai ada adapter provider-specific dengan autentikasi callback dan amount validation yang teruji.
- Bukti transfer lama yang masih berada di public disk harus dimigrasikan melalui inventory dan verifikasi file; jangan menghapus fallback sebelum rekonsiliasi data selesai.

## 5. Related Engineering Audit

Lihat [Codebase Engineering Audit — 27 November 2026](CODEBASE_ENGINEERING_AUDIT_2026-11-27.md) untuk temuan, bukti CI, batas deployment, dan release gates.
