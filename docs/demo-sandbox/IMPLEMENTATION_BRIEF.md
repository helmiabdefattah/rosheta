# Mostashfa-on — Demo Sandbox: Implementation Brief for Claude Code

> **How to use this file:** copy it into the repo (suggested path: `docs/demo-sandbox/IMPLEMENTATION_BRIEF.md`) and tell Claude Code:
> *"Read docs/demo-sandbox/IMPLEMENTATION_BRIEF.md. Start with Phase 0 and report back before writing any code."*
> **v1.3** — reconciled with the Phase 0 Discovery report: see **Appendix B**, which overrides the body and Appendix A wherever they disagree. Demo data lives in a **separate database** selected by `.env`, so the production database is never written to (design doc §2.1). **Stack-specific guidance for this project (Laravel + MySQL on Laragon, `C:\laragon\sites\rosheta`) is in Appendix A at the end of this file — read it before Phase 1.**
>
> The Arabic design rationale lives in `01-demo-sandbox-design-AR.md`; this file is the executable plan. Names below (`clinics`, `visits`, …) are placeholders — map them to the real schema in Phase 0 and record the mapping in `docs/demo-sandbox/SCHEMA_MAP.md`.

## Goal

Let any visitor (from a paid ad) open a fully working, pre-populated clinic as **doctor** or **assistant** with **no signup**, try every feature, and have **all data hard-deleted** when the demo ends. Dates in the seeded data are relative to the moment the demo starts (Africa/Cairo).

## Non-negotiable rules

1. **Isolation at the database level (v1.1 — supersedes the shared-DB approach):** all demo data lives in a **separate database** selected by `DEMO_DB_CONNECTION` in `.env`. The production database is never written to from a demo request — enforced at runtime by `DEMO_PROD_WRITE_GUARD` (§1.2b). Within that demo database, each visitor still gets their own tenant (`clinics.is_demo = true`). The only production writes allowed from the demo path are `demo_sessions` and `demo_leads` (marketing records that must outlive the demo); production reads are limited to reference tables.
2. **No external side effects:** no real SMS/WhatsApp/Email/Push, no real payment, no real pharmacy/lab orders, no real video sessions, no third-party integrations. Simulate instead of disabling UI.
3. **Hard delete on end** (even if the app normally soft-deletes), including uploaded files and cache keys.
4. **No scattered `if (isDemo)` inside business logic.** One `DemoContext` + one `DemoGuard` layer + provider decorators.
5. **Data-driven scenario templates** (JSON), not hard-coded seed code.
6. Do not change the behaviour of the production (non-demo) code paths. All new code must be covered by tests listed in each phase.

---

## Phase 0 — Discovery (read-only; produce a report, no code changes)

Deliver `docs/demo-sandbox/DISCOVERY.md` answering:

1. **Stack & conventions:** framework, ORM, migrations tool, job scheduler/queue, auth mechanism (session vs JWT, refresh tokens, how tokens are revoked), cache (Redis?), file storage (local/S3), timezone handling (where is `Africa/Cairo` applied?).
2. **Tenancy model:** list **every table** and whether it has `clinic_id` (or the equivalent tenant key), a path to it (e.g. `prescriptions → visits → clinics`), or **none** (global). Flag any table where demo data could leak into global space (e.g. `patients` shared across clinics, `notifications` keyed by user only, `audit_logs`, search indexes, analytics tables).
3. **Users & roles:** how doctors and assistants are modelled, permission system, how a user is attached to a clinic, whether one user can belong to multiple clinics.
4. **External side-effect points:** every call site for SMS, WhatsApp, email, push, payment gateway, pharmacy/lab partner APIs, video provider, calendar/social integrations, webhooks. Note whether they are already behind interfaces/services.
5. **Existing seeders/factories/fixtures** reusable for demo data.
6. **Public/aggregate queries** that must exclude demo tenants (doctor search, listing pages, sitemap, admin dashboards, reporting, exports).
7. **Deletion:** does cascading delete exist at DB or ORM level? Any FK without cascade? Any table referencing users/clinics without FK?
8. **Multi-connection readiness (added in v1.1):** does the framework/ORM support a second database connection cleanly? Specifically: can a model/repository be bound to a non-default connection at runtime for the whole request? Are there raw SQL queries, stored procedures, cross-database JOINs, or hard-coded connection names that would break when demo requests run on a second connection? List every one.
9. **Reference tables:** which tables are global/non-tenant reference data the app cannot function without (medications, ICD codes, lab tests, specialties, governorates/cities, subscription plans)? Their sizes and update frequency — these must be mirrored into the demo database.
10. **Risks & recommendation:** the minimum set of schema changes for Phase 1, whether isolation mode `tenant` / `schema` / `database` fits this stack best (see design doc §2.1), and anything in the current model that makes per-visitor tenants hard.

