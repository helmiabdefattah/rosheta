# Prompts to paste into Claude Code (IDE) — Mostashfa-on Demo Sandbox

**المشروع:** `C:\laragon\sites\rosheta` (المستندات محفوظة داخله)

استخدم البرومبتات بالترتيب. **لا تعطِ برومبت مرحلة قبل أن تراجع مخرجات المرحلة السابقة.**

> البرومبت الأول أدناه يبحث عن المستندات بنفسه أياً كان اسمها ومكانها، ثم يقترح ترتيبها. البرومبتات التالية تفترض أنها أصبحت في `docs/demo-sandbox/` باسمَي `IMPLEMENTATION_BRIEF.md` و`DESIGN_AR.md` — عدّل المسار إن اخترت غير ذلك.

---

## Prompt 0 — Discovery (الأهم — انسخه أولاً)

```
Locate the demo sandbox planning documents saved in this repository. There are
two: an English implementation brief (mentions "Phase 0 — Discovery",
"DEMO_PROD_WRITE_GUARD", "Appendix A") and an Arabic design document (mentions
"بيئة التجربة"). Search for them, tell me the paths you found, and read BOTH in
full — including Appendix A, which covers this project's Laravel/MySQL
specifics — before doing anything else. If they are not already under
docs/demo-sandbox/, move them there as IMPLEMENTATION_BRIEF.md and DESIGN_AR.md
and commit that move on its own.

Your task then is Phase 0 (Discovery) ONLY. This is a read-only investigation:
write NO application code, create NO migrations, change NO existing files
except the report files described below.

Investigate this repository and produce docs/demo-sandbox/DISCOVERY.md answering
all 10 questions in the brief's Phase 0 section. Requirements for the report:

0. Start by confirming or correcting the stack assumption in Appendix A: read
   composer.json, config/database.php and .env.example and state the actual
   framework version, database engine and version, auth mechanism, queue driver,
   cache driver, and session driver. If the project already uses a tenancy
   package (stancl/tenancy, spatie/laravel-multitenancy) say so prominently —
   it changes the whole approach. Appendix A is my assumption, not fact; tell me
   plainly where it is wrong.

1. Ground every claim in the actual code. For each answer, cite the concrete
   file paths (and line numbers where useful) you based it on. If something is
   ambiguous or you could not determine it, write "UNKNOWN — needs Helmi's
   input" instead of guessing. I would much rather see ten honest UNKNOWNs than
   one confident wrong answer.
2. Question 2 (tenancy model) must be an exhaustive table of EVERY table in the
   schema with: table name, tenant key (clinic_id / path to it / none), and a
   risk note if demo rows in it could leak into global space.
3. Question 4 (external side effects) must be an exhaustive list of call sites,
   not a summary. Include SMS, WhatsApp, email, push, payment gateway,
   pharmacy/lab partner APIs, video provider, calendar/social integrations,
   and outgoing webhooks.
4. Question 8 (multi-connection readiness) is critical — the whole design
   depends on routing demo requests to a second database connection. Find every
   raw SQL query, stored procedure, cross-database JOIN, migration hack, and
   hard-coded connection/database name that would break under a second
   connection. List them all. Also report the exact middleware order of the web
   and api route groups (Appendix A §A.3: the demo middleware must run before
   auth, and this is the most likely thing to break).
5. Also create docs/demo-sandbox/SCHEMA_MAP.md mapping the placeholder names
   used in the brief (clinics, visits, patients, invoices, prescriptions,
   appointments, users/roles) to the real names in this codebase.

End the report with a section "Recommendation" covering:
- Which isolation mode from design doc §2.1 fits this stack best
  (tenant / schema / database) and why.
- The minimum set of schema changes needed for Phase 1.
- Anything in the current architecture that makes per-visitor demo tenants
  hard or risky, with your proposed way around it.
- Your estimate of effort for Phase 1, and what you are least sure about.

When the report is written, stop and summarise the three most important
findings to me. Do not start Phase 1.
```

---

## Prompt 0.5 — Spike (شغّله قبل Prompt 1 — قرار جلسة/كاش/طابور)

