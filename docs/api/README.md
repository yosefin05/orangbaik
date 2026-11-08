# API & Webhook Specifications — OrangBaik.id

## 1. Webhook Endpoints (Payment Gateways)

### A. Midtrans Webhook
- **URL**: `POST /payment/midtrans/webhook` (Alias: `POST /payment/webhook` with gateway `midtrans`)
- **Headers**:
  - `Content-Type: application/json`
- **Security Check**:
  - `signature_key = hash('sha512', order_id + status_code + gross_amount + server_key)`
- **Response**:
  - `200 OK`: `{"success": true, "message": "Notification processed"}`
  - `400 Bad Request`: Signature tidak valid atau data tidak lengkap.
  - `500 Server Error`: Kegagalan database internal.

### B. Flip Webhook
- **URL**: `POST /payment/flip/webhook`
- **Headers**:
  - `X-CALLBACK-TOKEN: <FLIP_WEBHOOK_TOKEN>`
  - `Content-Type: application/x-www-form-urlencoded` atau `application/json`
- **Payload**:
  - Berisi `data` json string atau objek berisi `id`, `bill_link_id`, `status` (`SUCCESSFUL`, `FAILED`, `EXPIRED`).
- **Response**:
  - `200 OK`: `{"success": true, "message": "Notification processed"}`
  - `400 Bad Request`: Token tidak valid atau nominal tidak sesuai (Amount Integrity Mismatch).

---

## 2. Public & User Endpoints

### A. Donasi
- `POST /campaign/{slug}/donasi`
  - Input: `nominal`, `payment_channel_id`, `nama_donatur`, `no_hp`, `is_anonim`, `pesan`, `ref`
  - Output: JSON transaksi pembayaran (`payment_token`, `snap_token` atau VA number)

### B. Dokumen Terproteksi
- `GET /dokumen-penggalang/{id}`
  - Otorisasi: Admin / Owner / Dokumen Publik Organisasi
  - Output: Binary stream dengan header `X-Content-Type-Options: nosniff`

### C. Bukti Transfer Manual
- `GET /donasi/instruksi/{pembayaran}/bukti-transfer?token={guest_token}`
  - Otorisasi: Admin / Donatur Akun Terdaftar / Guest dengan payment_token valid
  - Output: Binary file stream bukti transfer
