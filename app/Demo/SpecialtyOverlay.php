<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Attachment;
use App\Models\BillableItem;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\ExaminationField;
use App\Models\ExaminationFieldValue;
use App\Models\MedicalPlan;
use App\Models\MedicalRequest;
use App\Models\Offer;
use App\Models\PatientTest;
use App\Models\Prescription;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Rewrites a freshly seeded tenant's clinical content in the chosen specialty.
 *
 * ClinicOnboardingService produces one flavour of clinic — general internal
 * medicine. That is production onboarding code shared with the admin panel, so
 * it is not modified (see the brief, Appendix B.6). Instead the demo runs it
 * first and then replaces the parts that are specialty-specific: the services
 * the clinic charges for, the examination fields, the saved treatment plans,
 * and the diagnosis / prescription / lab request on every visit that has
 * already happened.
 *
 * Replacing rather than generating keeps the *shape* the onboarding service
 * built — the same visits, the same patients, the same money — so nothing
 * downstream has to know a specialty was applied.
 */
class SpecialtyOverlay
{
    public function apply(Doctor $doctor, Clinic $clinic, array $profile): void
    {
        $this->replacePatients($doctor, $profile);
        $this->replaceBillableItems($doctor, $profile);
        $fields = $this->replaceExaminationFields($doctor, $profile);
        $this->replaceExaminationValues($doctor, $fields);
        $this->replaceMedicalPlans($doctor, $profile);
        $this->replaceVisitContent($doctor, $profile);
        $this->replaceVisitReasons($doctor, $profile);
        $this->replaceTestResults($doctor, $profile);
    }

    /**
     * Rewrite the patient roster when the specialty needs different people —
     * a paediatric clinic full of 40-year-olds with hypertension is not a
     * paediatric clinic.
     *
     * Rows are rewritten in place rather than recreated, so every appointment,
     * prescription, invoice and attachment that already points at a patient
     * keeps pointing at the same one.
     */
    protected function replacePatients(Doctor $doctor, array $profile): void
    {
        if (empty($profile['patients'])) {
            return;
        }

        $people = $profile['patients'];

        $clientIds = Appointment::where('doctor_id', $doctor->id)
            ->orderBy('id')
            ->pluck('client_id')
            ->filter()
            ->unique()
            ->values();

        foreach ($clientIds as $i => $clientId) {
            $person = $people[$i % count($people)];

            $client = Client::find($clientId);

            if ($client === null) {
                continue;
            }

            $client->forceFill([
                'name' => $person['name'],
                'gender' => $person['gender'],
                'dob' => $this->resolveDob($person),
                'blood_type' => $person['blood'] ?? $client->blood_type,
                'allergies' => $person['allergies'] ?? null,
                'chronic_diseases' => $person['chronic'] ?? null,
                'medical_history' => $person['history'] ?? null,
            ])->save();
        }
    }

    /**
     * A profile gives an age rather than a date of birth, so the roster stays
     * correct however long the file sits in the repository — a "3 year old"
     * hard-coded as 2023-05-01 quietly becomes a schoolchild.
     */
    protected function resolveDob(array $person): string
    {
        if (isset($person['dob'])) {
            return $person['dob'];
        }

        $months = (int) ($person['age_months'] ?? 0);
        $years = (int) ($person['age'] ?? 0);

        return now()->subYears($years)->subMonths($months)->toDateString();
    }

    /**
     * The clinic's chargeable extras — "خدمات" on the invoice.
     *
     * Renamed in place rather than deleted and recreated, because
     * appointment_items already points at these rows for visits that have been
     * billed and paid. Renaming happens in two passes: billable_items has a
     * unique (doctor_id, name) index, and a specialty name can collide with a
     * generic one still to be renamed — "رسم قلب (ECG)" appears in both the
     * generic list and the cardiology profile.
     */
    protected function replaceBillableItems(Doctor $doctor, array $profile): void
    {
        if (empty($profile['services'])) {
            return;
        }

        $existing = BillableItem::where('doctor_id', $doctor->id)->orderBy('id')->get();
        $originalNames = $existing->pluck('name', 'id');

        // Pass 1 — park every name out of the way so pass 2 cannot collide.
        foreach ($existing as $item) {
            $item->forceFill(['name' => "__demo_{$item->id}"])->save();
        }

        // Pass 2 — assign the specialty's services.
        foreach ($profile['services'] as $i => $service) {
            $item = $existing[$i] ?? new BillableItem;

            $item->forceFill([
                'doctor_id' => $doctor->id,
                'name' => $service['name'],
                'price' => $service['price'],
                'is_active' => true,
            ])->save();

            // Keep already-billed invoice lines reading correctly. The price is
            // deliberately left alone: those visits were paid at the old price,
            // and rewriting it would desync the collections seeded against it.
            AppointmentItem::where('billable_item_id', $item->id)
                ->update(['name' => $service['name']]);
        }

        // Anything the profile did not cover keeps its original name but is
        // hidden from the price list.
        $existing->slice(count($profile['services']))->each(
            fn (BillableItem $item) => $item->forceFill([
                'name' => $originalNames[$item->id] ?? $item->name,
                'is_active' => false,
            ])->save()
        );
    }

