<?php

namespace App\Http\Controllers;

use App\Demo\DemoContext;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Models\Appointment;
use App\Models\Prescription;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "This is what came out of the printer" — for the demo only.
 *
 * The real thermal prints (queue ticket, prescription) leave the server as a
 * data-only FCM message that the staff mobile app pushes to a Bluetooth
 * RONGTA. A demo tenant has no staff phone and no printer, so pressing print
 * used to succeed silently and the visitor saw nothing at all: the single most
 * visible feature of the clinic system was invisible in the demo of it.
 *
 * These routes render the same paper on screen instead. The content is built
 * from the same models, the same clinic printer language and the same QR
 * payload as EscPosTicketRenderer / PrintPrescriptionNotification, so what the
 * visitor sees is the ticket they would have torn off — not a stock picture.
 *
 * Demo-only by construction: they live under /demo/*, which StartDemoSession
 * points at the demo database, and they 404 outside a demo session.
 */
class DemoPrintPreviewController extends Controller
{
    use ClinicContext;

    /** Queue ticket, mirroring EscPosTicketRenderer::build(). */
    public function ticket(Request $request, Appointment $appointment): View
    {
        $this->abortUnlessDemo();

        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $appointment->load(['client', 'doctor', 'clinic']);

        $clinic = $appointment->clinic;
        $lang = $clinic?->printerLanguage() ?? config('app.locale');

        return view('demo.print.ticket', [
            'appointment' => $appointment,
            'lang' => $lang,
            'ahead' => $appointment->patientsWaitingAhead(),
            // The clinic's print_qr toggle decides this on paper too.
            'qr' => ($clinic->print_qr ?? true)
                ? $this->qrDataUri(route('practice.kiosk.ticket', [
                    'clinic' => $appointment->clinic_id,
                    'appointment' => $appointment->id,
                ]))
                : '',
        ]);
    }

    /**
     * The A5 prescription sheet — the same view the browser print page uses,
     * embedded in the demo's print viewer instead of opened in a tab.
     *
     * Rendered with $embedded so the sheet drops its own toolbar, does not
     * fire the ?auto=1 print dialog, and scales itself to the viewer's width
     * (A5 is 148mm, wider than the panel). The sheet itself is untouched
     * production markup: the demo shows the real document, not a copy of it.
     */
    public function sheet(Request $request, Prescription $prescription): View
    {
        $this->abortUnlessDemo();

        $doctor = $this->clinicDoctor($request);
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        // The same relations PrescriptionController::print() loads.
        $prescription->load([
            'items', 'client', 'doctor.specialization', 'diagnosis',
            'appointment.clinic', 'appointment.medicalRequests',
        ]);

        return view('clinic.prescriptions.print', [
            'prescription' => $prescription,
            'embedded' => true,
        ]);
    }

    /** Prescription slip, mirroring PrintPrescriptionNotification::getFcmData(). */
    public function prescription(Request $request, Prescription $prescription): View
    {
        $this->abortUnlessDemo();

        $doctor = $this->clinicDoctor($request);
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load([
            'items', 'client', 'doctor.specialization', 'diagnosis',
            'appointment.clinic', 'appointment.medicalRequests',
        ]);

        $clinic = $prescription->appointment?->clinic;

        return view('demo.print.prescription', [
            'prescription' => $prescription,
            'clinic' => $clinic,
            'lang' => $clinic?->printerLanguage() ?? config('app.locale'),
            'qr' => $this->qrDataUri(\App\Support\LandingQrCode::url()),
        ]);
    }

    /**
     * A `data:image/png;base64,…` QR, matching the options LandingQrCode uses
     * so the printed and previewed codes look alike. Returns an empty string on
     * failure — a missing QR must never break the preview.
     */
    protected function qrDataUri(string $payload): string
    {
        try {
            return (new QRCode(new QROptions([
                'outputType' => QROutputInterface::GDIMAGE_PNG,
                'eccLevel' => EccLevel::M,
                'scale' => 4,
                'quietzoneSize' => 2,
                'outputBase64' => true,
            ])))->render($payload);
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }

    /** Outside a running demo these previews do not exist. */
    protected function abortUnlessDemo(): void
    {
        abort_unless(
            config('demo.enabled') && app(DemoContext::class)->isDemo(),
            404
        );
    }
}
