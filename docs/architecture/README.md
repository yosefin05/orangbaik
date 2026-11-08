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
- **`Penggalang_Dana_Dokumen`**: Entitas dokumen legalitas. Memiliki scope `publicOnly` dan otorisasi `canBeAccessedBy`.
- **`Donasi`**: Data niat donatur, nominal, anonimitas, dan relasi fundraiser.
- **`Pembayaran`**: Sumber kebenaran status transaksi (`settlement`, `pending`, `failed`, `expired`). Memiliki `payment_token` UUID untuk keamanan akses tamu.
