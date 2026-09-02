<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Printable login cards for a doctor and their assistants — one card per
 * account, laid out to be screenshotted and handed over.
 *
 * Passwords are stored hashed, so an existing one can never be read back. A
 * card therefore shows the password only right after an admin generates a new
 * one here: the plaintext is flashed for that single render and is gone on
 * reload. Nothing is changed until the admin explicitly asks for it.
 */
class DoctorCredentialsController extends Controller
{
    /** Flash keys carrying the one-time plaintext through the redirect. */
    public const FLASH_USER = 'credentials_user_id';
    public const FLASH_PASSWORD = 'credentials_password';

    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'assistants.assistantClinic', 'clinics']);

        return view('admin.doctors.credentials', [
            'doctor' => $doctor,
            'assistants' => $doctor->assistants->sortBy('name')->values(),
        ]);
    }

    /**
     * Reset one account's password to a freshly generated one and hand the
     * plaintext back for a single render.
     */
    public function resetPassword(Request $request, Doctor $doctor, User $user)
    {
        $this->authorizeAccount($doctor, $user);

        // Eight digits, nothing else: these get read off a printed card and typed
        // on a phone keypad by people who are not going to manage anything more.
        // Zero-padded so a small draw still comes out eight characters long.
        $password = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        $user->update(['password' => $password]);

        return redirect()
            ->route('admin.doctors.credentials', $doctor)
            ->with(self::FLASH_USER, $user->id)
            ->with(self::FLASH_PASSWORD, $password);
    }

    /** The account must be this doctor's own login or one of their assistants. */
    private function authorizeAccount(Doctor $doctor, User $user): void
    {
        $isDoctorAccount = (int) $doctor->user_id === (int) $user->id;
        $isAssistant = (int) $user->doctor_id === (int) $doctor->id;

        abort_unless($isDoctorAccount || $isAssistant, 403);
    }
}