```
Read Appendix B of the implementation brief, then do the B.8 step 0 spike ONLY.

Work on a throwaway branch (spike/demo-connection). This is a proof of concept:
I will read it and throw it away, so optimise for answering the question, not
for production quality. Do not touch anything outside this branch.

The question: can a demo request in this application get its OWN database
connection, session store, cache prefix and queue — decided from the URL prefix
alone, before StartSession boots — while leaving the production database
completely untouched?

Build the minimum that answers it:
1. A `demo` connection in config/database.php reading DEMO_DB_* (create the
   rosheta_demo database locally and migrate it).
2. A middleware registered at the TOP of the web group, ahead of StartSession,
   that detects a /demo/* request from the request path (never from the session)
   and sets: database.default=demo, the session store to demo (own connection or
   file driver with a demo path), a demo cache prefix, a distinct session cookie
   name, and the demo queue.
3. Two throwaway routes under /demo: one that writes a row and reads it back
   across a redirect (proving the session survives and resolves to the demo
   store), and one that dispatches a queued job which writes a row (proving the
   worker context works).
4. A temporary query listener on the production connection that logs any write
   attempted during those requests.

Then report:
- Did the middleware ordering work? Show the actual middleware stack order you
  ended up with and where you had to register it.
- Which infrastructure tables, if any, still received writes on the production
  connection during the spike? List them with the SQL. If the answer is "none
  except demo_sessions/demo_leads", say so explicitly — that is the outcome the
  whole design depends on.
- What did NOT work, or only worked with a hack you would not ship?
- Your recommendation: proceed with Appendix B.3 as written, or does this app
  need a different approach?

Be blunt about problems. A spike that reports "it works" when it needed three
workarounds is worse than useless to me — I will build the real thing on top of
whatever you tell me here.
```

---

## Prompt 1 — Phase 1 (بعد مراجعة تقرير Discovery)

```
I have reviewed DISCOVERY.md and the spike. Implement Phase 1 following the
REVISED ORDER in Appendix B.8 of docs/demo-sandbox/IMPLEMENTATION_BRIEF.md.
Appendix B overrides the body of the brief and Appendix A everywhere they
disagree — re-read it before you start. Commit after each numbered step so I can
review incrementally:

1. Test harness (B.5): phpunit against a real MySQL rosheta_test database, a
   script that creates and migrates it, and only the factories this work needs
   (Doctor, Clinic, Appointment, + whatever the seeder touches). Do not try to
   make SQLite work and do not retrofit tests onto the rest of the app.
2. Second connection, demo storage disk, cache prefix, queue, and the one-way
   reference sync job — including the assertion that the job refuses to copy
   clients/visits/prescriptions/invoices or any user table (B.2).
3. DEMO_PROD_WRITE_GUARD + its test (§1.2b), preventive rather than
   after-the-fact if the framework allows (Appendix A.4). Do this before any
   seeding code exists.
4. Schema: demo columns on `doctors` (NOT clinics — B.1); demo_sessions and
   demo_leads on the production connection, keyed by doctor_id.
5. POST /api/demo/start using the web-guard flow from B.4 (Auth::login +
   redirect, no tokens), with DemoSeeder built OVER ClinicOnboardingService by
   composition (B.6) and a minimal template.
6. Purge covering BOTH doctor_id and clinic_id paths, explicitly including
   billable_items, medical_plans, examination_fields, insurance_collections and
   conversations, plus the dynamic completeness test that fails on any table it
   cannot classify (B.1).

Rules:
- Follow the existing conventions of this codebase (naming, folder structure,
  error handling, test style). Match what is already there rather than
  introducing a new style.
- Do not touch production code paths. If a change to shared code is
  unavoidable, stop and explain why before making it.
- No `if (isDemo)` conditionals scattered through business logic — the demo
  behaviour belongs in DemoContext, the connection switch, and the guard.
- Every env var must have a safe default so that a deployment without the new
  vars behaves exactly as today (DEMO_ENABLED=false path).
- If reality contradicts the brief, follow reality and tell me what you changed
  and why — the brief was written without seeing this code.

When done, run the full test suite and report: what you built, what you had to
deviate on, and anything you deliberately left for Phase 2.
```

---

## Prompt 2 — Phase 2 (التجربة الكاملة)

```
Implement Phase 2 from docs/demo-sandbox/IMPLEMENTATION_BRIEF.md.

Order: (a) the full general_v1 template, (b) DemoGuard provider decorators for
every external channel found in Discovery Q4, (c) the demo bar + role switch +
reset + end endpoints, (d) the /demo landing page.

The single most important acceptance criterion for the template: after starting
a demo, EVERY screen in the doctor app and the assistant app must show real
content immediately — no empty states anywhere. Before you write the template,
enumerate the actual screens/routes in this app and list which template entities
populate each one. Show me that mapping table first, then build. Any screen with
no data source is a gap in the template, not an acceptable empty state.

For DemoGuard: add the test double that fails the suite if any real provider is
invoked while DemoContext.isDemo() is true.

The demo bar and landing page must be RTL Arabic, tested at 360px width. Use the
Arabic copy from docs/demo-sandbox/DESIGN_AR.md sections 8.1 and 8.2 verbatim.
```