Stop and wait for review after Phase 0.

---

## Phase 1 — Foundation (separate DB, tenant flag, session, minimal seed, purge)

### 1.0 Second database connection (do this first)

- Register a `demo` connection driven by `DEMO_DB_*` env vars. Same engine and version as production.
- Run the **same** migrations against it (`migrate --database=demo`) and wire that into the deploy pipeline and CI, so its structure never drifts from production.
- All demo-tenant models/repositories resolve to this connection for the whole request when `DemoContext.isDemo()` is true. Prefer a single request-scoped connection switch over per-model annotations; if the ORM cannot do this cleanly, report it before implementing a workaround.
- Separate file disk (`DEMO_STORAGE_DISK`), cache prefix (`DEMO_CACHE_PREFIX`), and queue (`DEMO_QUEUE`) so demo jobs and cached values never mix with production ones.
- **Reference-table sync job:** daily one-way copy (production → demo) of the tables listed in `DEMO_REFERENCE_TABLES`. One direction only. Never copy patients, visits, invoices, real users, or any health data — this is a legal requirement, not a preference. The job must be idempotent and must not lock production tables (batched reads).

### 1.2b `DEMO_PROD_WRITE_GUARD` (the actual guarantee)

Having a second connection is not enough — a forgotten `->connection()` somewhere would silently write to production. Add a query listener on the production connection: during a request whose `DemoContext.isDemo()` is true, any `INSERT/UPDATE/DELETE/ALTER/TRUNCATE` throws immediately and logs an alert. Allow-list exactly two tables: `demo_sessions`, `demo_leads`. Enabled by default in all environments.

Required test: a demo request that attempts a write to a production-connection model must fail loudly.

### 1.1 Schema (migrations — applied to BOTH connections; `demo_sessions`/`demo_leads` live on production)

- `clinics`: add `is_demo BOOLEAN NOT NULL DEFAULT false` (indexed), `demo_expires_at TIMESTAMP NULL` (indexed), `demo_last_activity_at TIMESTAMP NULL`, `demo_template_key VARCHAR NULL`, `demo_source JSON NULL`.
- New table `demo_sessions`: `id UUID PK`, `clinic_id NULL FK (ON DELETE SET NULL)`, `started_role ENUM(doctor, assistant)`, `template_key`, `specialty`, `started_at`, `ended_at NULL`, `end_reason ENUM(expired, idle, user_ended, converted, purged) NULL`, `steps_completed JSON`, `lead_id NULL`, `converted_clinic_id NULL`, `utm_source, utm_medium, utm_campaign, utm_content, utm_term, fbclid, gclid, ttclid`, `ip_hash`, `country`, `device`, timestamps.
- New table `demo_leads`: `id`, `demo_session_id FK`, `name`, `phone`, `email`, `specialty`, `clinic_city`, `consent_marketing BOOL`, `created_at`.
- If any tenant-scoped table lacks a hard path to `clinics`, add the missing FK/column now (from the Phase 0 report).

### 1.2 Isolation scope

- With the separate demo database, the global `is_demo = false` scope on public/aggregate queries is **no longer required for safety** (production simply contains no demo rows). Do not add it — it was the highest-risk item in v1.0 and the second connection removes it. Keep `is_demo` only as a tenant marker *inside* the demo database.
- If the app uses a search index (Elastic/Meilisearch/Algolia), demo tenants are **never indexed** — assert this in a test, since the index is a shared resource that the DB separation does not cover.

### 1.3 DemoContext

- `DemoContext.isDemo()` resolved per request from (a) auth token claim `scope: demo` and (b) the tenant row (`is_demo`). The tenant row is the source of truth; the claim is a fast path. Mismatch → 401.
- Middleware on every authenticated request in a demo tenant: if `demo_expires_at < now` or idle timeout exceeded → end session, revoke, return `401 {code: "DEMO_ENDED"}`; else update `demo_last_activity_at` (throttled to once per minute).
- Demo tokens are rejected on admin routes and on any route whose tenant ≠ token tenant.

