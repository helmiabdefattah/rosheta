<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Lets a patient (client) attach a file or photo to one of their own clinic
 * appointments so the doctor can see it during the visit. The file is stored
 * as a patient attachment linked to the appointment — the same records the
 * doctor's examination screen lists.
 */
class ClientAttachmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $client = Auth::guard('client')->user();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'appointment_id' => ['nullable', 'integer'],
            // Images (incl. phone camera captures) and PDFs, up to 10 MB.
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf'],
        ]);

        // Optionally tie the file to one of the patient's own appointments.
        $appointmentId = null;
        if (! empty($data['appointment_id'])) {
            $appointment = Appointment::where('id', $data['appointment_id'])
                ->where('client_id', $client->id)
                ->first();
            abort_unless($appointment, 403);
            $appointmentId = $appointment->id;
        }

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $client->attachments()->create([
            'uploaded_by' => null, // uploaded by the patient, not clinic staff
            'appointment_id' => $appointmentId,
            'title' => $data['title'] ?? $file->getClientOriginalName(),
            'file_type' => 'patient_upload',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $message = app()->getLocale() === 'ar'
            ? 'تم رفع الملف وسيظهر للطبيب في موعدك.'
            : 'File uploaded — the doctor will see it at your appointment.';

        return back()->with('success', $message);
    }

    /** Rename a file the patient uploaded (edit its title). */
    public function update(Request $request, Attachment $attachment): RedirectResponse
    {
        $client = Auth::guard('client')->user();
        $this->authorizeOwnUpload($attachment, $client);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $attachment->update([
            'title' => trim((string) ($data['title'] ?? '')) ?: $attachment->file_name,
        ]);

        $message = app()->getLocale() === 'ar' ? 'تم تحديث الملف.' : 'File updated.';

        return back()->with('success', $message);
    }

    /**
     * Remove a file the patient uploaded themselves — allowed only before the
     * visit is done, and never for files added by clinic staff.
     */
    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        $client = Auth::guard('client')->user();
        $this->authorizeOwnUpload($attachment, $client);

        // Only while the appointment is still upcoming/active.
        $appointment = $attachment->appointment;
        if ($appointment && in_array($appointment->status, ['completed', 'cancelled'], true)) {
            $message = app()->getLocale() === 'ar'
                ? 'لا يمكن حذف الملف بعد انتهاء الموعد.'
                : "This file can't be removed after the appointment is over.";

            return back()->with('error', $message);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        $message = app()->getLocale() === 'ar' ? 'تم حذف الملف.' : 'File removed.';

        return back()->with('success', $message);
    }

    /** The file must be this patient's own upload (not a clinic-added file). */
    protected function authorizeOwnUpload(Attachment $attachment, $client): void
    {
        abort_unless(
            $attachment->attachable_type === $client->getMorphClass()
            && (int) $attachment->attachable_id === (int) $client->id
            && $attachment->uploaded_by === null,
            403
        );
    }
}