    /**
     * Fields the doctor records on every examination.
     *
     * @return array<int, array{model: ExaminationField, values: array}> the
     *         fields with the sample values their profile supplies
     */
    protected function replaceExaminationFields(Doctor $doctor, array $profile): array
    {
        if (empty($profile['examination_fields'])) {
            return [];
        }

        $existing = ExaminationField::where('doctor_id', $doctor->id)->orderBy('sort_order')->get();
        $applied = [];

        foreach ($profile['examination_fields'] as $i => $field) {
            $model = $existing[$i] ?? new ExaminationField;

            $model->forceFill([
                'doctor_id' => $doctor->id,
                'label' => $field['label'],
                'type' => $field['type'],
                'options' => $field['options'] ?? null,
                'sort_order' => $i,
                'is_active' => true,
            ])->save();

            $applied[] = ['model' => $model, 'values' => $field['values'] ?? []];
        }

        $existing->slice(count($profile['examination_fields']))
            ->each(fn (ExaminationField $f) => $f->forceFill(['is_active' => false])->save());

        return $applied;
    }

    /**
     * Re-record the values captured against those fields.
     *
     * This is the step whose absence was most obvious: the onboarding service
     * writes values keyed to ITS field labels (blood pressure, temperature),
     * and renaming the fields left a dental clinic showing
     * "رقم السن (نظام FDI) = 120/80" and "حالة اللثة = 78". The value belongs
     * to the field, so it has to be rewritten whenever the field is.
     *
     * @param  array<int, array{model: ExaminationField, values: array}>  $fields
     */
    protected function replaceExaminationValues(Doctor $doctor, array $fields): void
    {
        if ($fields === []) {
            return;
        }

        $fieldIds = collect($fields)->pluck('model.id')->all();

        $visits = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['completed', 'under_examination'])
            ->orderBy('id')
            ->pluck('id');

        // Values for a field the profile gave no samples for would be
        // meaningless; drop them rather than leave the old number behind.
        ExaminationFieldValue::whereIn('appointment_id', $visits)
            ->whereNotIn('examination_field_id', $fieldIds)
            ->delete();