### 1.4 Config (env with defaults)

```
DEMO_ENABLED=true
DEMO_ISOLATION=tenant            # tenant | schema | database  (see design doc §2.1)
DEMO_DB_CONNECTION=demo
DEMO_DB_HOST=127.0.0.1
DEMO_DB_PORT=5432
DEMO_DB_DATABASE=mostashfaon_demo
DEMO_DB_USERNAME=mostashfaon_demo
DEMO_DB_PASSWORD=
DEMO_PROD_WRITE_GUARD=true
DEMO_STORAGE_DISK=demo
DEMO_CACHE_PREFIX=demo:
DEMO_QUEUE=demo
DEMO_MAIL_DRIVER=log
DEMO_SMS_DRIVER=null
DEMO_WHATSAPP_DRIVER=null
DEMO_PUSH_DRIVER=null
DEMO_PAYMENT_DRIVER=fake
DEMO_PHARMACY_DRIVER=fake
DEMO_LAB_DRIVER=fake
DEMO_VIDEO_DRIVER=mock
DEMO_REFERENCE_SYNC_CRON="0 3 * * *"
DEMO_REFERENCE_TABLES=medications,icd_codes,lab_tests,specialties,governorates,cities
DEMO_MAX_DURATION_MINUTES=240
DEMO_IDLE_TIMEOUT_MINUTES=45
DEMO_PURGE_INTERVAL_MINUTES=5
DEMO_MAX_CONCURRENT_PER_IP=3
DEMO_MAX_STARTS_PER_IP_PER_DAY=10
DEMO_GLOBAL_MAX_ACTIVE=500
DEMO_TIMEZONE=Africa/Cairo
DEMO_TURNSTILE_SECRET=...
DEMO_STORAGE_PREFIX=demo/
DEMO_MAX_FILE_MB=5
DEMO_MAX_TENANT_STORAGE_MB=50
```

### 1.5 Endpoint `POST /api/demo/start`

Request: `{ role: "doctor"|"assistant", specialty?: string, template_key?: string, utm?: {...}, click_ids?: {fbclid, gclid, ttclid}, turnstile_token: string }`

Steps (single transaction for DB writes; file writes after commit):

1. Verify Turnstile; enforce per-IP and global limits (Redis counters or DB).
2. If a valid demo cookie (`demo_session_id`) points to an active session → return it (idempotent).
3. Create `demo_sessions` row.
4. Create clinic (`is_demo=true`, `demo_expires_at = now + MAX_DURATION`), doctor user, assistant user (random 32-char passwords, never returned), permissions per template.
5. Run **Seeder** with the template (Phase 1 uses `general_v1_min`: 5 patients, 3 past visits, 1 in-progress, 2 waiting today, 2 upcoming).
6. Issue access token for the requested role with claims `{scope:"demo", demo_session_id, clinic_id, exp = demo_expires_at}`.
7. Set cookie `demo_session_id` (HttpOnly, SameSite=Lax, expires with session).
8. Return `{ session_id, access_token, redirect_url, expires_at, role }`.

On any failure after step 3 → mark session `end_reason=purged`, purge partial data, return 503 with a friendly message. Log seeding errors with template key.

### 1.6 Seeder engine

- Input: template JSON + `T0` (now in `DEMO_TIMEZONE`, rounded down to 5 min) + tenant ids.
- Time expression grammar:
  - `{ "day": -14, "time": "11:30" }` → calendar day offset in Cairo, then clock time.
  - `{ "minutes": -12 }` → relative to T0.
  - Optional `"skipWeekday": [5]` (Friday) at template level: if a computed date lands on a skipped weekday, shift forward one day (past dates shift backward). Always store UTC.
- Clinic working hours are defined in the template as `T0 - 3h → T0 + 5h` (clamped to at least 08:00–22:00 display range) so "now" is always inside working hours regardless of when the demo starts.
- Entities are created in dependency order and every insert carries `clinic_id`. The seeder must be **deterministic** given (template, T0, seed) so tests are stable.
- Expose `DemoSeeder.seed(clinicId, templateKey, T0)` and `DemoSeeder.validateTemplate(json)`; fail fast on unknown fields.

### 1.7 Purge job

