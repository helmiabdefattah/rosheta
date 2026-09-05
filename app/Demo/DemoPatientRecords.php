<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Clinic;
use App\Models\Collection as PaymentCollection;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\MedicalRequest;
use App\Models\PatientTest;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Makes sure every demo patient the doctor can click on has something to read.
 *
 * The onboarding service deliberately leaves gaps — it files results on every
 * other visit so the empty state is visible too, and the patients it creates
 * last are only ever booked, never seen. That is right for a real clinic being
 * set up; it is wrong for a demo, where a visitor opens two or three files at
 * random and judges the product by what is in them.
 *
 * So: every patient with an appointment gets at least one visit behind them
 * inside the demo's history window, and every visit that has happened carries
 * a diagnosis with its treatment map, a prescription, orders and results.
 *
 * Content is CLONED from the visits the onboarding service already wrote
 * rather than invented here — the clinical text stays in one place, and
 * whatever SpecialtyOverlay rewrites afterwards rewrites these too (this runs
 * before it). The two deliberately empty cases — the new booking and the
 * no-show — are created after this runs, so they stay empty.
 */
class DemoPatientRecords
{
    /** Visits that already carry the onboarding service's clinical content. */
    private Collection $templates;

    /** Visits that also carry lab/radiology results. */
    private Collection $resultTemplates;

    public function complete(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $this->templates = $this->visitsWithContent($doctor);

        if ($this->templates->isEmpty()) {
            return;
        }

        $this->resultTemplates = $this->templates->filter(
            fn (Appointment $visit) => PatientTest::where('appointment_id', $visit->id)->exists()
        )->values();

        $this->giveEveryPatientAHistory($doctor, $clinic, $t0);
        $this->fillGapsInVisits($doctor);
    }

