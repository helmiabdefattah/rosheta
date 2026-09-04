# Demo Sandbox — Phase 0 Discovery

**Status:** read-only investigation. No application code changed; the only commit is the
documentation move.
**Repo:** `c:\laragon\sites\rosheta`, branch `dual_systems`, HEAD `3703145`.
**Brief version read:** `IMPLEMENTATION_BRIEF.md` **v1.3**, in full, including Appendix A
(Laravel/Laragon specifics) and Appendix B (discovery reconciliation).
**Date:** 2026-09-04.

Companion file: `SCHEMA_MAP.md` (placeholder → real names).

> **Documents located.** The English brief was at repo root as
> `02claudecodeimplementationbriefEN.md`; the prompt set was at
> `04claudecodeprompts.md`. Both moved to `docs/demo-sandbox/` as
> `IMPLEMENTATION_BRIEF.md` and `PROMPTS.md` in commit `55f9c1c`, on its own.
>
> **The Arabic design document** (v1.1) was supplied separately and is now saved as
> `docs/demo-sandbox/DESIGN_AR.md`. It is **older than the brief (v1.3)**, so Appendix B
> supersedes it wherever they disagree. Its §2.1, §5.3, §8.1 and §8.2 are reconciled
> against the codebase in **§11** below — including several content items the template
> requires that this application cannot currently represent.

---

## Q0. Stack assumption — where Appendix A is right and where it is wrong

Read: `composer.json`, `config/database.php`, `config/auth.php`, `config/session.php`,
`config/cache.php`, `config/queue.php`, `config/filesystems.php`, `config/app.php`,
`.env`, `.env.example`, `bootstrap/app.php`, `bootstrap/providers.php`.

| Item | Actual value | Appendix A said |
|---|---|---|
| Framework | Laravel **12.46.0** (`php artisan --version`), PHP `^8.2` | "Laravel" — ✅ correct |
| Skeleton | Laravel 11/12 slim — `bootstrap/app.php` + `bootstrap/providers.php`, **no `app/Http/Kernel.php`** | implied older structure |
| Database | **MySQL** — `DB_CONNECTION=mysql`, database `rosheta`, InnoDB, `utf8mb4_unicode_ci` | "MySQL/MariaDB (Laragon default)" — ✅ correct |
| MySQL version | **UNKNOWN — not verified.** `medical_tests` uses `utf8mb4_0900_ai_ci`, which implies MySQL ≥ 8.0, but I did not query `@@version` | not stated |
| Tenancy package | **None.** No `stancl/tenancy`, no `spatie/laravel-multitenancy` in `composer.json` | "if the app already uses one…" — it does not |
| Auth (clinic system) | **Session, `web` guard.** `practice.*` routes use `['auth', 'clinic.role:…']` | ❌ brief §1.3/§1.5 assume JWT + `access_token` — **corrected by Appendix B.4** |
| Auth (marketplace API) | `laravel/sanctum` 4.2.0, `personal_access_tokens`, guard `sanctum` with a **null provider** | |
| Other guards | `web` (User), `client` (Client), `nurse` (Nurse), `agent` (PharmacyAgent) | |
| Session driver | `SESSION_DRIVER=database`, `SESSION_CONNECTION` **unset** → default connection | ❌ A.3's aside is right to worry — see Q8 |
| Cache driver | `CACHE_STORE=database`, `DB_CACHE_CONNECTION` **unset** → default connection | not mentioned; same hazard |
| Queue driver | `QUEUE_CONNECTION=database`, `DB_QUEUE_CONNECTION` **unset** → default connection | A.7 assumes a queue name is enough; it is not |
| File storage | `FILESYSTEM_DISK=local`, but every clinic upload hard-codes `Storage::disk('public')` | A.7 assumes adding a disk suffices; five call sites must change |
| Timezone | `config/app.php:13` → `env('APP_TIMEZONE', 'Africa/Cairo')` — **already Cairo app-wide** | not mentioned; makes §1.6 simpler |
| Admin panel | Filament 4 at `/admin`, 13 resources (marketplace only, no clinic resources) | not mentioned |
| Media | `spatie/laravel-medialibrary` ^11.17 on `User` and `Doctor`; `config/media-library.php` not published | not mentioned |
| Debug | `laravel/telescope` ^5.16, enabled, 251,464 rows in `telescope_entries` | not mentioned |
| Migrations | 132 files on disk, 69 rows in `migrations` | |

**Appendix A verdicts:**

- **A.1 (isolation mode `tenant`)** — ✅ correct conclusion, and Appendix B.2 strengthens
  the reason. See Q10.1.
- **A.2 (flip the default connection, pin exceptions)** — ✅ workable; Q8 confirms
  nothing in `app/` hard-codes a connection.
- **A.3 (middleware order is the most likely thing to break)** — ✅ correct, and it is
  worse than stated because `SESSION_DRIVER=database`. Resolved order in Q8.
- **A.4 (`DB::listen` fires after the query)** — ✅ correct. Use the preventive form.
- **A.5 (queued jobs lose the switch)** — ✅ correct in principle, but currently
  theoretical: there is no `app/Jobs/` directory and no `dispatch()` call anywhere.
- **A.6 (migrations with `DB::statement` may hit the wrong database)** — ❌ **wrong for
  Laravel 12.** Verified in
  `vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php::runMethod()`,
  which wraps `up()` in `$this->resolver->setDefaultConnection($connection->getName())`.
  `migrate --database=demo` is safe. (Appendix B.7 already records this.)
- **A.7 (storage/cache/queue)** — ⚠️ incomplete; see Q8's table of four.
- **A.8 (ordered deletes, no `FOREIGN_KEY_CHECKS` toggle)** — ✅ correct, and Q7 supplies
  the order.
- **A.9 (Laragon: scheduler does not run automatically)** — ✅ correct, and stronger than
  stated: **nothing is scheduled at all** (`routes/console.php` contains only `inspire`).

---

## Q1. Stack & conventions

Covered by Q0. Additional conventions worth matching:

| Convention | Observed |
|---|---|
| Controllers | Fat controllers, thin models. One service (`ClinicOnboardingService`). No form requests — validation is inline `$request->validate([...])` |
| Authorization | Inline `abort_unless($appointment->doctor_id === $doctor->id, 403)`. **No Gates, no Policies, no `authorize()`** |
| Shared controller state | Traits under `App\Http\Controllers\Clinic\Concerns\` (`ClinicContext`, `BuildsClinicCalendar`) |
| Localisation | `lang/` + `__('app.…')` keys; `SetLocale` middleware reads `session('locale')`, default `en` |
| Comments | Substantial Arabic-context docblocks on the clinic system explaining *why* — match this density |
| Events | **None.** No `event()` call anywhere in `app/` |
| Jobs | **`app/Jobs/` does not exist.** No `dispatch()`. Only queued notifications (`BaseNotification implements ShouldQueue`) |
| Tests | `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` — the stock pair, nothing else |

### Dead code — do not wire demo logic into these

`app/Providers/AuthServiceProvider.php`, `EventServiceProvider.php`,
`RouteServiceProvider.php` exist but are **absent from `bootstrap/providers.php`**, so
they never boot. `app/Http/Middleware/HandleCors.php` is likewise unregistered (the
framework's own `HandleCors` runs instead). `app/Model s/Client.php` — directory name
contains a space — is an orphan. There is also a stray `-Force/` directory at repo root
from a mistyped PowerShell command.

---

## Q2. Tenancy model — exhaustive table inventory

### The finding that reframes everything: the tenant is `doctors`, not `clinics`

`clinics.doctor_id` (`database/migrations/2026_01_26_100002_create_clinics_table.php:14`)
makes a clinic a *location* under a doctor, and a doctor may own several
(`Doctor::clinics()`, `app/Models/Doctor.php:56`). Five tables carrying clinical or
financial data are keyed by `doctor_id` with **no `clinic_id` column at all**:
`billable_items`, `medical_plans`, `examination_fields`, `insurance_collections`,
`conversations`. Marking only `clinics.is_demo` would leave them unmarked and
un-purgeable. Adopted by the brief as Appendix B.1.

```
users.doctor_id ──┐
                  ├─→ doctors ──┬─→ clinics ──→ appointments ──→ all per-visit data
