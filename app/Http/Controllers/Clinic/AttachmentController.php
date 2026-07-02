<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use ClinicContext;

    /**
     * Upload a patient file. Stored via rosheta's polymorphic attachments,
     * attached to the Client (patient) so it shows across all their visits.
     */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'file' => ['required', 'file', 'max:10240'], // 10 MB
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $appointment->client->attachments()->create([
            'uploaded_by' => $request->user()->id,
            'appointment_id' => $appointment->id,
            'title' => $data['title'] ?? $file->getClientOriginalName(),
            'file_type' => $data['type'] ?? 'other',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', __('app.examine.attachment_uploaded'));
    }

    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        // Only clinic staff may delete via this route; ensure it's a patient file.
        abort_unless($attachment->uploaded_by !== null || $attachment->appointment_id !== null, 403);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', __('app.examine.attachment_deleted'));
    }
}
