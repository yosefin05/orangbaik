# Development Standards & Guidelines — OrangBaik.id

## 1. Standar Penulisan & Konvensi Kode

- **PSR Compliance**: Mengikuti PSR-12 coding standard dan PSR-4 autoloading.
- **Naming Conventions**:
  - Controller: `PascalCase` diakhiri `Controller` (contoh: `PenggalangDanaBeritaController`).
  - Model: `PascalCase` tunggal (contoh: `Campaign`, `Pembayaran`). Model legacy dengan underscore (`Penggalang_Dana`) dipertahankan demi kompatibilitas relasi existing.
  - Method & Variable: `camelCase` deskriptif (contoh: `authorizedCampaign`, `processImages`).
  - Route Name: `dot.notation` bertingkat (contoh: `admin.penggalang_dana.index`, `campaign.update.store`).
  - Database: `snake_case` jamak/tunggal sesuai migration awal.

---

## 2. Security Best Practices untuk Developer Baru

1. **Selalu Validasi Ownership**:
   - Jangan pernah mempercayai ID dari parameter URL secara buta.
   - Periksa selalu `$model->user_id === Auth::id()` atau gunakan Policy / `$this->authorized...()`.
2. **Perlindungan Data Pribadi (KTP / Identitas)**:
   - Jangan pernah menampilkan atau memuat relasi dokumen identitas pada query/halaman publik.
   - Dokumen privat wajib menggunakan disk `'private'` dan diakses melalui controller dengan otorisasi.
3. **Pencatatan Log Sensitif**:
   - Dilarang mencatat seluruh `$request->headers->all()` atau token otentikasi/password/nomor kartu ke `Log::info()`.

---

## 3. Workflow Pengujian (Testing)

Jalankan testing lokal:
```bash
php artisan test
```
Database testing dikonfigurasi menggunakan SQLite in-memory (`:memory:`) pada `phpunit.xml` sehingga aman dan tidak merusak database MySQL lokal.
