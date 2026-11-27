# API & Webhook Specifications — OrangBaik.id

## 1. Webhook Endpoints (Payment Gateways)

### A. Midtrans Webhook
- **Canonical URL**: `POST /payment/midtrans/webhook`
- **Legacy compatibility alias**: `POST /midtrans/notification`
- **Dynamic provider route**: `POST /payment/{gateway}/webhook` is reserved for configured drivers whose webhook authentication and amount-integrity behavior have been reviewed. Do not enable a custom gateway merely because the route exists.
- **Security Check**: Midtrans signature is calculated from `order_id + status_code + gross_amount + server_key` using SHA-512 and compared safely.
- **Important**: verify the exact controller/driver implementation and environment configuration before treating a callback as production-ready.

### B. Flip Webhook
- **Canonical URL**: `POST /payment/flip/webhook`
- **Header**: `X-CALLBACK-TOKEN` must match the configured callback token.
- **Payload**: provider callback data containing bill/payment identifiers and provider status.
- **Important**: callbacks must be authenticated, amount-checked against the stored donation amount, idempotent, and prevented from rolling back a settled payment.

## 2. Public & User Endpoints

### A. Donasi
- `POST /campaign/{slug}/donasi` is a legacy compatibility endpoint that redirects to the canonical donation flow.
- Canonical form submission: `POST /donasi/{slug}/bayar`.
- Input validation and response shape are defined by the active request/controller implementation.

### B. Dokumen Penggalang (STRICTLY PRIVATE)
- `GET /dokumen-penggalang/{id}`
- **Admin only**: only an authorized administrator may stream a verification document.
- **Guest, ordinary user, and fundraiser/owner**: denied (403). The owner may see verification metadata/status in the application, but cannot open or download the underlying KTP or legal/verification file.
- **Public access**: never allowed. Organization documents are also private; there is no public visibility toggle.
- Files must be stored and streamed from private storage. Legacy public-disk records require a controlled inventory and migration before removing any compatibility fallback.

### C. Bukti Transfer Manual
- `GET /donasi/instruksi/{pembayaran}/bukti-transfer?token={guest_token}`
- Access is restricted to the authorized admin, authenticated donor who owns the payment, or guest with a valid payment token. The implementation must verify payment ownership/token before streaming.

## 3. Contract maintenance
This document describes intended access boundaries. For exact response codes, route aliases, and provider callback behavior, verify `routes/web.php`, `routes/api.php`, the relevant controller, gateway driver, and current feature tests together. Do not infer live gateway readiness from route registration alone.