- Scheduled every `DEMO_PURGE_INTERVAL_MINUTES`. Selects demo clinics where `demo_expires_at < now` OR `demo_last_activity_at < now - IDLE` OR session `ended_at` is set and `clinic_id` not null.
- Hard-deletes in child→parent order (generate the order from FK graph if possible; otherwise an explicit ordered list in `DemoPurger`). Also: files under `demo/{clinic_id}/`, cache keys `tenant:{clinic_id}:*`, search index docs, token revocation (blacklist or `token_version++`).
- Sets `demo_sessions.clinic_id = NULL`, `ended_at`, `end_reason` if not set.
- Idempotent and safe to run concurrently (row lock / `SELECT … FOR UPDATE SKIP LOCKED` or equivalent).
- Emit metrics: purged count, duration, failures.
- Also expose a CLI/console command `demo:purge --all` and `demo:purge --clinic=<id>`.

### 1.8 Tests (Phase 1 exit criteria)

- Unit: time-expression resolver (midnight, Friday skip, month boundary, UTC↔Cairo).
- Integration: `start` creates the exact expected row counts per table for `general_v1_min` — **and creates them on the demo connection**.
- **Guard: a demo request attempting any write on the production connection throws** (except `demo_sessions`/`demo_leads`). Run this test against several real flows (create visit, update clinic settings, upload file), not just a synthetic query.
- Integration: after purge, **a generic test iterates every table in the demo database that has a tenant path and asserts 0 rows** for the deleted clinic; also asserts files and cache keys are gone.
- Isolation: with an active demo running, production doctor search / listings / admin stats / finance reports return identical results to before the demo started (byte-for-byte comparison of the response).
- Reference sync: job copies only whitelisted tables, one direction, and fails if asked to copy a non-whitelisted table.
- Auth: demo token on admin route → 403; expired demo → 401 `DEMO_ENDED`; token for clinic A on clinic B resource → 403.
- Rate limit: 4th concurrent start from same IP → 429.

---

## Phase 2 — Full experience (template, guard, demo UI)

### 2.1 Template `general_v1` (Arabic, Egyptian, realistic)

Create `resources/demo-templates/general_v1.json` implementing the content table in the design doc §5.3: 1 clinic, 5 services with EGP prices, 1 doctor, 1 assistant with restricted permissions, 18 patients (phones in `0100 000 00xx` format, one with a penicillin allergy), 12 completed visits over D-45…D-1 with diagnosis/vitals/prescription/paid invoice (some with lab request + attached result), 1 in-progress visit at T0-12m with vitals recorded by the assistant and empty prescription, 4 waiting today (T0+20m checked_in, T0+45m, T0+75m follow-up, T0+120m online), 6 upcoming (D+1, D+1, D+2, D+3, D+6, D+10; one cancelled, one pending confirmation), 1 no-show at D-2, 2 unpaid invoices, 6 notifications/messages (simulated reminders, one 5-star review, one reschedule request), and one **scheduled in-app notification at T0+60s** ("patient X checked in").

Rule: every screen in the doctor and assistant apps must show real content immediately after start. Add a checklist in the PR description mapping each screen → the template entities that populate it.

### 2.2 DemoGuard (provider decorators)

Wrap each external provider identified in Phase 0 §4 with a demo-aware implementation chosen at DI time via `DemoContext`:

| Channel | Demo behaviour |
|---|---|
| SMS / WhatsApp / Email / Push | write to tenant `outbox` with `simulated=true`; UI shows "محاكاة" badge |
| Payments | gateway sandbox if available, else instant simulated success; receipts carry a Demo stamp |
| Pharmacy / lab partner orders | internal order that auto-advances (`accepted` +1m, `delivered` +5m via delayed job) |
| Video calls | mock room page with a placeholder video |
| Invitations | created internally, acceptable from the same screen |
| File uploads | allowed; prefix `demo/{clinic_id}/`; per-file and per-tenant caps |
| Account email/phone change | allowed; OTP `000000` accepted, nothing sent |
| Third-party integrations (calendar, social) | disabled with tooltip "متاح في الحساب الحقيقي" |
| Delete clinic / close account | redirects to "end demo" |
| Exports (Excel/PDF) | allowed with a Demo watermark |

Add a test double that **fails the test suite** if any real provider is invoked while `DemoContext.isDemo()` is true.

### 2.3 Demo UI

