# Staging Security Audit Runbook

This runbook is for the staging operator after the audit branch is deployed to a dedicated staging environment. It is intentionally read-only until inventories, checksums, backups, and a rollback plan have been reviewed.

**Never run these checks against production unless a separately approved change window exists. Do not delete, move, rename, or rewrite any file during inventory.**

## 1. Confirm target and runtime

From the deployed application directory:

```bash
git status --short --branch
git rev-parse HEAD
php artisan about --only=environment
```

Inspect only non-secret runtime values. Do not print `.env`, database passwords, gateway credentials, application keys, or session secrets.

```bash
php artisan tinker --execute="dump([
    'app_env' => config('app.env'),
    'app_debug' => config('app.debug'),
    'app_url' => config('app.url'),
    'session_secure_cookie' => config('session.secure'),
    'filesystems_default' => config('filesystems.default'),
    'private_root' => config('filesystems.disks.private.root'),
    'public_root' => config('filesystems.disks.public.root'),
]);"
```

Expected staging safeguards:
- `app_env` is the intended staging environment.
- `app_debug` is `false`.
- `app_url` uses the staging HTTPS origin.
- Secure session cookies are enabled only after confirming TLS termination and trusted-proxy configuration.
- The `private` disk resolves to a non-public storage directory. The configured `public` disk maps to `storage/app/public` and is exposed through `public/storage`.

Do not copy production credentials into staging.

## 2. Read-only database inventory

Run against the staging database using a read-only account where possible. Save results only in a restricted location; document paths and identifiers can be sensitive.

### Verification documents

```sql
SELECT
    COALESCE(storage_disk, '(null)') AS storage_disk,
    COALESCE(CAST(is_public AS CHAR), '(null)') AS legacy_is_public,
    COUNT(*) AS records,
    SUM(file_dokumen REGEXP '^https?://') AS external_url_records
FROM penggalang_dana_dokumen
GROUP BY storage_disk, is_public
ORDER BY storage_disk, is_public;

SELECT
    id,
    penggalang_dana_id,
    storage_disk,
    is_public,
    file_dokumen
FROM penggalang_dana_dokumen
WHERE storage_disk = 'public'
   OR storage_disk IS NULL
   OR file_dokumen REGEXP '^https?://'
ORDER BY id;
```

### Transfer proofs

```sql
SELECT
    COUNT(*) AS total_payments,
    SUM(bukti_transfer IS NOT NULL AND bukti_transfer <> '') AS payments_with_proof
FROM pembayaran;

SELECT id, order_id, bukti_transfer
FROM pembayaran
WHERE bukti_transfer IS NOT NULL
  AND bukti_transfer <> ''
ORDER BY id;
```

### Phone-number collision preflight

The `penggalang_dana.no_telepon` column has a unique index. The current model normalizes Indonesian numbers to a `62...` representation. Do not change storage normalization until this read-only query confirms there are no collisions under the proposed local `08...` representation.

Requires MySQL 8+ for `REGEXP_REPLACE`:

```sql
WITH cleaned AS (
    SELECT
        id,
        no_telepon,
        REGEXP_REPLACE(no_telepon, '[^0-9]', '') AS digits
    FROM penggalang_dana
    WHERE no_telepon IS NOT NULL
      AND no_telepon <> ''
),
normalized AS (
    SELECT
        id,
        no_telepon,
        CASE
            WHEN digits LIKE '62%' THEN CONCAT('0', SUBSTRING(digits, 3))
            WHEN digits LIKE '8%' THEN CONCAT('0', digits)
            ELSE digits
        END AS proposed_local_number
    FROM cleaned
)
SELECT
    proposed_local_number,
    COUNT(*) AS records,
    GROUP_CONCAT(id ORDER BY id) AS fundraiser_ids
FROM normalized
GROUP BY proposed_local_number
HAVING COUNT(*) > 1;
```

Any returned row is a migration blocker. Resolve duplicates with the data owner; never pick a record to overwrite automatically.

## 3. Read-only physical storage inventory

The repository config maps:
- private files to `storage/app/private`;
- public files to `storage/app/public`;
- the public storage symlink to `public/storage`.

From the application root:

```bash
printf '%s\\n' '=== storage symlink ==='
readlink -f public/storage || true

printf '%s\\n' '=== public document and transfer files (checksums) ==='
find storage/app/public -type f \\
  \\( -path '*/dokumen_penggalang/*' -o -path '*/bukti-transfer/*' \\) \\
  -print0 2>/dev/null | xargs -0 -r sha256sum

printf '%s\\n' '=== private document and transfer files (checksums) ==='
find storage/app/private -type f \\
  \\( -path '*/dokumen_penggalang/*' -o -path '*/bukti-transfer/*' \\) \\
  -print0 2>/dev/null | xargs -0 -r sha256sum
```

Compare database-referenced paths with physical paths in both disks. Record:
- database row ID and storage disk;
- whether the referenced file exists;
- file size and SHA-256;
- whether more than one database row references the same path;
- unreferenced files that may require separate retention review.

Do not upload this inventory to a public issue or paste document paths into an unrestricted chat.

## 4. Backup and migration gates

Before any file or phone-number migration:
1. Take a verified staging database backup and storage backup.
2. Confirm backup integrity and restore it into an isolated test target.
3. Reconcile every DB reference with a physical file and checksum.
4. Prepare an idempotent migration plan with an explicit rollback mapping.
5. Test private-file streaming and direct public-URL denial on staging.
6. Verify admin authorization, owner denial, guest denial, and no-store response headers.
7. Re-run all checksums and database counts after migration.
8. Retain original public files until reconciliation and rollback windows are complete.

**No migration or cleanup command is provided here intentionally.** The correct operation depends on the actual staging inventory and must not be guessed.

## 5. Staging smoke-test checklist

- [ ] Homepage and campaign listing load over HTTPS.
- [ ] Registration, login, logout, and email verification work.
- [ ] Individual fundraiser submission requires a private KTP upload.
- [ ] Organization fundraiser submission requires private legal-document uploads.
- [ ] Owner profile and edit pages do not show document names, paths, or file links.
- [ ] Guest, owner, and ordinary user requests to the verification-document proxy return 403.
- [ ] Authorized admin can view the document through the protected proxy.
- [ ] Direct URLs to legacy public files are denied after a separately verified migration.
- [ ] Pending campaign cannot be opened directly or joined through any referral alias.
- [ ] Donation checkout and payment instructions work.
- [ ] Midtrans and Flip sandbox callbacks validate signatures/tokens, provider identifiers, exact whole-rupiah amounts, duplicate callbacks, and terminal-state behavior.
- [ ] Manual transfer proof upload and admin approval work; proof files are private and non-cacheable.
- [ ] Admin credential reveal is rate-limited, authorized, and no-store.
- [ ] Desktop and mobile layouts work on current supported browsers.
- [ ] Backup restore and rollback rehearsal completes successfully.

A green CI run is not a substitute for this staging checklist or a provider sandbox test.
