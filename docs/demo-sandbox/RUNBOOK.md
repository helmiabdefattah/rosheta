# Demo Sandbox — Runbook

How to set up, use, operate and disable the doctor/assistant demo environment.

---

## 1. What it does

A visitor presses a button on the login page and lands inside a **fully working
clinic that is live right now** — no signup, no password:

- **A patient on the chair**, examination started ~12 minutes ago.
- **A waiting queue** at roughly *now +20m*, *+45m*, *+75m*.
- **Two visits already finished** earlier today.
- **~15 past visits** going back about three months, each with a diagnosis, a
  prescription, lab/radiology requests and results, and a paid collection.
- **12 patients** with medical history, allergies and chronic conditions.
- **A clinic** with opening hours, prices, chargeable extras, treatment-plan
  templates and custom examination fields.
- **An assistant account**, switchable from the demo bar without a password.

Every date is computed from the moment the visitor pressed the button, so the
clinic is always mid-session — even at 2am, and even on a Friday.

When the demo ends, **every row, every file and every login is hard-deleted.**

---

## 2. One-time setup

### 2.1 Create the demo database

```bash
php artisan migrate            # adds demo_sessions + the demo columns on doctors
php artisan demo:setup         # creates rosheta_demo and fills reference data
```

`demo:setup` copies the **structure** of every production table using
`SHOW CREATE TABLE` (which preserves foreign keys), then copies **reference data
only**: specializations, governorates, cities, areas, insurance companies,
laboratories, medical tests and ~25k medicines. It takes a few minutes, mostly
on medicines.

It never copies patients, users, visits, prescriptions or invoices — the command
refuses those tables even if they are added to `DEMO_REFERENCE_TABLES`.

> **Why not `migrate --database=demo`?** Because it does not work today: six
> migration files were deleted after being run, nothing on disk creates
> `medical_tests`, and a fresh `migrate` dies at migration 18 of 132. See
> `DISCOVERY.md` §Q8. Once that is repaired, `demo:setup` can be replaced with a
> plain migrate.

Options: `--fresh` (drop and rebuild), `--structure-only` (skip reference data).

### 2.2 Turn it on

In `.env`:

```dotenv
DEMO_ENABLED=true
DEMO_DB_CONNECTION=demo
DEMO_DB_DATABASE=rosheta_demo
DEMO_PROD_WRITE_GUARD=true
```

`DEMO_DB_HOST/PORT/USERNAME/PASSWORD` fall back to the `DB_*` values. **In
production, create a dedicated MySQL user for the demo database with no
privileges on the production database** — defence in depth behind the write
guard.

Then: `php artisan config:clear`.

### 2.3 Run the scheduler (required)

Nothing else in this application is scheduled, so the scheduler is probably not
running. Expired demos are only cleaned up if it is:

```bash
# local (Laragon does not run the scheduler)
php artisan schedule:work

# server
* * * * * cd /path/to/rosheta && php artisan schedule:run >> /dev/null 2>&1
```

Without it, tenants accumulate in the demo database. They are still isolated and
harmless — just untidy — and `php artisan demo:purge` clears them at any time.

---

## 3. Using it

1. Open **`/login`**.
2. Under the sign-in form, **pick your specialty** from the dropdown.
3. Press **«جرّب كطبيب»** or **«جرّب كمساعد»**.
4. You land on the doctor or assistant dashboard, in Arabic, already populated
   with a clinic **for that specialty**.

A dark **demo bar** sits at the top (bottom on mobile) with:

| Control | What it does |
|---|---|
| countdown | time left before the hard expiry (default 4 hours) |
| **تحوّل إلى المساعد / الطبيب** | switches role inside the same clinic, no password |
| **أعد التجربة** | wipes the tenant and rebuilds it fresh, same session |
| **أنشئ حسابك الحقيقي** | goes to the real registration form |
| **إنهاء** | ends the demo and destroys everything immediately |

### Specialties

The dropdown is split into two groups:

- **عيادات مجهزة بمحتوى التخصص** — specialties with a purpose-built clinic.
  Everything is on-specialty: the services and their prices, the examination
  fields, the saved treatment plans, the diagnoses and prescriptions on past
  visits, the lab/radiology requests, and the reason each patient came in.
- **تخصصات أخرى** — everything else, which gets the general internal-medicine
  content. Still a complete, working clinic; just not tailored.

Built today: **أسنان** (also covers تقويم أسنان and جراحة فم وأسنان),
**أطفال**, **جلدية** (also حساسية ومناعة), **نساء وتوليد**, **عظام** (also
جراحة عظام، روماتيزم، علاج طبيعي), **قلب وأوعية دموية** (also جراحة قلب وصدر).
Six profiles covering 13 of the 36 specializations.

A dentist, for example, gets حشو عصب / خلع ضرس / تركيبة زيركون on the price
list, "رقم السن (نظام FDI)" and "حالة اللثة" on the examination form, and a
patient in the chair complaining of "ألم شديد بضرس العقل السفلي الأيمن".

### Adding a specialty — no code

Drop a JSON file into `resources/demo-templates/specialties/`. Copy an existing
one as the shape; `asnan.json` is the most complete. Keys:

| Key | Purpose |
|---|---|
| `label` | display name (also stored as the tenant's template key) |
| `slugs` | which `specializations.slug` values this file covers |
| `clinic_name` | `{doctor}` is replaced with the generated doctor name |
| `brief`, `prices` | doctor bio, examination and follow-up price |
| `services` | the chargeable extras price list |
| `examination_fields` | type must be `text`, `select`, `number`, `percentage` or `file` |
| `plans` | one-click treatment roadmaps |
| `cases` | diagnosis + treatment plan + medicines + requests, cycled over past visits |
| `reasons` | why patients came in, cycled over all appointments |

`requests[].type` must be `examination`, `lab_test` or `radiology`. The file is
validated on load — a missing `label`, empty `cases`, or a case without
`diagnosis` / `treatment_plan` / `medicines` throws immediately rather than
half-seeding a tenant.

Watch out for one constraint: `billable_items` has a unique
`(doctor_id, name)` index, so a service name that also appears in the generic
list is handled by a two-pass rename in `SpecialtyOverlay` — you do not need to
avoid such names, but that is why the rename is not a simple update.

### Things worth trying in the demo

- Open the patient under examination, write a prescription, finish the visit.
- Collect payment, then check **المدير → التحصيلات**.
- Switch to the assistant, check a patient in, take a payment.
- Change opening hours or prices in **إعدادات العيادة** — then look at the
  dashboard again.
- Press **أعد التجربة** and watch it all rebuild around the new "now".

---

## 4. How the isolation works

```
visitor ──► /demo/start ──► StartDemoSession (runs BEFORE StartSession)
                                │
                                ├─ database.default  → demo
                                ├─ session store     → demo (own cookie)
                                ├─ cache + queue     → demo
                                └─ Telescope         → off
                                        │
                     ┌──────────────────┴───────────────────┐
                     ▼                                      ▼
        rosheta_demo (all demo data)          rosheta (production)
                                                READ  reference tables
                                                WRITE demo_sessions ONLY
                                                      ↑
                                       everything else is BLOCKED by
                                       GuardedMySqlConnection
```

Three things make this safe:

1. **A separate database.** Demo patients are never created in the production
   `clients` table, so they can never appear in `/admin/clients`, the patient
   app, or the pharmacy/lab marketplace.
2. **The switch happens before the session starts.** `StartDemoSession` is
   *prepended* to the `web` middleware group, so it runs ahead of `StartSession`
   and `Authenticate`. It decides from the URL path (`/demo/*`) or a signed
   cookie — never from the session, which would be circular.
3. **`DEMO_PROD_WRITE_GUARD`.** Every MySQL connection is a
   `GuardedMySqlConnection`. During a demo request, any `INSERT/UPDATE/DELETE/
   ALTER/TRUNCATE/DROP` on the production connection **throws before the query
   runs** — not after, unlike a `DB::listen` approach. Only `demo_sessions` is
   allow-listed. Reads are unaffected.

Verified: INSERT, UPDATE, DELETE, Eloquent `save()`, raw `statement()` and
`TRUNCATE` are all blocked, while production reads, `demo_sessions` writes and
demo-database writes all pass.

---

## 5. Operating it

### Purge

```bash
php artisan demo:purge                      # everything expired, idle or ended (scheduled every 5 min)
php artisan demo:purge --all                # every demo tenant, even active ones
php artisan demo:purge --session=<uuid>     # one session
php artisan demo:purge --doctor=<id>        # one tenant, even with no session record
```

The purge deletes child → parent explicitly rather than relying on cascades,
because several relationships here are `ON DELETE SET NULL` and four tables
(`notifications`, `media`, `personal_access_tokens`, `sessions`) have no foreign
key at all. `FOREIGN_KEY_CHECKS` is deliberately left **on**, so an ordering
mistake fails loudly instead of leaving orphans.

It also deletes the tenant's uploaded files, found via `attachments.file_path`
before the rows are removed.

### Check what is running

```sql
-- production database
SELECT id, started_role, started_at, expires_at, ended_at, end_reason, doctor_id
FROM demo_sessions ORDER BY created_at DESC LIMIT 20;
```

`doctor_id IS NULL` + `purged_at` set = cleaned up. The row itself is kept
forever: it is the marketing funnel record, and it survives the tenant on
purpose.

### Emergency stop

```dotenv
DEMO_ENABLED=false
```

then `php artisan config:clear`. The login button disappears, `/demo/start`
turns visitors away politely, and existing demos end on their next request.
Production is unaffected either way. Clean up leftovers with
`php artisan demo:purge --all`.

### Changing what the demo contains

Content lives in `app/Demo/DemoSeeder.php`:

- `input()` — patient count, history depth, appointments per day, days ahead,
  prices, opening hours.
- `WAITING_OFFSETS`, `FINISHED_OFFSETS`, `IN_PROGRESS_STARTED_MINUTES_AGO` —
  where today's queue sits relative to "now".
- Clinical content (diagnoses, prescriptions, lab results, billable items) comes
  from `ClinicOnboardingService`, which the seeder **composes over and does not
  modify** — it is production onboarding code shared with the admin panel.

---

## 6. Limits and known gaps

**Limits** (all configurable in `.env`): 4h max duration, 45min idle timeout,
3 concurrent demos per IP, 10 starts per IP per day, 500 active globally.

**Deliberate deviations from the plan**, and why:

| Item | Status |
|---|---|
| `/demo/start` is exempt from CSRF | Unavoidable: the login page's token lives in a *production* session, while the request already reads sessions from the demo database, so it could never match. There is no user session to protect at that point. Abuse is covered by the per-IP limits. Every other demo route keeps CSRF. |
| Uploaded files still go to the `public` disk | The five upload call sites hard-code `Storage::disk('public')`, and changing them means touching production code paths. Purge deletes them by `attachments.file_path` instead, which is reliable. A `demo` disk is configured and ready for when those call sites are updated. |
| Friday is not a day off if the demo starts on a Friday | Otherwise the clinic would be closed and the dashboard empty — the demo would look broken. Realism loses to a working first impression. |
| Anonymous `sessions` rows with `user_id = NULL` survive purge | They belong to no user, carry nothing, and Laravel's session GC removes them. |
| No Turnstile/captcha | None exists in this application. The per-IP and global limits are the only protection today. |
| No analytics events yet | Meta Pixel / CAPI / GA4 are not built. `demo_sessions` already stores UTMs and click ids for when they are. |
| Guided checklist, lead capture, convert-to-real-account | Not built — these are Phase 3 of the brief. |

**Not yet covered by automated tests.** Everything above was verified by hand
against `rosheta.test` (start, dashboard, role switch, reset, end, purge,
concurrency, write guard). The MySQL test harness is the first item of Phase 1
in the brief and is still outstanding — see `DISCOVERY.md` §10.3.