- **Demo bar** (fixed top; bottom on mobile): countdown, role switch (doctor ⇄ assistant), "أعد التجربة", primary CTA "أنشئ حسابك الحقيقي", "إنهاء". RTL, Arabic copy in the design doc §8.1.
- Endpoints: `POST /api/demo/{session}/switch-role`, `/reset`, `/end`, `GET /api/demo/{session}`.
- `reset` = purge tenant data + re-seed with the same template and a new T0, same session id.
- Landing page `/demo`: Arabic RTL, two CTAs, optional specialty select, hidden Turnstile, reads UTMs/click ids into first-party cookie (`mo_attrib`, 90 days), calls `start`, redirects. Lighthouse mobile performance ≥ 90.

### 2.4 Tests (Phase 2 exit criteria)

- Template validation test for `general_v1`; row-count snapshot test.
- Guard tests per channel (no real call, simulated artefact created).
- Playwright E2E: doctor flow (open in-progress visit → write prescription → finish → invoice → call next patient), assistant flow (check-in → collect payment), role switch, reset, end → 401.
- Visual check at 360px width (RTL) for the demo bar.

---

## Phase 3 — Conversion & measurement

### 3.1 Guided checklist

8 steps (design doc §8.2). Steps auto-complete from domain events (e.g. `PrescriptionCreated` within demo tenant → step 1). Persist in `demo_sessions.steps_completed`. `POST /api/demo/{session}/progress` as a fallback for UI-only steps. Toast at 5/8.

### 3.2 Lead capture

`POST /api/demo/{session}/lead` → `demo_leads` + outbound webhook (configurable URL: Google Sheet/Apps Script, CRM, or WhatsApp Business API). Triggers: after step 3, after 6 minutes, or desktop exit-intent — **never before entering**. Dismissible; shown at most twice per session.

### 3.3 Convert to real account

`POST /api/demo/{session}/convert` → runs the normal signup flow; optional flag `copy_settings=true` copies **only**: clinic name, address, working hours, services & prices, assistant permission preset. Never patients, visits, invoices, files. Then ends the demo with `end_reason=converted` and `converted_clinic_id` set. First login to the real account shows a "welcome back from demo" hint.

### 3.4 Analytics events (client + server, deduplicated by `event_id`)

| Event | Trigger |
|---|---|
| `PageView` / `demo_view` | `/demo` loaded |
| `demo_start` (custom) | start succeeded |
| `demo_step_{1..8}` | checklist step |
| `demo_engaged` | 5/8 steps or 6 min active |
| `Lead` (standard) | lead saved |
| `CompleteRegistration` (standard) | convert succeeded |
| `demo_role_switch`, `demo_reset`, `demo_end` (`reason`) | — |

Server side: Meta Conversions API + GA4 Measurement Protocol (+ TikTok Events API if used). Include `demo_session_id`, `template_key`, `started_role`, UTMs, hashed `fbp/fbc` where available. Client side: Meta Pixel + gtag with the same `event_id`.

### 3.5 Internal metrics

`GET /internal/demo/metrics` (admin only): active demos, starts/day, seed p95 latency, seed failures, step completion funnel, lead rate, conversion rate, purge backlog. Optional Grafana/Metabase dashboard.

### 3.6 Tests

Event emission tests (both sides, same `event_id`); convert copies only allowed settings; lead webhook retried on failure; checklist auto-completion from domain events.

---

## Phase 4 — Expansion

- Templates `dental_v1` (tooth chart, dental services), `pediatric_v1` (growth chart, vaccination schedule). Template selected from `/demo?specialty=`.
- Live-feel scheduled notifications (new booking at T0+3m, payment received at T0+7m).
- A/B flag on checklist copy and lead-capture timing (store variant in `demo_sessions`).
- Optional: host on `demo.mostashfaon.com` with same codebase and `DEMO_ONLY=true` flag if production load requires.

---

## Definition of done (whole feature)

- **The production database contains zero rows created by any demo session** (verified by the write guard, its tests, and a staging soak run), apart from `demo_sessions` / `demo_leads`.
- A visitor with no account reaches a populated doctor dashboard in < 3 s p95 from clicking "جرّب كطبيب".
- Every doctor/assistant screen is non-empty on first load.
- No external provider is called during a demo (verified by tests and by provider dashboards in staging).
- After end/expiry/idle, zero rows, files, cache keys or index docs remain for the tenant; old tokens return 401.
- Demo tenants are invisible to patients, public pages, admin stats.
- Funnel events arrive in Meta Events Manager and GA4 with deduplication working.
- Runbook `docs/demo-sandbox/RUNBOOK.md`: how to edit a template, force-purge, disable demo (`DEMO_ENABLED=false` returns a friendly page), read metrics.

