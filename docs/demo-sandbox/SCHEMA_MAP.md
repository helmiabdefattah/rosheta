# Schema Map — brief placeholders → real names

Companion to `DISCOVERY.md`. The implementation brief was written without seeing this
codebase, so it uses placeholder entity names. This file is the translation table.
Reconciled against `IMPLEMENTATION_BRIEF.md` **v1.3** (Appendix B wins on conflicts).

Model classes all live in `App\Models\`. "Tenant key" is the column that ties a row to
one demo tenant — see `DISCOVERY.md` §2 for why the tenant is `doctors`, not `clinics`.

---

## Core entities

| Brief placeholder | Real table(s) | Model | Tenant key | Notes |
|---|---|---|---|---|
| **the tenant** ("a clinic") | **`doctors`** | `Doctor` | *is the tenant* | The brief's `clinics.is_demo` becomes `doctors.is_demo` (Appendix B.1) |
| `clinics` | `clinics` | `Clinic` | `doctor_id` | A **location** under a doctor, not the tenant. One doctor may own several |
| `visits` | `appointments` | `Appointment` | `clinic_id` + `doctor_id` | Serves both systems: rosheta online booking *and* the clinic walk-in queue |
| `patients` | **`clients`** | `Client` | **none — global** | Shared with the patient app and the whole pharmacy/lab marketplace. The single strongest reason for a separate demo database |
| `users` / staff logins | `users` | `User` | `doctor_id`, `clinic_id` | Doctor = has a `doctors` row; assistant = has `users.doctor_id` set |
| `roles` / permissions | *(no table)* | — | — | **No permission system exists.** Role = which routes `EnsureClinicRole` lets you reach |
| `prescriptions` | `prescriptions` → `prescription_items` | `Prescription`, `PrescriptionItem` | via `appointment_id`; also `doctor_id`, `client_id` | |
| `diagnoses` | `diagnoses` | `Diagnosis` | via `appointment_id`; also `doctor_id`, `client_id` | One per appointment; carries `treatment_plan` |
| **`invoices`** | `collections` **+** `appointment_items` **+** `appointment_insurances` | `Collection`, `AppointmentItem`, `AppointmentInsurance` | via `appointment_id` | **There is no invoice table.** Money is: the visit price on `appointments.price`, extras in `appointment_items`, cash received in `collections`, insurer split in `appointment_insurances`. "Unpaid invoice" = `Appointment::remainingAmount() > 0` |
| `services` + prices | `billable_items` **+** `clinics.medical_examination_price` / `follow_up_price` | `BillableItem` | `doctor_id` / `clinic_id` | Two visit types are priced on the clinic; chargeable extras are a per-doctor price list |
| `lab request` | `medical_requests` | `MedicalRequest` | via `appointment_id`; also `doctor_id` | Doctor's order for a lab/radiology test |
| `lab result` | `patient_tests` (+ `attachments` for the file) | `PatientTest`, `Attachment` | `client_id`; `appointment_id` is **SET NULL** | Also `offers` / `client_requests` when the result comes from the marketplace |
| `notifications` | `notifications` | *(Laravel database channel)* | **none — polymorphic, no FK** | `notifiable_type` / `notifiable_id` |
| `messages` / chat | `conversations` → `chat_messages` | `Conversation`, `ChatMessage` | `doctor_id` (+ `client_id`) | Doctor ⇄ patient chat, time-windowed by `Doctor::chatWindowDays()` |
| `reviews` | `reviews` | `Review` | **none — `client_id` has no FK** | |
| working hours | `clinic_working_hours` + `clinic_doctor_working_hours` + `clinics.opening_hours` (JSON) | `ClinicWorkingHour`, `ClinicDoctorWorkingHour` | `clinic_id` | Three representations kept in sync by `Clinic::syncOpeningHoursFromWorkingHours()` |
| clinical templates | `medical_plans` → `medical_plan_items` | `MedicalPlan`, `MedicalPlanItem` | `doctor_id` | Reusable treatment plans |
| custom exam fields | `examination_fields` → `examination_field_values` | `ExaminationField`, `ExaminationFieldValue` | `doctor_id` / via `appointment_id` | |
| doctor time off | `doctor_off_dates` | `DoctorOffDate` | `doctor_id`; `clinic_id` **SET NULL** | |

---

## New tables this feature adds

| Brief name | Connection | Key change vs brief |
|---|---|---|
| `demo_sessions` | **production** (`mysql`) | `clinic_id` → **`doctor_id`**; `converted_clinic_id` → **`converted_doctor_id`** (Appendix B.1) |
| `demo_leads` | **production** (`mysql`) | unchanged |

Both models must carry `protected $connection = 'mysql'` so they survive the
request-scoped connection flip (Appendix A.2).

---

## Vocabulary differences that matter

| Brief term | This codebase |
|---|---|
| "in-progress visit" | `appointments.status = 'under_examination'` |
| "waiting / checked in" | `appointments.status = 'scheduled'` with a `queue_number` |
| "no-show" | `appointments.status = 'missed'` (or `'escaped'` — left the queue) |
| "upcoming, pending confirmation" | `appointments.status = 'pending'`, `source = 'reservation'` (booked on the patient app) |
| "visit type" | `appointments.type` ∈ `medical_examination`, `follow_up`, `examination`, `consultation` |
| "the workspace" / doctor+assistant app | route prefix `practice/`, route names `practice.*`, controllers in `App\Http\Controllers\Clinic\` |
| "the patient app" | route prefix `client/`, `client` auth guard, model `Client` |
| "check-in kiosk" | `practice.kiosk.*` — public, per clinic |
| "waiting room screen" | `practice.display.*` — public, per clinic |

Note the naming collision: **`App\Http\Controllers\Clinic\*` is the doctor's workspace
("practice"), while the `clinic.*` route names are a different, single-route area** for
a clinic-manager account (`/clinic/dashboard`, `EnsureUserIsClinicStaff`). Do not
confuse them.

---

## Status enums (superset across both systems)

`appointments.status`
`pending`, `confirmed`, `completed`, `missed`, `cancelled`, `scheduled`,
`under_examination`, `escaped`

`appointments.type`
`medical_examination`, `follow_up`, `examination`, `consultation`

Both widened by
`database/migrations/2026_07_01_000003_extend_appointments_for_clinic_workflow.php`.
