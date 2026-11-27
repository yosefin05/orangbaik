# Codebase Engineering Audit — 27 November 2026

## Scope and evidence

Baseline: `main` at `18112a89b2488af8ab3f554b303b3fff5985a39a`.

Repository inventory: 579 Git tree entries. The inventory includes Laravel application code, five route files, Blade views/components/layouts, migrations, feature/unit tests, Composer/npm manifests, CI workflows, and architecture/security/deployment documentation.

This document records findings supported by source inspection and the existing audit records. It is not a claim that every runtime, browser, gateway, or production check has passed.

## Deployment boundary

- `.github/workflows/main.yml` is named **Deploy OrangBaik Staging** and triggers on pushes to `main`.
- Its target is `/www/wwwroot/staging.orangbaik.id/orangbaik`; it checks out the main revision, installs production dependencies, swaps built assets, clears runtime caches, checks `/up` and the staging homepage, and has a rollback trap.
- `.github/workflows/tests.yml` runs on pull requests to `main` and pushes to branches. It checks strict optimized PSR-4 autoloading, builds frontend assets, audits npm/Composer dependencies, and runs the Laravel test suite with warnings treated as failures.
- No production deployment workflow was identified in the inspected workflow files. Production remains out of scope for this audit.

## Confirmed CI evidence at baseline

| Workflow | Run | Result |
|---|---:|---|
| Laravel Tests on `main` | 45995363760 | Success |
| Deploy OrangBaik Staging on `main` | 45995364543 | Success |
| Laravel Tests on audit branch | 45996741059 | Success |

A successful workflow is evidence for the checks actually executed by that workflow; it is not proof of live payment gateway transactions, visual/browser accessibility, backup restoration, or production readiness.

## Findings requiring remediation or explicit sign-off

### P1 — Sensitive payment credential page exposure addressed in this audit branch

The baseline `PaymentGatewayController::show()` passed decrypted credential values to the Blade view, where password inputs only masked the text visually. This audit branch now passes presence flags only. Saved credentials are requested through a separate admin-only POST endpoint protected by the existing `auth` + `admin` route group, CSRF, a six-requests-per-minute throttle, an explicit credential-key allowlist, no-store response headers, and an audit log entry that excludes the secret itself. The page's reveal control calls this endpoint only after an administrator explicitly requests the value.

**Verification status:** feature tests now assert that secrets are absent from initial HTML, successful explicit reveal returns no-store headers, unknown keys are rejected, and non-admin users are denied. The latest full CI run for the test changes is in progress; do not mark this finding closed until that run passes.

### P2 — Rich-text sanitizer needs focused regression coverage

The current `App\\Support\\RichText` implementation uses `DOMDocument`, explicit allowed-tag and allowed-attribute lists, drops active-content elements, and restricts link/image URL schemes. This is materially stronger than the older 23 November audit description, which is now stale.

**Recommendation:** retain the current allowlist boundary while adding/confirming regression tests for entity-encoded and malformed schemes, protocol-relative URLs, event-handler attributes, SVG/MathML payloads, nested malformed markup, and image source handling. Consider a maintained sanitizer package only after compatibility and behavior are compared; do not replace working sanitization blindly.

### P1 — Generic payment webhook must remain fail-closed

The current `routes/web.php` exposes `POST /payment/{gateway}/webhook` plus dedicated Midtrans and Flip callback endpoints. The generic controller resolves a gateway-specific driver and returns a failure for unknown or rejected callbacks. No universal `/payment/webhook` endpoint is present in the current route file.

**Recommendation:** do not activate generic/custom gateways until each provider-specific adapter has mandatory authentication, exact amount validation, idempotency, allowed state transitions, replay handling, and sanitized logging covered by tests. Keep dedicated Midtrans/Flip callbacks canonical until generic adapter support is independently proven.

### P1 — Legacy public-disk payment proofs need inventory before fallback removal

The audit review records a legacy public-storage fallback in the donor payment-proof flow. Removing it without inventory could make historical records inaccessible; leaving it indefinitely can expose old files by direct URL.

**Recommendation:** inventory database references and physical files, migrate verified legacy files to private storage with checksums and record-level verification, update references transactionally, verify authorized streaming and direct-URL denial, then remove the fallback in a separate release. Never delete originals until backup and reconciliation are confirmed.

### P1 — Document access policy and implementation documentation disagree

The current `Penggalang_Dana_Dokumen::canBeAccessedBy()` permits administrators only. Owner-facing profile flows no longer load document records; the individual edit flow selects only the record identifiers needed for replacement uploads and renders a generic label, not the document name or path. This matches the requirement that identity/legal verification documents and their metadata are not exposed to users.