---

# Appendix A — Laravel / Laragon specifics (project: `C:\laragon\sites\rosheta`)

> Added in v1.2. The stack is assumed to be **Laravel + MySQL/MariaDB** (Laragon default). **Verify this in Phase 0 and correct this appendix if wrong.** If the app already uses a tenancy package (`stancl/tenancy`, `spatie/laravel-multitenancy`), read its docs first and integrate with it instead of hand-rolling connection switching — say so before implementing.

## A.1 Isolation mode is `tenant`

MySQL has no schemas separate from databases, so schema-per-session is unavailable and database-per-visitor is too heavy. Use **one demo database + a tenant row per visitor** (`DEMO_ISOLATION=tenant`).

## A.2 The connection switch — flip the default, pin the exceptions

In `config/database.php` add a `demo` connection that duplicates `mysql` but reads `DEMO_DB_*`.

Switch by **flipping the default connection for the whole request** in demo middleware:

```php
Config::set('database.default', config('demo.connection')); // 'demo'
DB::purge('mysql'); // optional, only if a stale connection would confuse things
```

This is preferred over annotating every model with `$connection`, because a single forgotten model would silently hit production. Then **pin the exceptions explicitly** on their models:

```php
class DemoSession extends Model { protected $connection = 'mysql'; } // production
class DemoLead    extends Model { protected $connection = 'mysql'; } // production
```

Reference-data models (medications, ICD codes, cities, specialties) read from the **demo** database (populated by the sync job), not from production — do not pin those.

## A.3 Middleware order is the most likely thing to break

The demo middleware **must run before authentication**. If `auth` resolves the user first, Laravel/Sanctum will look for that user (and `personal_access_tokens`) on the production connection and the demo login will fail or, worse, query production. Register the demo middleware early in the group, ahead of `auth`/`auth:sanctum`, and add a test asserting a demo request never queries `users` on the production connection.

Related: if `SESSION_DRIVER=database`, session writes will follow the flipped default into the demo DB. Prefer token-based auth for the demo (or `SESSION_DRIVER=redis`/`file`) and state which you chose.

## A.4 `DEMO_PROD_WRITE_GUARD` in Laravel

Register in a service provider:

```php
DB::connection('mysql')->listen(function ($query) {
    if (! app(DemoContext::class)->isDemo()) return;
    if (! preg_match('/^\s*(insert|update|delete|alter|truncate|drop|replace)\b/i', $query->sql)) return;
    if (Str::contains($query->sql, ['demo_sessions', 'demo_leads'])) return; // allow-list
    Log::critical('Demo request attempted production write', ['sql' => $query->sql]);
    throw new DemoProductionWriteException($query->sql);
});
```

Note `DB::listen` fires **after** the query runs, so this reports rather than prevents. If the framework version allows, use a `before_executing` hook / a custom connection class that overrides `statement()`, `insert()`, `update()`, `delete()`, `affectingStatement()` to throw first. Implement the preventive version if possible and say which one you used. Either way the test in §1.2b must fail the suite when a production write is attempted.

## A.5 Queued jobs lose the request-scoped switch

A job dispatched during a demo request runs in a worker with the default connection — production. Every demo job must carry `clinic_id` + a demo flag in its payload and re-establish the context in `handle()` (a shared `InteractsWithDemoTenant` trait). Dispatch them `->onQueue(config('demo.queue'))` and run a dedicated worker for that queue. Add a test that a job dispatched from a demo request does not touch production.

## A.6 Migrations

```bash
php artisan migrate --database=demo
```

Each connection keeps its own `migrations` table, so both stay in sync structurally. Watch for migrations that hard-code `DB::statement('...')` or `Schema::connection('mysql')` — Phase 0 must list them; they will silently run against the wrong database. Add the demo migrate step to the deploy script and to CI.

## A.7 Storage, cache, queue

- `config/filesystems.php`: add a `demo` disk (separate root/bucket) used whenever `isDemo()`.
- Cache: use `DEMO_CACHE_PREFIX`; do not rely on `Cache::tags()` if the driver is `file`.
- Queue: `DEMO_QUEUE` as above.

