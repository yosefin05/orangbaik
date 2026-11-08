# Production Deployment & Readiness Checklist — OrangBaik.id

## 1. Persyaratan Lingkungan (Environment Requirements)

- **PHP**: 8.2 atau 8.3
- **Extensions**: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`, `gd` / `imagick`
- **Database**: MySQL 8.0+ atau MariaDB 10.5+
- **Web Server**: Nginx atau Apache dengan mod_rewrite aktif
- **SSL/TLS**: Wajib HTTPS aktif untuk callback Webhook (Midtrans & Flip menolak HTTP pada production)

---

## 2. Environment Variables Checklist (.env)

Pastikan variabel berikut terpasang di server production:

### Aplikasi & Database
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://orangbaik.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orangbaik_prod
DB_USERNAME=orangbaik_user
DB_PASSWORD=YOUR_STRONG_PASSWORD
```

### Payment Gateways (Midtrans & Flip)
```env
# MIDTRANS PRODUCTION
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# FLIP PRODUCTION
FLIP_API_KEY=YOUR_PRODUCTION_FLIP_API_KEY
FLIP_IS_PRODUCTION=true
FLIP_WEBHOOK_TOKEN=YOUR_STRONG_RANDOM_WEBHOOK_TOKEN
```

---

## 3. Langkah Deployment

```bash
# 1. Update source code
git pull origin main

# 2. Install dependencies (tanpa dev tools)
composer install --no-dev --optimize-autoloader

# 3. Optimasi Classmap Linux Case-Sensitivity
composer dump-autoload -o

# 4. Jalankan migration additive
php artisan migrate --force

# 5. Link storage publik
php artisan storage:link

# 6. Cache config & routing untuk performa
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart queue worker jika menggunakan background queue
php artisan queue:restart
```

---

## 4. Webhook URL Configuration di Dashboard Gateway

1. **Midtrans Dashboard**:
   - Notification URL: `https://orangbaik.id/payment/midtrans/webhook`
2. **Flip Dashboard**:
   - URL Webhook: `https://orangbaik.id/payment/flip/webhook`
   - Verification Token: Samakan dengan `FLIP_WEBHOOK_TOKEN` di file `.env`.