**Status:** controller, owner-facing profile/edit rendering, and API/security documentation now consistently restrict document names, paths, and files to admin-only contexts. Owners see verification status only. The database `is_public` field remains for legacy compatibility; application authorization does not treat it as permission.


### P1 — Midtrans callback persistence was hardened in this audit branch

The baseline `MidtransService::handleWebhook()` merged the full provider callback payload into `pembayaran.gateway_response`, which could persist unnecessary donor/provider fields and the signature. This audit branch now stores a minimal `last_midtrans_callback` allowlist (status, fraud status, payment type, transaction ID, gross amount, receive time) and preserves existing internal gateway metadata. The feature test was extended to ensure arbitrary customer/card-token fields and the signature are not persisted.

**Verification status:** change committed to the audit branch; the full Laravel workflow for the latest commit is running. Do not treat this finding as closed until that exact run passes.

### P1 — Generic API driver disabled until provider security contract exists

The previous `GenericApiDriver` accepted arbitrary callback status values and could mark a transaction settled without provider authentication or amount verification. It was not registered as a supported built-in provider, but the class itself remained unsafe if reused.

This audit branch changes the placeholder to fail closed: `isConfigured()` returns false, transaction creation throws a clear `LogicException`, and webhook handling always rejects. A feature test covers the disabled state and forged settlement callback.

**Verification status:** change committed to the audit branch; exact latest-commit CI must pass before merge. Re-enable only by implementing a concrete provider adapter with signature/token verification, amount integrity, idempotency, and status-transition tests.

### P2 — Webhook routes have legacy aliases and multiple orchestration paths

`routes/web.php` exposes dedicated Midtrans/Flip endpoints, a dynamic provider endpoint, and a legacy Midtrans alias. `routes/api.php` also exposes a Midtrans notification endpoint. Historical audit records identify multiple controllers/orchestration paths.

**Recommendation:** map each endpoint to its middleware, controller, driver, signature/token check, response contract, and provider dashboard configuration. Select one canonical endpoint per provider; retain aliases only when needed for backward compatibility and test each alias explicitly.

### P1 — External document URLs cannot guarantee private verification storage

New fundraiser submissions and replacements now require direct uploads to private storage; new external document URLs are rejected. Unchanged URL-backed legacy records are preserved for compatibility and remain admin-only at the application endpoint, but OrangBaik cannot control the external host's direct-access policy.

**Status:** upload-only is implemented for new submissions and replacements. Keep existing URL-backed records in a separate migration/review queue; do not auto-fetch arbitrary URLs because that introduces SSRF risk. Preserve existing records until their source and replacement file have been verified.

### P2 — Indonesian phone storage format is inconsistent with the requested local format

`Penggalang_Dana::setNoTeleponAttribute()` and `PenggalangDanaController::normalizedContactInput()` currently canonicalize numbers to international digits beginning with `62`, while the agreed storage convention is local digits beginning with `08`. Validation rules also expect `62` format. A direct setter change is unsafe while existing records and the unique index may contain both equivalent `08...` and `62...` values.

**Recommendation:** inventory current values and unique-index behavior first; design an additive normalization migration that detects collisions before writing, then update create/update validation and tests together. Keep display formatting separate from storage normalization.

### P1 — Pending campaigns were reachable through direct URLs

Campaign creation marks emergency and sustainable campaigns as pending while setting `is_active=true`. The public listing already filtered approval status, but the direct campaign detail query and fundraiser referral entry points did not consistently enforce that approval gate.

**Status:** direct campaign detail and sidebar queries now apply the same approved/regular/legacy-null rule as public listings. Fundraiser enrollment and referral activation also require an active, approved campaign. Regression tests cover direct URL access and enrollment against a pending campaign.

### P2 — File replacement must not delete the prior proof before commit

A private transfer-proof replacement previously deleted the old private file before storing the new file and updating the database pointer. Filesystem operations do not roll back with the SQL transaction.

**Status:** the old private proof is now deleted only after the replacement file and database pointer have committed. Legacy public-disk proofs remain preserved. Regression coverage verifies successful private replacement and legacy public-file preservation.

### P2 — Raw webhook and exception details in logs

Webhook controllers and several application error paths previously logged raw exception messages or stack traces.

**Status:** webhook controller logs and the audited donation/campaign/QR paths now record exception classes and minimal identifiers rather than raw exception messages or stack traces. Domain validation errors remain user-facing only where they are fixed, controlled messages.

### P2 — Naming conventions are mixed in legacy domain types and routes

Examples include `Penggalang_Dana_Dokumen`, underscore-separated route names, kebab-case URLs, inconsistent route parameter names, and legacy model names. Some are public contracts or Eloquent table mappings.