        foreach ($visits as $visitIndex => $visitId) {
            foreach ($fields as $field) {
                $samples = $field['values'];

                if ($samples === []) {
                    ExaminationFieldValue::where('appointment_id', $visitId)
                        ->where('examination_field_id', $field['model']->id)
                        ->delete();

                    continue;
                }

                ExaminationFieldValue::updateOrCreate(
                    [
                        'appointment_id' => $visitId,
                        'examination_field_id' => $field['model']->id,
                    ],
                    ['value' => $samples[$visitIndex % count($samples)]]
                );
            }
        }
    }

    /** One-click treatment roadmaps saved by the doctor. */
    protected function replaceMedicalPlans(Doctor $doctor, array $profile): void
    {
        if (empty($profile['plans'])) {
            return;
        }

        MedicalPlan::where('doctor_id', $doctor->id)->each(function (MedicalPlan $plan) {
            $plan->items()->delete();
            $plan->delete();
        });

        foreach ($profile['plans'] as $plan) {
            $model = MedicalPlan::create([
                'doctor_id' => $doctor->id,
                'title' => $plan['title'],
            ]);

            foreach ($plan['items'] as $item) {
                $model->items()->create($item);
            }
        }
    }

    /**
     * The diagnosis, prescription and lab requests on every visit that has
     * happened — the content the doctor actually opens and reads.
     */
    protected function replaceVisitContent(Doctor $doctor, array $profile): void
    {
        $cases = $profile['cases'];

        $visits = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['completed', 'under_examination'])
            ->orderBy('id')
            ->get();

        foreach ($visits as $i => $visit) {
            $case = $cases[$i % count($cases)];

            $this->rewriteDiagnosis($visit, $case);
            $this->rewritePrescription($visit, $case);
            $this->rewriteRequests($visit, $doctor, $case);
        }
    }

    protected function rewriteDiagnosis(Appointment $visit, array $case): void
    {
        Diagnosis::where('appointment_id', $visit->id)->update([
            'diagnosis' => $case['diagnosis'],
            'treatment_plan' => $case['treatment_plan'],
            'notes' => $case['notes'] ?? null,
        ]);
    }

    protected function rewritePrescription(Appointment $visit, array $case): void
    {
        $prescriptions = Prescription::where('appointment_id', $visit->id)->get();

        foreach ($prescriptions as $prescription) {
            $prescription->items()->delete();

            foreach ($case['medicines'] as $medicine) {
                $prescription->items()->create($medicine);
            }
        }
    }

    protected function rewriteRequests(Appointment $visit, Doctor $doctor, array $case): void
    {
        $existing = MedicalRequest::where('appointment_id', $visit->id)->orderBy('id')->get();
        $requests = $case['requests'] ?? [];

        // Keep the completed/requested mix the onboarding service produced, so
        // some visits still show results waiting and others show them back.
        foreach ($requests as $i => $request) {
            $model = $existing[$i] ?? null;

            if ($model === null) {
                MedicalRequest::create([
                    'appointment_id' => $visit->id,
                    'client_id' => $visit->client_id,
                    'doctor_id' => $doctor->id,
                    'type' => $request['type'],
                    'name' => $request['name'],
                    'status' => $existing->first()->status ?? 'requested',
                    'notes' => $request['notes'] ?? null,
                ]);

                continue;
            }

            $model->forceFill([
                'type' => $request['type'],
                'name' => $request['name'],
                'notes' => $request['notes'] ?? null,
            ])->save();
        }

        $existing->slice(count($requests))->each(fn (MedicalRequest $r) => $r->delete());
    }

    /**
     * The lab and radiology results already filed against past visits, and the
     * PDFs behind them.
     *
     * Without this a dental clinic files a chest X-ray and an abdominal
     * ultrasound — the onboarding service's four generic reports — against
     * every case, and the file the doctor opens says "Chest X-Ray - PA view".
     */
    protected function replaceTestResults(Doctor $doctor, array $profile): void
    {
        if (empty($profile['results'])) {
            return;
        }

        $results = $profile['results'];

        $tests = PatientTest::where('doctor_id', $doctor->id)->orderBy('id')->get();

        foreach ($tests as $i => $test) {
            $result = $results[$i % count($results)];

            $test->forceFill([
                'type' => $result['type'],
                'title' => $result['title'],
                'notes' => $result['notes'] ?? null,
            ])->save();

            $this->rewriteAttachment(
                Attachment::where('attachable_type', PatientTest::class)
                    ->where('attachable_id', $test->id)
                    ->get(),
                $result
            );
        }

        // The marketplace lab panel on the examine screen reads its files from
        // the offers the onboarding service created; those carry the same
        // generic reports.
        $offerAttachments = Attachment::where('attachable_type', Offer::class)->orderBy('id')->get();

        foreach ($offerAttachments as $i => $attachment) {
            $this->rewriteAttachment(collect([$attachment]), $results[$i % count($results)]);
        }
    }

    /**
     * Swap an attachment's file for one that matches the specialty, deleting
     * the old one so the demo does not leave orphans on disk.
     *
     * @param  \Illuminate\Support\Collection<int, Attachment>  $attachments
     */
    protected function rewriteAttachment($attachments, array $result): void
    {
        foreach ($attachments as $attachment) {
            $lines = $result['lines'] ?? [$result['title']];
            $bytes = DemoPdf::render($lines);

            // Name the file from the English report heading, not the Arabic
            // title: Str::slug keeps Arabic characters, and an Arabic filename
            // in a URL is needless trouble.
            $fileName = (Str::slug($lines[0]) ?: 'result').'-'.Str::random(6).'.pdf';
            $path = 'test-results/'.$fileName;

            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            Storage::disk('public')->put($path, $bytes);

            $attachment->forceFill([
                'title' => $result['lines'][0] ?? $result['title'],
                'file_type' => $result['type'] === 'radiology' ? 'radiology' : 'lab',
                'file_path' => $path,
                'file_name' => $fileName,
                'mime_type' => 'application/pdf',
                'file_size' => strlen($bytes),
            ])->save();
        }
    }

    /** Why each patient came in — shown on the queue and the visit card. */
    protected function replaceVisitReasons(Doctor $doctor, array $profile): void
    {
        if (empty($profile['reasons'])) {
            return;
        }

        $reasons = $profile['reasons'];

        Appointment::where('doctor_id', $doctor->id)
            ->orderBy('id')
            ->get()
            ->each(function (Appointment $appointment, int $i) use ($reasons) {
                $appointment->forceFill(['reason' => $reasons[$i % count($reasons)]])->save();
            });
    }
}
