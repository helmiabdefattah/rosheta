<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Notifications\PrintPrescriptionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    use ClinicContext;

    /** Create a prescription (medicines, doses, durations) for the appointment. */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dose' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
            // The alternative mirrors the primary medicine, every part optional.
            'items.*.substitute_name' => ['nullable', 'string', 'max:255'],
            'items.*.substitute_dose' => ['nullable', 'string', 'max:255'],
            'items.*.substitute_frequency' => ['nullable', 'string', 'max:255'],
            'items.*.substitute_duration' => ['nullable', 'string', 'max:255'],
            'items.*.substitute_instructions' => ['nullable', 'string', 'max:255'],
        ]);

        $prescription = $appointment->prescriptions()->create([
            'code' => 'RX-'.strtoupper(Str::random(8)),
            'client_id' => $appointment->client_id,
            'doctor_id' => $doctor->id,
            'diagnosis_id' => $appointment->diagnosis?->id,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            if (blank($item['medicine_name'] ?? null)) {
                continue;
            }
            // Substitute detail without a substitute name would print as an
            // orphan dose under the primary medicine.
            if (blank($item['substitute_name'] ?? null)) {
                unset(
                    $item['substitute_name'],
                    $item['substitute_dose'],
                    $item['substitute_frequency'],
                    $item['substitute_duration'],
                    $item['substitute_instructions'],
                );
            }
            $prescription->items()->create($item);
        }

        return back()->with('status', __('app.examine.prescription_created', ['code' => $prescription->code]));
    }

    /**
     * A4/A5 prescription styled like a classic Egyptian prescription, ready to
     * print or "Save as PDF" from the browser. Accessible to the doctor and the
     * assistants of the doctor who wrote it.
     */
    public function print(Request $request, Prescription $prescription): View
    {
        $this->authorizePrescription($request, $prescription);

        // medicalRequests: the examinations/tests ordered during the visit, printed
        // under the medicines on every print type.
        $prescription->load(['items', 'client', 'doctor.specialization', 'diagnosis', 'appointment.clinic', 'appointment.medicalRequests']);

        return view('clinic.prescriptions.print', compact('prescription'));
    }

    /**
     * Server-generated PDF file of the prescription (mpdf — full Arabic/RTL
     * shaping). Streams inline by default so the browser's PDF viewer can show
     * and download it; `?download=1` forces a file download.
     */
    public function pdf(Request $request, Prescription $prescription)
    {
        $this->authorizePrescription($request, $prescription);
        // medicalRequests: the examinations/tests ordered during the visit, printed
        // under the medicines on every print type.
        $prescription->load(['items', 'client', 'doctor.specialization', 'diagnosis', 'appointment.clinic', 'appointment.medicalRequests']);

        return $this->streamPrescriptionPdf($request, $prescription);
    }

    /** Render the prescription PDF with mpdf and return it as an HTTP response. */
    protected function streamPrescriptionPdf(Request $request, Prescription $prescription)
    {
        $isAr = app()->getLocale() === 'ar';

        $tmp = storage_path('app/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A5',
            'tempDir' => $tmp,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'directionality' => $isAr ? 'rtl' : 'ltr',
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $mpdf->WriteHTML(view('clinic.prescriptions.pdf', compact('prescription'))->render());

        $filename = $prescription->code.'.pdf';
        $disposition = $request->boolean('download') ? 'D' : 'I';

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($disposition === 'D' ? 'attachment' : 'inline').'; filename="'.$filename.'"',
        ]);
    }

    /**
     * Print the prescription on the clinic's Bluetooth thermal printer via the
     * staff mobile app (same channel as the queue ticket). The app renders it as
     * a bitmap so Arabic prints correctly on the RONGTA head.
     */
    public function printThermal(Request $request, Prescription $prescription): JsonResponse
    {
        $this->authorizePrescription($request, $prescription);

        try {
            PrintPrescriptionNotification::sendToClinicStaff($prescription);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false], 500);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Only the doctor who wrote the prescription — or one of their assistants —
     * may print it. clinicDoctor() resolves an assistant to the doctor they work
     * for, so both roles pass for that doctor's prescriptions.
     */
    protected function authorizePrescription(Request $request, Prescription $prescription): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($prescription->doctor_id === $doctor->id, 403);
    }
}
