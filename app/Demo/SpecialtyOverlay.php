<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\BillableItem;
use App\Models\Clinic;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\ExaminationField;
use App\Models\MedicalPlan;
use App\Models\MedicalRequest;
use App\Models\Prescription;

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
        $this->replaceBillableItems($doctor, $profile);
        $this->replaceExaminationFields($doctor, $profile);
        $this->replaceMedicalPlans($doctor, $profile);
        $this->replaceVisitContent($doctor, $profile);
        $this->replaceVisitReasons($doctor, $profile);
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

    /** Fields the doctor records on every examination. */
    protected function replaceExaminationFields(Doctor $doctor, array $profile): void
    {
        if (empty($profile['examination_fields'])) {
            return;
        }

        $existing = ExaminationField::where('doctor_id', $doctor->id)->orderBy('sort_order')->get();

        foreach ($profile['examination_fields'] as $i => $field) {
            $model = $existing[$i] ?? new ExaminationField(['doctor_id' => $doctor->id]);

            $model->forceFill([
                'doctor_id' => $doctor->id,
                'label' => $field['label'],
                'type' => $field['type'],
                'options' => $field['options'] ?? null,
                'sort_order' => $i,
                'is_active' => true,
            ])->save();
        }

        $existing->slice(count($profile['examination_fields']))
            ->each(fn (ExaminationField $f) => $f->forceFill(['is_active' => false])->save());
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