    /** Completed visits that have a diagnosis — the ones worth copying. */
    protected function visitsWithContent(Doctor $doctor): Collection
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereIn('id', Diagnosis::where('doctor_id', $doctor->id)->select('appointment_id'))
            ->orderBy('id')
            ->get();
    }

    /**
     * A patient in today's queue whose file opens empty is the worst thing the
     * demo can show. Give anyone without a visit behind them one inside the
     * history window, with the same content the rest of the archive has.
     */
    protected function giveEveryPatientAHistory(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $startOfToday = $t0->copy()->startOfDay();

        $byClient = Appointment::where('doctor_id', $doctor->id)
            ->whereNotNull('client_id')
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy('client_id');

        $index = 0;

        foreach ($byClient as $clientId => $visits) {
            $hasHistory = $visits->contains(
                fn (Appointment $a) => $a->status === 'completed' && $a->scheduled_at?->lt($startOfToday)
            );

            if ($hasHistory) {
                continue;
            }

            $this->createPastVisit($doctor, $clinic, (int) $clientId, $t0, $index++);
        }
    }

    /** One completed, paid visit in the past, with a full file behind it. */
    protected function createPastVisit(Doctor $doctor, Clinic $clinic, int $clientId, Carbon $t0, int $index): void
    {
        $when = DemoWindow::pastSlot($t0, $index, $this->closedDays($clinic));
        $isFollowUp = $index % 3 === 1;

        $visit = Appointment::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'client_id' => $clientId,
            'scheduled_at' => $when,
            'appointment_date' => $when->toDateString(),
            'appointment_time' => $when->format('H:i:s'),
            'queue_number' => ($index % 6) + 1,
            'source' => $index % 3 === 0 ? 'kiosk' : 'system',
            'type' => $isFollowUp ? 'follow_up' : 'medical_examination',
            'price' => $isFollowUp ? $clinic->follow_up_price : $clinic->medical_examination_price,
            'status' => 'completed',
            'reason' => $isFollowUp ? 'متابعة الحالة' : 'كشف أول',
        ]);

        $this->copyContentInto($visit, $doctor, $index);
        $this->markAsPaid($visit, $doctor);
    }

    /**
     * The visit is finished and settled. Without the collection every patient
     * given a history here would be listed as owing money, which is both wrong
     * and the first thing the front-desk screens shout about.
     */
    protected function markAsPaid(Appointment $visit, Doctor $doctor): void
    {
        $collectedBy = User::where('doctor_id', $doctor->id)->value('id') ?? $doctor->user_id;

        PaymentCollection::create([
            'appointment_id' => $visit->id,
            'amount' => (float) $visit->price,
            'collected_by' => $collectedBy,
            'collected_at' => $visit->scheduled_at,
        ]);
    }

    /** Anything a visit that has happened is missing, copied from one that has it. */
    protected function fillGapsInVisits(Doctor $doctor): void
    {
        $visits = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['completed', 'under_examination'])
            ->orderBy('id')
            ->get();

        foreach ($visits as $i => $visit) {
            $this->copyContentInto($visit, $doctor, $i);
        }
    }

    /** Each piece is copied only if this visit does not already have it. */
    protected function copyContentInto(Appointment $visit, Doctor $doctor, int $index): void
    {
        $template = $this->templates[$index % $this->templates->count()];

        if ($template->id === $visit->id) {
            $template = $this->templates[($index + 1) % $this->templates->count()];
        }

        $this->copyDiagnosisAndPrescription($visit, $template, $doctor);
        $this->copyRequests($visit, $template, $doctor);
        $this->copyExaminationValues($visit, $template);
        $this->copyResults($visit, $doctor, $index);
    }

    protected function copyDiagnosisAndPrescription(Appointment $visit, Appointment $template, Doctor $doctor): void
    {
        $diagnosis = Diagnosis::where('appointment_id', $visit->id)->first();

        if (! $diagnosis) {
            $source = Diagnosis::where('appointment_id', $template->id)->first();

            if (! $source) {
                return;
            }

            $diagnosis = Diagnosis::create([
                'appointment_id' => $visit->id,
                'client_id' => $visit->client_id,
                'doctor_id' => $doctor->id,
                'diagnosis' => $source->diagnosis,
                'treatment_plan' => $source->treatment_plan,
                'notes' => $source->notes,
            ]);
        }

        // A diagnosis with no treatment map is half a file: the map is the part
        // the demo is there to show.
        if (blank($diagnosis->treatment_plan)) {
            $plan = Diagnosis::where('appointment_id', $template->id)->value('treatment_plan');

            if (filled($plan)) {
                $diagnosis->forceFill(['treatment_plan' => $plan])->save();
            }
        }

        if (Prescription::where('appointment_id', $visit->id)->exists()) {
            return;
        }

        $source = Prescription::with('items')->where('appointment_id', $template->id)->first();

        if (! $source) {
            return;
        }

        $prescription = Prescription::create([
            'code' => 'RX-'.strtoupper(Str::random(8)),
            'appointment_id' => $visit->id,
            'client_id' => $visit->client_id,
            'doctor_id' => $doctor->id,
            'diagnosis_id' => $diagnosis->id,
            'notes' => $source->notes,
        ]);

        foreach ($source->items as $item) {
            $prescription->items()->create([
                'medicine_name' => $item->medicine_name,
                'dose' => $item->dose,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'instructions' => $item->instructions,
            ]);
        }
    }

    protected function copyRequests(Appointment $visit, Appointment $template, Doctor $doctor): void
    {
        if (MedicalRequest::where('appointment_id', $visit->id)->exists()) {
            return;
        }

        $sources = MedicalRequest::where('appointment_id', $template->id)->orderBy('id')->get();

        foreach ($sources as $source) {
            MedicalRequest::create([
                'appointment_id' => $visit->id,
                'client_id' => $visit->client_id,
                'doctor_id' => $doctor->id,
                'type' => $source->type,
                'name' => $source->name,
                'status' => $visit->status === 'completed' ? 'completed' : 'requested',
                'notes' => $source->notes,
            ]);
        }
    }

    protected function copyExaminationValues(Appointment $visit, Appointment $template): void
    {
        if ($visit->examinationValues()->exists()) {
            return;
        }

        foreach ($template->examinationValues as $value) {
            $visit->examinationValues()->create([
                'examination_field_id' => $value->examination_field_id,
                'value' => $value->value,
            ]);
        }
    }

    /**
     * Lab and radiology results, with their PDFs — the onboarding service files
     * them on every other visit only. Each copy gets its own file on disk so
     * the purge deletes them independently.
     */
    protected function copyResults(Appointment $visit, Doctor $doctor, int $index): void
    {
        if ($this->resultTemplates->isEmpty()) {
            return;
        }

        if (PatientTest::where('appointment_id', $visit->id)->exists()) {
            return;
        }

        $template = $this->resultTemplates[$index % $this->resultTemplates->count()];

        if ($template->id === $visit->id) {
            return;
        }

        $sources = PatientTest::with('attachments')
            ->where('appointment_id', $template->id)
            ->orderBy('id')
            ->get();

        foreach ($sources as $source) {
            $test = PatientTest::create([
                'client_id' => $visit->client_id,
                'appointment_id' => $visit->id,
                'doctor_id' => $doctor->id,
                'uploaded_by' => $source->uploaded_by,
                'type' => $source->type,
                'title' => $source->title,
                'notes' => $source->notes,
            ]);

            foreach ($source->attachments as $attachment) {
                $this->copyAttachment($attachment, $test, $visit);
            }
        }
    }

    protected function copyAttachment(Attachment $source, PatientTest $test, Appointment $visit): void
    {
        $path = $source->file_path;

        if ($path && Storage::disk('public')->exists($path)) {
            $name = Str::beforeLast(basename($path), '.').'-'.Str::random(6).'.pdf';
            $copy = Str::beforeLast($path, '/').'/'.$name;

            Storage::disk('public')->copy($path, $copy);
        } else {
            // The source file is gone; write a readable stand-in rather than
            // filing a result whose "view" link opens nothing.
            $name = 'result-'.Str::random(6).'.pdf';
            $copy = 'test-results/'.$name;

            Storage::disk('public')->put($copy, DemoPdf::render([$source->title ?: 'Result']));
        }

        $test->attachments()->create([
            'uploaded_by' => $source->uploaded_by,
            'appointment_id' => $visit->id,
            'title' => $source->title,
            'file_type' => $source->file_type,
            'file_path' => $copy,
            'file_name' => $name,
            'mime_type' => $source->mime_type,
            'file_size' => Storage::disk('public')->size($copy),
        ]);
    }

    /** @return array<int, string> */
    protected function closedDays(Clinic $clinic): array
    {
        return $clinic->workingHours()
            ->where('is_closed', true)
            ->pluck('day')
            ->map(fn ($day) => strtolower((string) $day))
            ->all();
    }
}