## A.8 Purge in MySQL

Ordered deletes child → parent inside `DB::connection('demo')->transaction()`. Do **not** disable `FOREIGN_KEY_CHECKS` — an ordering bug would then pass silently and leave orphans. Generate the delete order from the FK graph if possible and assert it in the §1.8 test that walks every table dynamically (use `SHOW TABLES` / information_schema on the demo connection so a table added later cannot be missed).

## A.9 Local dev on Laragon (Windows)

- Create the demo database in HeidiSQL: `CREATE DATABASE rosheta_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` (Laragon default user is usually `root` with an empty password — keep that only locally; production must use a dedicated demo DB user with no access to the production database).
- The scheduler does not run automatically on Laragon: use `php artisan schedule:work` in a terminal during development, and a real cron/Task Scheduler entry on the server for the purge and reference-sync jobs.
- Windows paths: use `storage_path()` / `Storage::disk('demo')`, never hard-coded `\` paths.

---

# Appendix B — Discovery reconciliation (v1.3)

> Written after the Phase 0 report on `C:\laragon\sites\rosheta`. **Where this appendix contradicts the body of the brief or Appendix A, this appendix wins.** The findings it responds to came from reading the actual code; the body was written blind.

## B.1 The tenant is `doctors`, not `clinics`

Everywhere the brief says `clinics.is_demo`, read `doctors.is_demo`. Concretely:

- Demo columns (`is_demo`, `demo_expires_at`, `demo_last_activity_at`, `demo_template_key`, `demo_source`) go on **`doctors`**.
- `demo_sessions.clinic_id` → `demo_sessions.doctor_id`; `converted_clinic_id` → `converted_doctor_id`.
- The purge walks from `doctor_id`, and must explicitly cover the five doctor-keyed tables with no clinic path: `billable_items`, `medical_plans`, `examination_fields`, `insurance_collections`, `conversations`. Clinics are deleted as children of the doctor.
- File storage prefix becomes `demo/{doctor_id}/`.
- The dynamic purge-completeness test (§1.8) must enumerate tables by **both** `doctor_id` and `clinic_id` paths, and must fail on any table it cannot classify — an unclassifiable table is a finding, not something to skip.

## B.2 `clients` being global confirms the separate database

A `tenant`-mode demo inside the production database would have created real patient accounts with logins, visible in `/admin/clients` and to the patient app and marketplace. That settles it: `DEMO_ISOLATION=tenant` **inside a separate demo database**, never inside production. In the demo database, `clients` holds demo patients only.

`clients` is therefore **never** in `DEMO_REFERENCE_TABLES`. The sync job copies clinical/geographic reference data only; add an assertion that the job refuses to copy `clients`, `visits`, `prescriptions`, `invoices` or any user table even if someone adds them to the env var later.

## B.3 Sessions, cache and queue — decision (answers open question #1)

**Do not solve this by widening the allow-list.** A 7-table allow-list would make the guarantee unreadable and would put demo-owned rows in production tables where a `user_id` collision (demo doctor #5 vs real doctor #5) could affect a real account.

**Decision: detect demo by URL prefix, then point session, cache and queue at demo infrastructure.**

1. All demo traffic lives under its own URL prefix (`/demo/...`) or a `demo.` subdomain. `DemoContext::isDemo()` is derived from the **request path/host** — never from the session.
2. That removes the chicken-and-egg problem entirely: the demo middleware can run **before `StartSession`** at the top of the `web` group, because it needs nothing from the session to decide.
3. In that middleware, before `StartSession` boots: flip `database.default` to `demo`, set `session.connection` to `demo` (or `session.driver=file` with a demo-specific path), set the cache prefix, and set the queue connection/queue name.
4. Demo jobs run on a **dedicated worker** for the demo queue on the demo connection. A job dispatched from a demo request must carry `doctor_id` + the demo flag and re-establish context in `handle()` (Appendix A.5 still applies).
5. Use a distinct session cookie name for demo (e.g. `rosheta_demo_session`) so a demo session can never be mistaken for a production one.

Result: the production write allow-list stays at exactly **two** tables (`demo_sessions`, `demo_leads`), and the guarantee remains "no demo request writes anything to the production database except its own funnel records".

**Prototype this before anything else** (see B.8). If the spike shows the middleware cannot be ordered before `StartSession` in this app, stop and report — do not fall back to a wide allow-list without telling me.

## B.4 Auth flow: replace tokens with the `web` guard

§1.3 and §1.5 assume JWT claims and an `access_token` response. Rewrite for the session-based `practice.*` routes:

- `POST /api/demo/start` creates the tenant, seeds it, then `Auth::guard('web')->login($demoDoctor)` and returns a `redirect_url`; no token is issued or returned.
- Role switch = `Auth::login()` as the assistant user in the same demo tenant, with a check that both users belong to the demo doctor of the current session.
- "Revoke tokens" at end-of-demo becomes: invalidate and flush the session, regenerate the CSRF token, and delete the demo session records so the cookie resolves to nothing.
- Expiry/idle enforcement moves into the demo middleware (which runs on every demo request anyway).

## B.5 Testing: build the minimum harness, not a suite for the legacy app

`sqlite :memory:` is unusable here (no `pdo_sqlite`, plus MySQL-only DDL in five migrations). Do not try to make SQLite work, and do not attempt to retrofit tests across the existing application.

- Point `phpunit.xml` at a real MySQL test database (`rosheta_test`), created and migrated by a script.
- Add only the factories the demo work needs: `Doctor`, `Clinic`, `Appointment`, plus whatever the seeder touches.
- Write only the tests that protect the two promises we actually make: **(a) no production writes from a demo request** (exercised through several real flows, not a synthetic query), and **(b) purge leaves zero rows, files and cache keys**. Then the auth/expiry tests. Everything else in §1.8 is optional for Phase 1.
- Treat this harness as the first deliverable of Phase 1; the guarantees are unverifiable without it.

## B.6 Reuse `ClinicOnboardingService` — by composition, not modification

`DemoSeeder` calls it; it does not fork or rewrite it. It is production onboarding code, so:

- Do not change its behaviour. If the seeder needs something it does not expose, add a parameter with a default that preserves today's behaviour exactly, and write a characterization test of the current output **before** touching it.
- The seeder adds on top: `T0`-relative time resolution, a seeded RNG for determinism, and JSON template loading.
- Keep template data out of the service — the service builds entities, the template says what to build.

## B.7 What got smaller

- **DemoGuard** collapses to FCM push + `MAIL_MAILER=log`. No SMS, WhatsApp, payment gateway, pharmacy/lab partner API, video provider or webhooks exist — so §2.2's table is mostly inapplicable. Keep the "fail the suite if a real provider is invoked" test for the two that do exist.
- **No search index** → drop the index-isolation test in §1.2.
- **Timezone** is already `Africa/Cairo` app-wide → §1.6 only needs the offset resolver, not timezone plumbing.
- **No soft deletes** → the "hard delete even though the app soft-deletes" caveat in rule 3 is moot.
- **Appendix A.6's migration warning does not apply** — verified: `migrate --database=demo` sets the default connection around `up()`.

## B.8 Revised Phase 1 order

0. **Spike (1 day, throwaway branch):** prove the demo middleware can run before `StartSession`, flip `database.default`, and give the request its own session/cache/queue — with one route that reads and writes a row in the demo database and leaves production untouched. Report before continuing. This is the riskiest assumption in the whole design; everything else is ordinary work.
1. Test harness + factories (B.5).
2. Second connection, storage disk, cache prefix, queue, reference sync (§1.0, with B.2's refusal assertion).
3. `DEMO_PROD_WRITE_GUARD` + its test (§1.2b) — preventive version if possible (A.4).
4. Schema: demo columns on `doctors`, `demo_sessions` / `demo_leads` on production (B.1).
5. `POST /api/demo/start` with the `web`-guard flow (B.4) + `DemoSeeder` over `ClinicOnboardingService` (B.6), minimal template.
6. Purge covering both `doctor_id` and `clinic_id` paths (B.1) + the dynamic completeness test.

## B.9 Out of scope but urgent — pre-existing IDOR

`Clinic\PatientController::show()` and `update()` have no ownership check: any authenticated clinic user can read or edit any row in the global `clients` table by id. This is a live cross-tenant exposure of real patient records **today**, unrelated to the demo feature. Fix it as a standalone hotfix on its own branch, before or in parallel with Phase 1 — do not bundle it into this work, and do not let the demo project's timeline delay it. While fixing, audit the other `Clinic\*` controllers for the same missing check.