**Recommendation:** do not mass-rename models, route names, or parameters in a cleanup-only release. Establish conventions for new code, then migrate legacy identifiers through compatibility aliases, explicit route/model binding, deprecation notes, and tests.

### P2 — Blade organization requires a usage-driven classification

The repository has dedicated `resources/views/components` and `resources/views/layouts`, plus page- and admin-scoped view trees. Do not move files solely to make directory names look uniform.

Classification rule:
- **Layouts:** document shells and shared page chrome with named sections/stacks.
- **Components:** reusable UI units with a stable input contract and behavior (for example campaign card, form controls, modal).
- **Partials:** page- or feature-scoped fragments without a meaningful independent public interface.
- **Pages:** route-level composition and page-specific content.
- **Admin views:** remain grouped by admin feature; extract shared UI only when reuse is demonstrated.

Before moving a Blade file, search all `view()`, `@include`, `<x-...>`, component aliases, dynamic view names, and tests. Preserve view names or add compatibility aliases during transitions.

### P2 — Composer dependency constraint is too broad

`composer.json` currently requires `simplesoftwareio/simple-qrcode` with `*`. The lock file pins a resolved version, but the manifest permits an unbounded future update.

**Recommendation:** inspect the locked package version, PHP/Laravel compatibility, transitive constraints, and security advisories, then constrain to a compatible maintained range and run Composer update only for that package in an isolated branch. Do not guess a version constraint.

### P2 — Historical docs contain conflicting or stale claims

The security/API docs have been aligned with admin-only document names, metadata, and file access. Older historical audit records may still describe owner access or public organization documents and must be treated as superseded evidence. The deployment guide describes production steps, but the inspected automated deployment workflow targets staging only. The release verification dated 9 November records blockers that must be rechecked against current code and current CI rather than copied as present-day facts.

**Recommendation:** date and label historical evidence, distinguish implemented behavior from intended behavior, and link every release claim to a current test, workflow, or operational check. Do not copy old `VERIFIED` labels into a current release decision without rerunning the corresponding check.

## Blade and frontend inventory policy

The current tree includes:
- Shared components in `resources/views/components/`.
- A dedicated admin layout in `resources/views/layouts/admin.blade.php`.
- Public route-level composition in `resources/views/pages/`.
- Feature-scoped admin views in `resources/views/admin/<feature>/`.
- Profile-specific partials under `resources/views/profile/partials/`.

The repository tree alone does not prove that every view is correctly classified. A complete classification requires reference search and rendering tests. No blind mass move or rename is recommended.

## Data and migration safety rules

1. Do not rewrite historical migrations that may already have run in staging or production.
2. Use additive, reversible migrations for schema changes; verify them against the actual target DB engine/version before deployment.
3. Do not delete or overwrite donation/payment records, gateway configuration, user accounts, verification metadata, or uploaded documents during refactoring.
4. For file migrations, use copy → checksum → update reference → verify access → retain backup; cleanup is a separate approved step.
5. Never print or request full `.env`, API keys, webhook tokens, private documents, or database credentials in issue logs or chat.

## Required verification gates before release

- [x] Full Laravel test suite on audit branch commit `15943e8` (run 45998143618): success, including strict PSR-4, frontend build, dependency advisory audit, and complete Laravel suite. Re-run after any further code changes.
- [ ] Strict optimized Composer PSR-4 autoload check.
- [ ] Composer and npm dependency audits reviewed; no untriaged high/critical advisories.
- [ ] Frontend production build and manifest verification.
- [ ] PHP syntax check and route registration/cache check.
- [ ] Database migration dry-run/rehearsal against a disposable DB matching target engine/version.
- [ ] Authorization tests for guest, ordinary user, fundraiser, document owner, and admin.
- [ ] Payment tests for exact amount, invalid/missing secret, replay, duplicate callback, terminal-state rollback, and idempotency.
- [ ] Private document and historical proof direct-URL denial tests.
- [ ] Browser QA at desktop/mobile sizes for donation, payment instructions, receipt, profile, fundraiser dashboard, and admin workflows.
- [ ] Staging backup/rollback rehearsal and smoke tests for homepage, `/up`, authentication, donation, admin, and document access.
- [ ] Explicit production approval and separate production backup/change window. Production is not covered by staging CI.

## Current decision

**Codebase cleanup: IN PROGRESS. Latest audit branch CI: PASSED on `15943e8`. Release decision: NOT YET APPROVED.**

The baseline main test and staging deployment workflows succeeded, but the P1 findings and unverified operational gates above remain. This audit branch is a review boundary; merge only after each change is implemented, tested, and reviewed. No production change is authorized by this document.
