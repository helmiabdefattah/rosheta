<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Saves the doctor's custom examination-field values for one appointment.
 * File fields are stored through the existing patient-attachments system.
 */
class ExaminationValueController extends Controller
{
    use ClinicContext;

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $fields = $doctor->examinationFields()->where('is_active', true)->get();
        $inputs = (array) $request->input('fields', []);

        foreach ($fields as $field) {
            $raw = $inputs[$field->id] ?? null;

            if ($field->type === 'file') {
                $file = $request->file("field_files.{$field->id}");
                if ($file) {
                    $path = $file->store('attachments', 'public');
                    $attachment = $appointment->client->attachments()->create([
                        'uploaded_by' => $request->user()->id,
                        'appointment_id' => $appointment->id,
                        'title' => $field->label,
                        'file_type' => 'exam_field',
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $appointment->examinationValues()->updateOrCreate(
                        ['examination_field_id' => $field->id],
                        ['attachment_id' => $attachment->id, 'value' => $file->getClientOriginalName()],
                    );
                }

                continue;
            }

            $value = is_string($raw) ? trim($raw) : $raw;

            if ($field->type === 'percentage' && $value !== null && $value !== '') {
                $value = max(0, min(100, (float) $value));
            } elseif ($field->type === 'number' && $value !== null && $value !== '') {
                $value = (float) $value;
            }

            if ($value === null || $value === '') {
                $appointment->examinationValues()->where('examination_field_id', $field->id)->delete();

                continue;
            }

            $appointment->examinationValues()->updateOrCreate(
                ['examination_field_id' => $field->id],
                ['value' => (string) $value],
            );
        }

        return back()->with('status', __('app.field.values_saved'));
    }
}