users.clinic_id ──┘             ├─→ billable_items
                                ├─→ medical_plans → medical_plan_items
                                ├─→ examination_fields
                                ├─→ insurance_collections
                                └─→ conversations → chat_messages
```

### All 70 tables

Source: `information_schema.TABLES` / `.KEY_COLUMN_USAGE` / `.REFERENTIAL_CONSTRAINTS`
on database `rosheta` (118 foreign keys total). Row counts are `TABLE_ROWS` estimates.

**Legend for Class:** `T` = tenant-scoped (seed + purge) · `G` = global, demo writes into
it (leak risk) · `R` = reference/read-only · `I` = infrastructure · `M` = marketplace,
untouched by the demo.

| # | Table | Rows | Class | Tenant key / path | Risk note |
|--:|---|--:|:--:|---|---|
| 1 | `doctors` | 14 | **T** | *is the tenant* | `user_id` SET NULL; `specialization_id` CASCADE to a reference table |
| 2 | `clinics` | 5 | **T** | `doctor_id` CASCADE | also `user_id` SET NULL |
| 3 | `users` | 176 | **T** | `doctor_id`, `clinic_id` — both **SET NULL** | Doctor + assistant logins. SET NULL means users survive tenant delete → **must be purged explicitly** |
| 4 | `appointments` | 201 | **T** | `clinic_id`, `doctor_id` CASCADE | `client_id` SET NULL |
| 5 | `appointment_items` | 7 | **T** | `appointment_id` CASCADE | |
| 6 | `appointment_insurances` | 7 | **T** | `appointment_id` CASCADE | `created_by`→users SET NULL |
| 7 | `collections` | 14 | **T** | `appointment_id` CASCADE | `collected_by`→users SET NULL |
| 8 | `diagnoses` | 16 | **T** | `appointment_id` CASCADE | `doctor_id` SET NULL, `client_id` CASCADE |
| 9 | `prescriptions` | 17 | **T** | `appointment_id` CASCADE | `doctor_id`/`diagnosis_id` SET NULL |
| 10 | `prescription_items` | 42 | **T** | `prescription_id` CASCADE | |
| 11 | `medical_requests` | 43 | **T** | `appointment_id` CASCADE | `doctor_id` SET NULL |
| 12 | `examination_field_values` | 90 | **T** | `appointment_id` CASCADE | `attachment_id` SET NULL → **file orphan** |
| 13 | `examination_fields` | 12 | **T** | `doctor_id` CASCADE | ⚠ no `clinic_id` |
| 14 | `billable_items` | 10 | **T** | `doctor_id` CASCADE | ⚠ no `clinic_id` |
| 15 | `medical_plans` | 8 | **T** | `doctor_id` CASCADE | ⚠ no `clinic_id` |
| 16 | `medical_plan_items` | 20 | **T** | `medical_plan_id` CASCADE | |
| 17 | `insurance_collections` | 0 | **T** | `doctor_id` CASCADE | ⚠ no `clinic_id` |
| 18 | `conversations` | 4 | **T** | `doctor_id` CASCADE | ⚠ no `clinic_id`; `client_id` CASCADE |
| 19 | `chat_messages` | 14 | **T** | `conversation_id` CASCADE | |
| 20 | `clinic_working_hours` | 40 | **T** | `clinic_id` CASCADE | |
| 21 | `clinic_doctor` | 8 | **T** | `clinic_id`, `doctor_id` CASCADE | pivot |
| 22 | `clinic_doctor_working_hours` | 80 | **T** | `clinic_id`, `doctor_id` CASCADE | |
| 23 | `doctor_off_dates` | 3 | **T** | `doctor_id` CASCADE | `clinic_id` **SET NULL** |
| 24 | `patient_tests` | 16 | **T** | `client_id` CASCADE only | ⚠ `appointment_id` **and** `doctor_id` both SET NULL → **survives tenant delete**; holds a file path |
| 25 | `attachments` | 25 | **T/G** | `appointment_id` **SET NULL**; polymorphic `attachable` with **no FK** | ⚠ **worst case.** Survives every cascade, holds the file, and has no hard path to the tenant. Add `clinic_id` |
| 26 | **`clients`** | 165 | **G** | **none** | ⚠⚠ **The decisive finding.** Patients are global rosheta accounts with logins, emails, phones. Visible to `/admin/clients`, the patient app and the marketplace. No `clinic_id`, no `doctor_id` |
| 27 | `client_addresses` | 597 | G | `client_id` CASCADE | via clients |
| 28 | `bonus_points` | 42 | G | `client_id` CASCADE | via clients |
| 29 | `feedbacks` | 0 | G | `client_id` CASCADE | via clients |
| 30 | `quotes` | 3 | G | `client_id` CASCADE | via clients; also polymorphic `model_type`/`model_id` |
| 31 | `reviews` | 8 | G | `client_id` — **no FK** | ⚠ nothing cascades; orphans on client delete |
| 32 | `notifications` | 483 | G | polymorphic `notifiable_type`/`notifiable_id` — **no FK** | ⚠ demo pushes orphan silently; delete by hand |
| 33 | `media` | 4 | G | polymorphic `model_type`/`model_id` — **no FK** | ⚠ spatie; `Doctor`/`User` avatars |
| 34 | `personal_access_tokens` | 46 | G | polymorphic `tokenable` — **no FK** | ⚠ not used by the clinic system, but a demo user could acquire one via `/api/login` |
| 35 | `sessions` | 1 | G/I | `user_id` — **no FK** | ⚠ follows the connection flip — see Q8 |
| 36 | `client_requests` | 193 | G/M | `client_id` CASCADE | ⚠ written by `ClinicOnboardingService::seedMarketplaceLabResults()` → demo rows in the **marketplace** |
| 37 | `client_request_lines` | 426 | G/M | `client_request_id` CASCADE | same; `medicine_id`/`medical_test_id` are **RESTRICT** |
| 38 | `offers` | 168 | G/M | `client_request_id` **NO ACTION** | ⚠ blocks deleting a demo patient's `client_requests` → **FK error 1451** unless deleted first |
| 39 | `offer_lines` | 392 | G/M | `offer_id` CASCADE | `medicine_id`/`medical_test_id` RESTRICT |
| 40 | `orders` | 16 | G/M | `client_request_id` **NO ACTION** | same hazard as `offers` |
| 41 | `order_lines` | 8 | G/M | `order_id` CASCADE | |
| 42 | `specializations` | 35 | **R** | — | FK target of `doctors.specialization_id` → **must mirror** |
| 43 | `governorates` | 27 | **R** | — | FK target of `clinics`, `clients` → **must mirror** |
| 44 | `cities` | 102 | **R** | `governorate_id` CASCADE | FK target of `clinics` → **must mirror** |
| 45 | `areas` | 102 | **R** | `city_id` CASCADE | FK target of `clinics` → **must mirror** |
| 46 | `insurance_companies` | 4 | **R** | — | FK target of `appointment_insurances`, `insurance_collections` → **must mirror** |
| 47 | `laboratories` | 8 | **R** | `user_id`, `area_id` SET NULL | FK target used by `seedMarketplaceLabResults()` → **must mirror** |
| 48 | `medicines` | 22,910 | **R** | — | Read by `/api/medicines/search`. **Not** an FK target from any demo row |
| 49 | `medical_tests` | 682 | **R** | — | ⚠ collation `utf8mb4_0900_ai_ci` — all other tables are `utf8mb4_unicode_ci`. Reproduce per-table or joins throw *Illegal mix of collations* |
| 50 | `pharmacies` | 151 | M | `user_id`, `area_id` SET NULL | |
| 51 | `nurses` | 2 | M | `user_id` CASCADE | |
| 52 | `nurse_offers` | 7 | M | `nurse_id`, `home_nurse_request_id` CASCADE | |
| 53 | `nurse_visits` | 54 | M | `home_nurse_request_id` CASCADE | |
| 54 | `home_nurse_requests` | 8 | M | `client_id` CASCADE | ⚠ cascades from a demo patient |
| 55 | `medical_test_offers` | 603 | M | `laboratory_id`, `medical_test_id` CASCADE | |
| 56 | `laboratory_test_prices` | 2 | M | `laboratory_id` CASCADE | |
| 57 | `charitable_organizations` | 9 | M | `user_id` SET NULL | |
| 58 | `support_tickets` | 0 | M | `assigned_to`→users SET NULL | |
| 59 | `working_hours` | 0 | M | — no FK | Unused legacy table (0 rows) |
| 60 | `migrations` | 69 | I | — | Per-connection; demo DB keeps its own |
| 61 | `cache` | 0 | I | — | ⚠ follows the connection flip |
| 62 | `cache_locks` | 0 | I | — | ⚠ follows the connection flip |
| 63 | `jobs` | 2 | I | — | ⚠ follows the connection flip |
| 64 | `job_batches` | 0 | I | — | ⚠ follows the connection flip |
| 65 | `failed_jobs` | 0 | I | — | ⚠ follows the connection flip |
| 66 | `password_reset_tokens` | 0 | I | — | |
| 67 | `password_reset_codes` | 0 | I | — | |
| 68 | `telescope_entries` | 251,464 | I | — | Pinned to production by `config/telescope.php:62` (`env('DB_CONNECTION','mysql')`) |
| 69 | `telescope_entries_tags` | 209,500 | I | `entry_uuid` CASCADE | same |
| 70 | `telescope_monitoring` | 0 | I | — | same |

**Counts:** T = 25, G = 16 (of which 6 are marketplace spillover), R = 8, M = 10, I = 11.

---

## Q3. Users & roles

- **Doctor** — a `users` row with a `doctors` row pointing back (`doctors.user_id`).
  `User::isDoctor()`, `app/Models/User.php:136`.
- **Assistant** — a `users` row with `users.doctor_id` set, usually also
  `users.clinic_id`. `User::isAssistant()`, `app/Models/User.php:142`.
- **Gate** — `App\Http\Middleware\EnsureClinicRole` (alias `clinic.role`, registered
  `bootstrap/app.php:19`). Accepts `doctor`, `assistant`, or both. It also logs out
  suspended accounts (`EnsureClinicRole:29-43`) and stashes the resolved `Doctor` on
  `$request->attributes` under `clinic_doctor` (`:55`).
- **No permission system.** No Gates, no Policies, no `spatie/laravel-permission`. Role
  separation is purely which routes each role may reach (`routes/web.php:464`, `:491`,
  `:501`). The brief's "assistant with restricted permissions" (§2.1) therefore means
  *route access*, and the "assistant permission preset" that §3.3 copies on conversion
  **does not exist as data** — UNKNOWN what should be copied instead; my suggestion is
  `doctors.assistant_limit` plus clinic assignment.
- **One user, one doctor.** An assistant is pinned to one clinic via `users.clinic_id`
  (`Doctor::activeClinic()`, `app/Models/Doctor.php:73-86`); a doctor switches clinics
  through `session('practice.clinic_id')` (`Doctor::ACTIVE_CLINIC_SESSION_KEY`,
  `app/Models/Doctor.php:62`; written by `Clinic\ClinicController::switchClinic():28`).
  **Note for Appendix B.4's role switch:** the active clinic lives in the session, so a
  role switch that re-logs-in must preserve or re-establish that key.
- **Login redirect** — `Auth\LoginController:109-131` picks the landing route by account
  type; assistants go to `practice.assistant.dashboard` (`:128`), clinic doctors to
  `doctor.dashboard` (`:124`). The demo's `redirect_url` should match this logic.

### Pre-existing IDOR (Appendix B.9 — confirmed)

`Clinic\PatientController::show(Request, Client $patient)` (`:17`) and
`update(Request, Client $patient)` (`:51`) take a route-model-bound `Client` and perform
**no ownership check whatsoever** before loading and editing the record. Every other
method in the same file calls `authorizeAppointment()` (`:113`). Any authenticated clinic
user can read or edit any patient in the global `clients` table by id. Live exposure of
real patient records today. Route: `routes/web.php:470-471`. Agreed it is a standalone
hotfix, not part of this feature.

---

## Q4. External side effects — exhaustive call-site list

**There is no SMS, no WhatsApp, no payment gateway, no pharmacy/lab partner API, no
video provider, no calendar or social integration, and no outbound webhooks in this
codebase.** `composer.json` contains no such SDK. The complete inventory:

### Push (FCM) — the only live outbound channel

Transport: `App\Notifications\Channels\FcmChannel`. It builds
`(new Kreait\Firebase\Factory)->withServiceAccount($serviceAccountPath)` **inline, per
send** (`FcmChannel.php:128`) — not injected, not bound in the container. `CloudMessage`
is dispatched at `FcmChannel.php:180` and `:183`. Credentials:
`storage/app/firebase-service-account.json` plus `FCM_*` in `.env`.

Selection happens in `BaseNotification::via()` (`app/Notifications/BaseNotification.php:47-68`),
which appends `FcmChannel::class` when the notification sets `$sendPush = true`.
**That method is the single cleanest interception point for the whole channel.**

*Reachable from the clinic system (`practice.*`):*

| # | Call site | Notification | Target |
|--:|---|---|---|
| 1 | `Clinic\AssistantDashboardController.php:31` | `PrintQueueTicketNotification` (`$sendPush=true`, `$storeInDatabase=false`, `mobile_only`) | clinic staff phones — drives the Bluetooth ticket printer |
| 2 | `Clinic\KioskController.php:112` | `PrintQueueTicketNotification` | same |
| 3 | `Notifications/PrintQueueTicketNotification.php:42` | `sendToClinicStaff()` fan-out | each staff `User` |
| 4 | `Clinic\ClinicNotificationController.php:64` | `ClinicBroadcastNotification` (`$sendPush=true`, `$storeInDatabase=true`) | the clinic's patients (`Client`s) |

*Reachable from the rosheta side and touching a demo doctor:*

| # | Call site | Notification |
|--:|---|---|
| 5 | `ClientDoctorReservationController.php:125` | `DoctorAppointmentBookedNotification` → `$doctor->user` |

*Not reachable from a demo request (marketplace), listed for completeness:*

`Api\ClientRequestController.php:162`, `:281` · `Api\OrderController.php:69` ·
`ClientNurseRequestController.php:303`, `:522`, `:551` · `ClientOfferController.php:210` ·
`ClientQuoteController.php:72`, `:78` · `ClientTestRequestController.php:215`, `:220`,
`:232`, `:246` · `LaboratoryOfferController.php:98`, `:156`, `:349` ·
`LaboratoryQuoteController.php:106` · `NurseOfferController.php:185` ·
`NurseVisitController.php:40`, `:79` · `OfferController.php:167` ·
`PharmacyOrderController.php:92`, `:134`

*Console commands (manual only):* `SendTestNotificationToClients.php:87` ·
`SendTestNotificationToUsers.php:114` · `TestMobilePushNotification.php:128`

### Email — one call site in the entire application

`AuthResetController.php:33` → `Mail::to($user->email)->send(new ResetPasswordCodeMail($code))`.
`BaseNotification::$sendMail` defaults to `false` (`BaseNotification.php:20`) and no
notification overrides it. `MAIL_MAILER=log` neutralises this completely.

### SMS — none

`BaseNotification::$sendSms` exists (`:26`) but the channel body is commented out
(`BaseNotification.php:59-62`). Nothing sends SMS.

### Outbound HTTP — one call site, console only

`Console/Commands/FetchAllApiResults.php:234` →
`Http::asForm()->withHeaders(...)->post('https://dwaprices.com/routing.php', …)`
(`:25`). A manually-run medicine-price scraper (`api:fetch-all-results`). Not reachable
from any request; not scheduled.

### Real hardware — the Bluetooth ticket printer

`Api\PrinterStatusController` (`routes/api.php:57`) sets `clinics.printer_connected_at`;
`Clinic::hasConnectedPrinter()` (`app/Models/Clinic.php:69`) gates the UI. The printer
itself is driven by the FCM push above. Simulate by leaving `printer_connected_at` null,
or through the FCM stub.

### Payments — none

There is no gateway. `collections` records cash taken at the desk
(`Clinic\CollectionController`). Nothing to fake; the brief's "sandbox or simulated
success" row is inapplicable.

### Analytics / pixels — none

No Meta Pixel, no `gtag`, no GTM, no TikTok, no Hotjar, no Clarity anywhere in
`resources/views/`. Phase 3.4 is entirely greenfield on **both** the client and server
sides.

### Captcha — none

No Turnstile, reCAPTCHA or hCaptcha in `app/`, `resources/`, `config/` or
`composer.json`. `DEMO_TURNSTILE_SECRET` is greenfield.

### Rate limiting — none

No `RateLimiter::for(...)` registration and no `throttle:` middleware on any route.
The brief's per-IP caps are greenfield.

---

## Q5. Existing seeders / factories

### `app/Services/ClinicOnboardingService.php` — 1,077 lines, already ~80% of the seeder

Production code, used by `Admin\ClinicOnboardingController` (`routes/web.php:423-424`).
`onboard()` (`:58`) wraps `run()` (`:74`) in a single `DB::transaction` and, on any
throwable, deletes every file written during the attempt (`:64-71`). It creates:

doctor `User` + `Doctor` + `Clinic` + the clinic↔doctor pivot with prices + working hours
in all three representations (`seedWorkingHours():188`) + assistant `User` +
`billable_items` (`:275`) + `medical_plans` (`:297`) + `examination_fields` (`:343`) +
patients (`:365`) + past visits (`:409`) + today/upcoming appointments with a lifelike
status mix (`:440`, `statusFor():494`) + diagnoses and prescriptions (`:528`) +
`medical_requests` (`:556`) + `patient_tests` **with a generated PDF** (`:583`,
`attachGeneratedPdf():915`) + `examination_field_values` (`:639`) + collections, extras
and insurance splits (`seedMoney():667`) + marketplace lab results (`:747`).

Parameters already exposed: `patients_count`, `appointments_per_day`, `days_ahead`,
`history_visits`, `seed_history`, `seed_demo`, prices, `open_from`/`open_to`,
`closed_days`.

**Gaps for the brief** (all additive, per Appendix B.6 — compose, do not modify):

1. **Times are day-relative**, not `T0`-relative — `Carbon::today()` plus fixed slot
   strings. The brief needs a visit in progress at `T0−12m`, a queue at `T0+20m`, and
   working hours spanning `T0−3h → T0+5h`.
2. **Not deterministic** — `Str::random()`, `fake()`, `uniquePhone():1048`. Row-count
   snapshot tests need a seeded RNG.
3. **Not template-driven** — content is hard-coded in `clinicalTemplates():831`,
   `seedBillableItems():275`, `insurers():979`.
4. **`seedMarketplaceLabResults():747`** writes `client_requests`, `offers`,
   `offer_lines` and selects a real `Laboratory` — so `laboratories`, `medical_tests`
   and `medicines` must exist on the demo connection, or that step must be skippable.
5. **`attachGeneratedPdf():915`** writes to `Storage::disk('public')` under
   `test-results/` with **no tenant prefix** (`RESULTS_DIR`, `:46`).

### Other reusable assets

- `database/seeders/ClinicDemoSeeder.php` — smaller and **idempotent** (rebuilds one
  doctor's appointments on each run). A good model for the `reset` endpoint.
- `database/seeders/ChatDemoSeeder.php`, `DoctorsSeeder.php`, `SpecializationsSeeder.php`,
  `EgyptLocationSeeder.php` (governorates/cities/areas — useful for seeding the demo
  database's reference tables).
- **Factories present:** `Client`, `User`, `Area`, `City`, `Governorate`, `Pharmacy`,
  `Offer`, `OfferLine`, `Medicine`, `ClientAddress`, `ClientRequest`,
  `ClientRequestLine`.
- **Factories missing and required:** `Doctor`, `Clinic`, `Appointment` — plus
  `Diagnosis`, `Prescription`, `Collection`, `BillableItem` if tests build fixtures
  directly. Note `Doctor` and `Clinic` use `HasFactory` but have no factory class, so
  `Doctor::factory()` currently throws.

---

## Q6. Public / aggregate queries that would expose demo tenants

Listed so the isolation decision can be verified. A separate demo database makes all of
them safe automatically.

| Query | Location | Filter today |
|---|---|---|
| Public doctor/clinic booking search — `Clinic::with(...)->paginate(12)` | `ClientDoctorReservationController.php:23-42` | governorate / city / area / specialisation only |
| Clinic detail + slot availability | `ClientDoctorReservationController.php:86`, `:141`, `:175-184` | none |
| Admin dashboard counts (`Doctor::count()`, `Clinic::count()`, `Client::count()`, `User::count()`) | `Admin\DashboardController.php:27-41` | none |
| Filament resources — `Clients`, `Users`, `Offers`, `Orders`, `ClientRequests` | `app/Filament/Resources/*` | none |
| Filament widgets — `StatsOverview`, `BlogPostsChart` | `app/Filament/Widgets/*` | none |
| Admin clinic/doctor CRUD | `Admin\ClinicController`, `Admin\DoctorController` | none |

**No search index** — no Scout, Elastic, Meilisearch or Algolia in `composer.json`.
**No sitemap. No export or reporting jobs.** So §1.2's "assert demo tenants are never
indexed" has nothing to assert against (Appendix B.7 already drops it).

---

## Q7. Deletion, cascades and files

118 foreign keys; cascades are mostly present and correct. **No model uses
`SoftDeletes`** (verified across `app/Models/`), so deletes are already hard — the
brief's rule 3 is satisfied for free.

### The seven gaps a purge must close explicitly

1. **`users`** — `doctor_id` and `clinic_id` are both `ON DELETE SET NULL`. Deleting the
   tenant leaves the doctor's and assistant's logins behind, detached but alive.
2. **`attachments`** — `appointment_id` is `SET NULL`; `attachable_id`/`attachable_type`
   has **no FK**. Row *and file* survive. Add `clinic_id`.
3. **`patient_tests`** — `appointment_id` and `doctor_id` both `SET NULL`; only
   `client_id` cascades. Row and file survive if the patient is deleted last.
4. **`examination_field_values.attachment_id`** — `SET NULL`; can orphan a file.
5. **`notifications`, `media`, `personal_access_tokens`, `sessions`** — polymorphic or
   FK-less. Nothing cascades; all must be deleted by hand.
6. **`reviews.client_id` has no FK** — orphans on patient delete.
7. **`offers.client_request_id` and `orders.client_request_id` are `NO ACTION`** —
   deleting a demo patient who has a seeded marketplace lab result **fails with MySQL
   error 1451** unless `offers`/`orders` are deleted first. This is a real ordering trap
   because `ClinicOnboardingService::seedMarketplaceLabResults()` creates exactly that
   shape.

### Files have no tenant prefix

Every upload path is hard-coded, and so is the disk:

| Path | Call site |
|---|---|
| `attachments/` | `Clinic\AttachmentController.php:33`; delete at `:54` |
| `attachments/` | `Clinic\ExaminationValueController.php:33` |
| `test-results/` | `Clinic\PatientTestController.php:45`; delete at `:68` |
| `test-results/` | `ClinicOnboardingService.php:46` (`RESULTS_DIR`), written at `:921` |
| `requests/`, `clients/avatars/` | marketplace controllers |

There is **no `demo/{doctor_id}/` layout to delete by prefix**. Either (a) change these
five call sites to a disk/prefix resolved from `DemoContext`, or (b) purge files by the
`file_path` values on the `attachments` / `patient_tests` rows being deleted. I
recommend (a) for the guarantee and (b) as a belt-and-braces sweep.

### Proposed delete order (children → parents)

```
examination_field_values, appointment_items, appointment_insurances, collections,
prescription_items → prescriptions, medical_requests, diagnoses,
patient_tests (file then row), attachments (file then row),
offer_lines → offers, order_lines → orders, client_request_lines → client_requests,
home_nurse_requests,
appointments,
chat_messages → conversations, medical_plan_items → medical_plans,
billable_items, examination_fields, insurance_collections, doctor_off_dates,
clinic_doctor_working_hours, clinic_working_hours, clinic_doctor,
notifications (by notifiable), media (by model), personal_access_tokens (by tokenable),
sessions (by user_id), reviews (by client_id), bonus_points, feedbacks, quotes,
client_addresses,
clients, users, clinics, doctors
```

Per Appendix A.8, do **not** disable `FOREIGN_KEY_CHECKS`; assert the order with the
dynamic test from B.1 that enumerates tables from `information_schema` on the demo
connection and **fails on any table it cannot classify**.

---

## Q8. Multi-connection readiness — and the exact middleware order

### Verdict: the connection switch itself is unobstructed

- **No tenancy package installed** (`composer.json`) — nothing to integrate with.
- **Zero hard-coded connections in application code.** Grepping `app/` and `database/`
  for `DB::connection(`, `Schema::connection(`, `setConnection(`, `protected $connection`
  yields exactly **one** hit:
  `database/migrations/2026_01_06_150625_create_telescope_entries_table.php:22,64` —
  vendor-generated, and correct (`Schema::connection($this->getConnection())`).
- **No raw `DB::statement` or `DB::select` in `app/`.** All raw SQL is six `selectRaw()`
  calls inside query builders, which follow the model's connection automatically:
  `Clinic\ManagerController.php:176`, `:186` · `Concerns\SerializesChat.php:114`, `:140` ·
  `Api\LaboratoryController.php:64` · `Widgets\BlogPostsChart.php:21`.
- **No cross-database JOINs, no stored procedures, no views.**
- **Migration hacks that use MySQL-only raw DDL** — five files:
  `2026_01_12_205731_…`, `2026_01_12_213633_…`, `2026_01_26_190000_…`,
  `2026_01_26_200000_…`, and `2026_07_01_000003_extend_appointments_for_clinic_workflow.php`
  (which uses `ALTER TABLE … MODIFY`, `SHOW INDEX`, `CREATE INDEX`, and a backfill
  `UPDATE`). **These are safe under `migrate --database=demo`** — see Q0's A.6 verdict.
  They *do* mean the test database must be MySQL.

### Resolved middleware order (Appendix A.3 / B.3)

Dumped from the live container, not read from source:

**Global stack** (runs before any group)
```
 1. Illuminate\Http\Middleware\ValidatePathEncoding
 2. Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks
 3. Illuminate\Http\Middleware\TrustProxies
 4. Illuminate\Http\Middleware\HandleCors
 5. Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance
 6. Illuminate\Http\Middleware\ValidatePostSize
 7. Illuminate\Foundation\Http\Middleware\TrimStrings
 8. Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull
 9. Livewire\...\DisableBackButtonCacheMiddleware
```

**`web` group**
```
 1. Illuminate\Cookie\Middleware\EncryptCookies
 2. Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse
 3. Illuminate\Session\Middleware\StartSession      ← the line the demo switch must precede
 4. Illuminate\View\Middleware\ShareErrorsFromSession
 5. Illuminate\Foundation\Http\Middleware\ValidateCsrfToken
 6. Illuminate\Routing\Middleware\SubstituteBindings
 7. App\Http\Middleware\SetLocale
 8. App\Http\Middleware\MostashfaOnClientContext
```

**`api` group**
```
 1. Illuminate\Routing\Middleware\SubstituteBindings
 2. App\Http\Middleware\MostashfaOnClientContext
```

**`middlewarePriority`** (the re-sort applied to route middleware)
```
 1. Sanctum\EnsureFrontendRequestsAreStateful   7. AuthenticatesRequests
 2. HandlePrecognitiveRequests                  8. ThrottleRequests
 3. EncryptCookies                              9. ThrottleRequestsWithRedis
 4. AddQueuedCookiesToResponse                 10. AuthenticatesSessions
 5. StartSession                               11. SubstituteBindings
 6. ShareErrorsFromSession                     12. Authorize
```

**Reading of this for B.3:** a demo middleware **prepended** to the `web` group would
sit at position 0, ahead of `EncryptCookies` and `StartSession`. `SortedMiddleware` only
reorders middleware that appear in `middlewarePriority` **relative to each other**, and
our middleware would not be in that list, so it should keep position 0. Note also that
`clinic.role` (`EnsureClinicRole`) is **not** in the priority list, so it sorts after
`SubstituteBindings` — which is why `$request->attributes` already has the bound models
when it runs. **I am reasonably confident but not certain** that no priority interaction
moves `StartSession` ahead; this is precisely what the B.8 step-0 spike must prove
empirically rather than by reading `SortedMiddleware`.

### The four subsystems that follow the connection flip

This is the part Appendix A.7 understates, and the reason Appendix B.3 exists.

| Subsystem | Config | Why it follows the flip |
|---|---|---|
| **Sessions** | `SESSION_DRIVER=database`; `config/session.php:76` `'connection' => env('SESSION_CONNECTION')` — **unset** → `null` → default connection | The session store resolves the connection lazily. Flipping `database.default` mid-request moves the session table under the request's feet |
| **Cache** | `CACHE_STORE=database`; `config/cache.php:44` `'connection' => env('DB_CACHE_CONNECTION')` — **unset** | Same |
| **Queue** | `QUEUE_CONNECTION=database`; `config/queue.php:40` `'connection' => env('DB_QUEUE_CONNECTION')` — **unset**. `job_batches`/`failed_jobs` use `env('DB_CONNECTION','sqlite')` (`:102`, `:121`) | Same for `jobs`; batches/failed are pinned |
| **Telescope** | `config/telescope.php:62` `'connection' => env('DB_CONNECTION','mysql')` | **Pinned to production** — not a correctness problem, but every demo query is logged into a table already holding 251k rows. Recommend `TELESCOPE_ENABLED=false` on the demo host |

Appendix B.3's decision — detect demo from the **URL path**, run before `StartSession`,
and give the request its own session store, cache prefix, queue and cookie name — is the
right resolution, and keeps the production write allow-list at exactly two tables.

### ⛔ Blocker: the schema cannot currently be rebuilt from migrations

Verified empirically, not inferred. I created a throwaway database and ran the suite
against it:

```
DB_DATABASE=rosheta_migratecheck php artisan migrate --force
→ 2025_11_25_000100_create_medical_test_offers_table ......................... FAIL
  SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'medical_tests'
```

It fails at **migration 18 of 132**, having created only 26 tables. (Throwaway database
dropped afterwards; nothing else was touched.)

**Cause.** `migrations` holds **138** rows against **132** files on disk — six migrations
ran in production and their files were later deleted:

```
0001_01_01_000003_create_medicines_table
2025_11_09_100000_create_pharmacy_agents_table
2025_11_25_000402_add_images_to_medical_test_requests_table
2025_11_25_000500_create_medical_test_offer_lines_table
2026_01_01_000500_update_nurses_and_clients_add_status_area_nurse_id
2026_06_19_000001_add_integration_key_to_clinics_table
```

Nothing on disk creates **`medical_tests`** (682 rows in production), yet
`2025_11_25_000100_create_medical_test_offers_table` declares an FK to it. Its real DDL:

```sql
CREATE TABLE `medical_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `test_name_en` text NOT NULL,
  `test_name_ar` text NOT NULL,
  `test_description` text,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `conditions` varchar(255) DEFAULT NULL,
  `type` enum('test','radiology') NOT NULL DEFAULT 'test',
  PRIMARY KEY (`id`),
  KEY `medical_tests_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
```

Drift also runs the other way: `medical_test_requests` and `medical_test_request_lines`
have create-migrations recorded as **run**, but the tables **do not exist** in `rosheta`
— so a rebuilt database is a superset of production in one place and a subset in another.

**Why this matters here.** It directly blocks brief §1.0 ("run the **same** migrations
against it… so its structure never drifts from production"), B.8 step 1 (a
migration-built `rosheta_test`) and B.8 step 2 (`migrate --database=demo`). Both new
databases are created by exactly the command that fails.

**Options, cheapest first:**

1. **Restore the missing migration(s)** — write `create_medical_tests_table` with the DDL
   above, dated before `2025_11_25_000100`, and mark it as already run in production
   (`INSERT INTO migrations`). Check the other five deleted files for further gaps the
   same way. This repairs the pipeline permanently and is a prerequisite for CI.
2. **Build both databases from `mysqldump --no-data rosheta`** and stop claiming
   migrations are the source of truth. Fast, but the demo schema then drifts silently the
   first time someone adds a migration.

I recommend (1) — everything downstream depends on it. Note `db.sql` (48 MB) and
`medicines.sql` (51 MB) sit in the repo root, which suggests the production database was
partly built by import rather than migration.

**The deleted files are not recoverable from git.** Searching all branches
(`git log --all --diff-filter=AD`) finds only one of the six —
`2026_06_19_000001_add_integration_key_to_clinics_table` (commit `9c9da71`). The other
five were never committed: they were created, run, and deleted without ever being
tracked. So the repair must be **reconstructed from the live schema** via
`SHOW CREATE TABLE`, which is why step 1a carries real uncertainty rather than being a
`git checkout`.

### Queued jobs (Appendix A.5)

Currently theoretical: no `app/Jobs/`, no `dispatch()`. The only queue traffic is
`BaseNotification implements ShouldQueue`, so **any notification sent during a demo
request is already a job that loses the connection context**. The `InteractsWithDemoTenant`
trait from A.5 is needed the moment the demo sends its first simulated notification.

---

## Q9. Reference tables

| Table | Rows | FK target from a demo row? | Needed by |
|---|--:|:--:|---|
| `specializations` | 35 | **yes** — `doctors.specialization_id` | tenant creation |
| `governorates` | 27 | **yes** — `clinics`, `clients` | clinic address |
| `cities` | 102 | **yes** — `clinics`, `clients` | clinic address |
| `areas` | 102 | **yes** — `clinics` | clinic address |
| `insurance_companies` | 4 | **yes** — `appointment_insurances`, `insurance_collections` | money screens |
| `laboratories` | 8 | **yes** — `offers`, `medical_test_offers` | `seedMarketplaceLabResults()` |
| `medicines` | 22,910 | no | `/api/medicines/search` autocomplete |
| `medical_tests` | 682 | **yes** — `client_request_lines`, `offer_lines` (both **RESTRICT**) | lab/radiology picker |

≈ 23.9k rows, dominated by `medicines`. All are effectively static, so a daily
`DEMO_REFERENCE_SYNC_CRON` is defensible but low-value; weekly would do.

**Two traps:**

1. `medical_tests` uses collation `utf8mb4_0900_ai_ci` while every other table uses
   `utf8mb4_unicode_ci`. Reproduce it per-table in the demo database or joins throw
   *Illegal mix of collations*.
2. `medicines` is not an FK target, so it *could* stay a production read — but
   `/api/medicines/search` (`routes/api.php:24`) is a **public, unauthenticated** route
   outside the demo path, which means it reads production `medicines` regardless of what
   we mirror. Mirroring it only matters if the demo prescription screen is changed to
   query through the demo connection. **Recommendation: mirror all eight anyway.** 23.9k
   rows is nothing, and a uniform rule ("the demo database is self-sufficient") is worth
   more than the saved bytes. Appendix B.2's refusal assertion covers the safety side.

---

## Q10. Recommendation

### 10.1 Isolation mode: `tenant` **inside a separate demo database**

Design-doc §2.1 is not available to me (see the header note), so I am choosing from the
three modes as the brief names them. The answer is the one Appendix A.1 reached, for the
reason Appendix B.2 gives:

- **`schema` is unavailable** — MySQL has no schema namespace distinct from a database.
- **`database`-per-visitor is too heavy** — creating and migrating a database per ad
  click, with 132 migrations, is not viable at the brief's 500 concurrent demos.
- **`tenant` inside production is disqualified by `clients`** — patients are a global
  table with no tenant key (Q2 row 26). A tenant-mode demo would mint real rosheta
  patient accounts with logins, visible in `/admin/clients`, to the patient app, and to
  the pharmacy/lab marketplace. Excluding them would mean adding a column to `clients`
  and auditing ~20 call sites — the highest-risk item in brief v1.0, and unnecessary.

So: **one demo database, one `doctors` row per visitor**, exactly as Appendix B.

### 10.2 Minimum schema changes for Phase 1

1. **`doctors`** — `is_demo BOOLEAN NOT NULL DEFAULT false` (indexed),
   `demo_expires_at TIMESTAMP NULL` (indexed), `demo_last_activity_at TIMESTAMP NULL`,
   `demo_template_key VARCHAR NULL`, `demo_source JSON NULL`. Demo database only; adding
   them to production too keeps the two schemas identical, which the migration story
   wants — do that.
2. **`demo_sessions`** — on **production**, keyed `doctor_id` / `converted_doctor_id`
   (B.1), model pinned `protected $connection = 'mysql'`.
3. **`demo_leads`** — on production, same pinning.
4. **`attachments.clinic_id`** — nullable FK, `ON DELETE CASCADE`. The only tenant-scoped
   table with no hard path to the tenant, and it owns files (Q7 gap 2).
5. **Optional but recommended:** change `patient_tests.appointment_id` / `.doctor_id`
   from `SET NULL` to `CASCADE`, or accept an explicit purge step for it.

Nothing is needed on `clients` — in a separate database, demo patients simply live there.

### 10.3 What makes per-visitor tenants hard here, and the way around it

| Difficulty | Way around |
|---|---|
| Tenant identity is split across `doctors` **and** `clinics`, with five tables keyed only by `doctor_id` | Treat `doctors` as the tenant; purge from `doctor_id` and `clinic_id` (B.1) |
| Patients are global | Separate database (B.2) |
| Session/cache/queue all follow the connection flip | URL-prefix detection + own session store, before `StartSession` (B.3) |
| Auth is session-based, brief assumes tokens | `Auth::login()` + redirect (B.4) |
| Files are not tenant-prefixed, disk hard-coded in 5 places | Resolve disk+prefix from `DemoContext`; sweep by `file_path` as backup (Q7) |
| No test harness can run at all | Real MySQL test database (B.5) — **do this first** |
| `Doctor`/`Clinic`/`Appointment` factories do not exist | Write them as part of the harness |
| `offers`/`orders` `NO ACTION` FKs block patient deletion | Fixed delete order (Q7) |
| Seeder is day-relative and random | Compose over `ClinicOnboardingService` with a `T0` resolver + seeded RNG (B.6) |

### 10.4 Effort estimate for Phase 1

Assuming one developer working with Claude Code, and that the B.8 order is followed.
Ranges reflect my confidence, not padding.

| B.8 step | Estimate | Confidence |
|---|---|---|
| 0. Spike — middleware before `StartSession`, own session/cache/queue | **0.5–1 day** | high — it either works in an afternoon or it reveals a blocker, which is the point |
| 1a. **Repair the migration pipeline** (Q8 blocker — restore `create_medical_tests_table`, audit the other five deleted files) | **0.5–1 day** | medium — one table is confirmed missing; the other five deleted migrations are unaudited |
| 1b. Test harness: MySQL test DB, create/migrate script, `Doctor`/`Clinic`/`Appointment` factories | **1–2 days** | medium — expect further ordering bugs once 1a unblocks the run past migration 18 |
| 2. Second connection, storage disk, cache prefix, queue, reference sync + refusal assertion | **1–1.5 days** | high |
| 3. `DEMO_PROD_WRITE_GUARD`, preventive, + tests through real flows | **1–1.5 days** | medium — the preventive form needs a custom `MySqlConnection` subclass and a connection resolver override; ~half a day of that is framework spelunking |
| 4. Schema: demo columns on `doctors`, `demo_sessions`/`demo_leads` pinned to production | **0.5 day** | high |
| 5. `POST /api/demo/start`, `web`-guard flow, `DemoSeeder` over `ClinicOnboardingService`, minimal template | **2–3 days** | medium-low — the `T0` resolver and making a randomised 1,077-line service deterministic is the bulk of it |
| 6. Purge, both paths, + dynamic completeness test | **1.5–2 days** | medium |
| | **≈ 8.5–12.5 working days** | |

Add ~1 day if the spike forces a different approach, and more if step 1a uncovers gaps
from the other five deleted migrations beyond `medical_tests`. This excludes the B.9 IDOR hotfix
(a few hours, separate branch) and excludes Phase 2.

### 10.5 What I am least sure about

Ranked by how much a wrong guess would cost:

1. **That a prepended middleware reliably runs before `StartSession`.** I read
   `SortedMiddleware`'s algorithm and believe it holds, but I have not executed it. The
   entire two-table allow-list guarantee rests on this. **This is why B.8 step 0 exists;
   do not skip it.**
2. **Making `ClinicOnboardingService` deterministic without modifying it.** B.6 forbids
   changing its behaviour, but `Str::random()` and `fake()` are called deep inside
   private methods. Seeding the global RNG (`mt_srand`, `fake()->seed()`) from the
   outside may be enough — or it may not, and then B.6's "add a parameter with a default
   that preserves today's behaviour" is the fallback. **I would validate this on day one
   of step 5** rather than discover it late.
3. ~~Whether 132 migrations run cleanly on an empty MySQL database.~~ **Resolved — they
   do not.** Confirmed by running them (Q8 blocker). What remains uncertain is the
   *size* of the repair: I proved `medical_tests` is missing, but I did not reconstruct
   what the other five deleted migrations did, so there may be further gaps past
   migration 18. Step 1a is scoped on the optimistic reading.
4. **MySQL version and whether the demo database will live on the same server.** Affects
   the collation reproduction (Q9) and connection-pool limits under 500 concurrent demos.
5. **What "assistant permission preset" means for §3.3's conversion copy**, given no
   permission system exists (Q3).

---

## Q11. Reconciliation with `DESIGN_AR.md` (v1.1)

The design doc predates the Phase 0 findings, so parts of it are already answered and
parts are contradicted by the code. Nothing below changes the architecture — the doc's
central decision is correct — but several content requirements need adjusting before
Phase 2.

### 11.1 Its open questions (§14), answered by Phase 0

| # | Question | Answer |
|---|---|---|
| 0 | PostgreSQL or MySQL? | **MySQL** (`DB_CONNECTION=mysql`, InnoDB). So §2.1's pattern 1 (schema-per-session) is **unavailable**, and pattern 2 (database-per-session) is impractical at 500 concurrent demos with 132 migrations. **Pattern 3 — tenant inside one demo database — is the only viable option**, which is what the doc recommends starting with anyway |
| 1 | Is everything keyed by `clinic_id` or `doctor_id`? | **`doctor_id`.** Five clinical/financial tables have no `clinic_id` at all (Q2). The purge must walk both paths. This is Appendix B.1 |
| 2 | `mostashfaon.com/demo` or `demo.mostashfaon.com`? | Still yours. Appendix B.3 requires demo detection from **path or host before `StartSession`** — either satisfies it. Path-prefix is simpler on Laragon and for cookies; I lean the same way the doc does |
| 3 | Copy clinic settings on conversion? | Still yours, but note §3 above: the "assistant permission preset" the brief wants to copy **does not exist as data** — there is no permission system. Copyable today: clinic name, address, phone, working hours, `appointment_duration`, the two visit prices, and `billable_items` |
| 4 | Which specialties in phase 1? | Still yours. `specializations` has 35 rows, so any choice is seedable |
| 5 | Where do leads go? | Still yours — no CRM, webhook or WhatsApp Business integration exists in the code |

### 11.2 Factual corrections to the design doc

| Doc says | Reality |
|---|---|
| §12.1 "DST غير موجود في مصر" (no DST in Egypt) | **Wrong since April 2023.** `Africa/Cairo` has **8 transitions in 2023–2026**; today is `EEST +03:00` and the next change is **2026-10-29**. See 11.3 — this is a live bug risk in the seeder, and the doc explicitly tells us to skip testing it |
| §2.1 `DEMO_DB_PORT=5432` | PostgreSQL's port. MySQL: **3306** |
| §2.1 `DEMO_REFERENCE_TABLES=medications,icd_codes,lab_tests,specialties,governorates,cities` | **None of these table names exist.** The real set is `medicines`, `medical_tests`, `specializations`, `governorates`, `cities`, **`areas`**, **`insurance_companies`**, **`laboratories`** (Q9). There are **no ICD codes** and **no subscription-plans table** in this application at all |
| §4.2 `demo_sessions.clinic_id` / `converted_clinic_id` | `doctor_id` / `converted_doctor_id` (Appendix B.1) |
| §6.1 `DEMO_ACCESS_TOKEN_TTL`, §6.2 step 2, §9, §10 (token-based) | The clinic system is **session/`web` guard**; no tokens (Appendix B.4) |
| §4.4 "lifecycle rule حذف بعد 48 ساعة" | An S3 concept. Storage here is a **local disk**; this becomes a scheduled cleanup command |
| §7 rows for SMS, WhatsApp, payment gateway, pharmacy/lab partner APIs, calendar/social integrations | **None of these integrations exist** (Q4). DemoGuard reduces to FCM + `MAIL_MAILER=log` (Appendix B.7) |
| §1.3 / §7 "شاشة محاكاة للاتصال المرئي" (mock video call) | There is **no telemedicine feature** to mock. Nothing to build or stub — the "كشف أونلاين" service in §5.3 is just a price-list row, which is fine |
| §13 Phase 1 estimate: 4–6 days | My estimate is **8.5–12.5 days** (10.4). The delta is the migration-pipeline repair and the test harness, neither of which the doc could have known about |

### 11.3 The DST finding, concretely

Verified with `DateTimeZone::getTransitions()` on this PHP install:

```
2025-04-24 → EEST +03:00      2026-04-23 → EEST +03:00
2025-10-30 → EET  +02:00      2026-10-29 → EET  +02:00
```

§5.2's offsets (`D-45`, `D+10`) will straddle a transition for any demo started within
~6 weeks of late October or late April. **Calendar arithmetic is safe; epoch arithmetic
is not.** `Carbon::copy()->addDays($n)->setTimeFromTimeString($t)` gives the intended
wall-clock time; `$t0 + $n * 86400` silently shifts by an hour across the boundary.
`ClinicOnboardingService` already uses the safe form
(`$date->copy()->setTimeFromTimeString(...)`), so the requirement is simply: **keep it
that way in the `T0` resolver, and add a DST-boundary case to the unit test** that §12.1
tells us to skip.

### 11.4 Template items in §5.3 that this application cannot represent

Each of these needs a decision before Phase 2 — build the capability, or change the
template.

| §5.3 item | Problem |
|---|---|
| Assistant "بدون رؤية الملاحظات الطبية" (cannot see medical notes) — explicitly there to **showcase the permission system** | **There is no permission system** (Q3). The assistant's limits are route-level only. Either build a real capability check for medical notes, or drop this from the template and stop advertising a feature that does not exist |
| "فاتورة **مدفوعة** (نقدي/فيزا/محفظة)" — cash / card / wallet | **`collections` has no payment-method column** — only `amount`, `collected_by`, `collected_at`, `note`. The method can go in `note` as free text, or a column must be added |
| "تقييم من مريض 5 نجوم" (a 5-star patient review) | **`reviews` cannot target a doctor or clinic.** `reviewable_type` in use is `Laboratory`, `NurseVisit`, `Pharmacy`; neither `Doctor` nor `Clinic` declares a `reviews()` morph relation. Nowhere to put it |
| "طلب تعديل موعد" (a reschedule request) | **No such feature exists** — no reschedule-request table, model or route |
| "إشعار حقيقي بعد دقيقة (`T0+60s`)" | Feasible, but needs a **delayed queued job on the demo queue and a worker running for it** (Appendix A.5). No worker runs today; `app/Jobs/` does not exist |
| "شعار افتراضي" (default clinic logo) | `clinics` has no logo column; media-library collections are registered on `Doctor` and `User`, not `Clinic` |

Items that map cleanly and need no change: the clinic, the 5 priced services
(`billable_items` + the two clinic prices), 18 patients, 12 completed visits with
diagnosis/prescription/lab result, the in-progress visit at `T0−12m`
(`status='under_examination'`), today's waiting queue, 6 upcoming appointments, the
no-show (`status='missed'`), 2 unpaid invoices (`remainingAmount() > 0`), the penicillin
allergy alert (`clients.allergies`), and non-zero monthly revenue.

---

## Open questions for Helmi

1. **Six items in the `general_v1` template cannot be represented by this application**
   (Q11.4) — most importantly the assistant permission restriction, which exists in the
   template specifically to showcase a permission system that **does not exist**. For
   each: build the capability, or cut it from the template? My recommendation is to cut
   the review, the reschedule request and the assistant restriction from `general_v1`,
   put the payment method in `collections.note`, and keep the `T0+60s` notification
   (it needs the demo queue worker anyway).
2. **The migration pipeline is broken** (Q8 blocker) — `migrate` on an empty database
   dies at migration 18 because six migration files were deleted after running. Do you
   want me to repair it by restoring the missing migrations (my recommendation, ~0.5–1
   day, and a prerequisite for CI), or to build `rosheta_test` / `rosheta_demo` from
   `mysqldump --no-data` and accept the drift? Five of the six deleted files were never
   committed to git — **do you have them in a local backup?** If not, I reconstruct from
   `SHOW CREATE TABLE`.
3. **May I create `rosheta_test` and `rosheta_demo` locally** and repoint `phpunit.xml`,
   or should tests run only against a MySQL container in CI?
4. **Reference data** — confirm mirroring all eight tables including 22,910 `medicines`
   (my recommendation, Q9), rather than only the six FK targets.
5. **`/demo` URL prefix vs `demo.` subdomain** for B.3's path-based detection — the
   brief mentions both. Path-prefix is simpler locally on Laragon; the subdomain is
   cleaner for cookie isolation. Which do you want for Phase 1?

---

*Phase 0 complete. No Phase 1 work started.*