---

## Prompt 3 — Phase 3 (التحويل والقياس)

```
Implement Phase 3 from docs/demo-sandbox/IMPLEMENTATION_BRIEF.md: the 8-step
guided checklist (auto-completing from domain events, not from UI clicks where
a domain event exists), lead capture with its webhook, convert-to-real-account
with settings copy, and the analytics events.

For analytics, the critical detail is deduplication: every event fires from both
the browser and the server with the SAME event_id. Write a test that asserts the
client and server payloads for a given event carry identical event_id and
matching user data. Getting this wrong silently doubles our reported conversions
and makes the ad budget decisions wrong, so treat it as a correctness bug, not a
nice-to-have.

Also build GET /internal/demo/metrics (admin-only) — it is our source of truth
for the ad campaigns, and it must not be reachable by a demo token.
```

---

## Prompt 4 — Phase 4 (التوسّع)

```
Implement Phase 4 from docs/demo-sandbox/IMPLEMENTATION_BRIEF.md: templates
dental_v1 and pediatric_v1, specialty-driven landing page variants
(/demo?specialty=dental), scheduled "live feel" notifications, and the A/B flag
stored on demo_sessions.

Adding a new template must require ZERO code changes — only a new JSON file. If
that is not currently true, fix the seeder first and tell me what you changed.
```

---

## برومبتات مساعدة (استخدمها وقت الحاجة)

### مراجعة أمنية قبل الإطلاق

```
Act as a security reviewer for the demo sandbox feature. Assume an attacker who
starts a demo session and tries to (1) write to the production database,
(2) read another visitor's demo tenant, (3) reach admin routes, (4) keep a
session alive forever, (5) exhaust storage or database connections,
(6) trigger a real SMS/payment/pharmacy order, (7) get a demo doctor to appear
in patient-facing search or the search index.

For each: trace the actual code path and tell me whether it succeeds or is
blocked, citing files. Where it is blocked, name the specific line that blocks
it. Do not accept "the design says so" as evidence — I want the code. Report
findings ranked by severity, and write a test for each real gap you find.
```

### التحقق من الحذف الكامل

```
Write and run an integration test proving requirement F7: start a demo, then
exercise it heavily (create visits, prescriptions, invoices, upload files,
change clinic settings, invite an assistant, send simulated messages), then end
it and run the purge. Assert that for the purged clinic_id there are ZERO rows
remaining in every table of the demo database that has a tenant path, ZERO files
under its storage prefix, ZERO cache keys, and that old tokens return 401.
The test must discover the tables dynamically from the schema so a table added
later cannot be silently missed.
```

### إعداد بيئة التطوير المحلية

```
Add everything a developer needs to run the demo locally: docker-compose service
for the demo database, .env.example entries with safe defaults, a make/npm
script that creates and migrates the demo database and seeds the reference
tables, and a section in docs/demo-sandbox/RUNBOOK.md covering: how to edit a
scenario template, how to force-purge a session, how to disable the demo in an
emergency (DEMO_ENABLED=false), and how to read the metrics endpoint.
```

### عند التعثّر أو الخروج عن الخطة

```
Stop implementing. Compare the current state of the code against
docs/demo-sandbox/IMPLEMENTATION_BRIEF.md and give me:
1. What is done and verified by tests.
2. What is done but untested.
3. What is not started.
4. Where the implementation now diverges from the brief, and whether the code or
   the brief should be corrected.
Then update the brief file itself so it matches reality, and wait.
```

---

## نصائح لتشغيل Claude Code على هذا المشروع

- **`/init` أولاً** إن لم يكن هناك `CLAUDE.md` في المستودع — يعطيه سياق البنية والأوامر (اختبارات، lint، migrations) فيقل الخطأ كثيراً.
- **branch منفصلة:** `git checkout -b feat/demo-sandbox` قبل Prompt 1.
- **commit بعد كل خطوة مرقّمة** كما في البرومبت — يسهّل التراجع عن خطوة واحدة بدل المرحلة كلها.
- **لا تدمج مرحلتين في برومبت واحد.** أطول برومبت = أكثر انحرافاً عن الخطة.
- **أضف `docs/demo-sandbox/` إلى سياق كل جلسة** (`@docs/demo-sandbox/IMPLEMENTATION_BRIEF.md`) حتى لا يعتمد على ذاكرة الجلسة.
- **راجع الـ migrations يدوياً** قبل تشغيلها على أي قاعدة بيانات فيها بيانات حقيقية.
