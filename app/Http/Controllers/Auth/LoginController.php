<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // If already authenticated as client, redirect to client dashboard
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }

                // If already authenticated as user, redirect based on user type
        if (Auth::check()) {
            $user = Auth::user();
            // Redirect laboratory owners to their dashboard
            if ($user->laboratory_id) {
                return redirect()->route('laboratories.dashboard');
            }
            if ($user->pharmacy_id) {
                return redirect()->route('pharmacies.dashboard');
            }
            if ($user->nurse_id) {
                return redirect()->route('nurse.dashboard');
            }
            if ($user->charitable_organization_id) {
                return redirect()->route('user.profile.edit');
            }
            // Doctors first: Clinic Quick Setup registers the doctor's own login
            // as the clinic's manager (clinics.user_id), so those accounts match
            // managedClinic too and used to land on the clinic staff portal.
            if ($user->doctor()->exists()) {
                return redirect()->route('doctor.dashboard');
            }
            if ($user->managedClinic) {
                return redirect()->route('clinic.dashboard');
            }

            // Doctor's assistant → clinic (design) system assistant workspace
            if ($user->doctor_id) {
                return redirect()->route('practice.assistant.dashboard');
            }
            // Redirect other users to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string', // Changed from 'email' to 'string' to accept phone or email
            'password' => 'required|string',
        ]);

        $login = $request->email; // Can be email or phone number
        $password = $request->password;
        $remember = $request->filled('remember');

        // First, try to authenticate as a User (admin/lab owner) - only by email
        $user = User::where(function($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('phone_number', $login);
        })->first();
        if ($user && Hash::check($password, $user->password)) {
            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'email' => [
                        app()->getLocale() === 'ar'
                            ? 'حسابك غير مفعّل. يرجى التواصل مع الإدارة لتفعيل الحساب.'
                            : 'Your account is not active. Please contact the administration to activate it.',
                    ],
                ]);
            }

            // An assistant cannot work while the doctor they assist is deactivated.
            $doctorAccount = $user->supervisingDoctorAccount();
            if ($doctorAccount && ! $doctorAccount->is_active) {
                throw ValidationException::withMessages([
                    'email' => [
                        app()->getLocale() === 'ar'
                            ? 'حساب الطبيب الذي تعمل معه غير مفعّل. يرجى التواصل مع الإدارة.'
                            : 'The account of the doctor you work with is not active. Please contact the administration.',
                    ],
                ]);
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Trigger FCM token refresh after login
            $request->session()->put('fcm_token_refresh', true);

            // Redirect laboratory owners to their dashboard
            if ($user->laboratory_id) {
                return redirect()->route('laboratories.dashboard');
            }
            if ($user->pharmacy_id) {
                return redirect()->route('pharmacies.dashboard');
            }
            if ($user->nurse_id) {
                return redirect()->route('nurse.dashboard');
            }
            if ($user->charitable_organization_id) {
                return redirect()->route('user.profile.edit');
            }
            // Doctors first — see the note in showLoginForm().
            if ($user->doctor()->exists()) {
                return redirect()->route('doctor.dashboard');
            }
            if ($user->managedClinic) {
                return redirect()->route('clinic.dashboard');
            }
            // Doctor's assistant → clinic (design) system assistant workspace
            if ($user->doctor_id) {
                return redirect()->route('practice.assistant.dashboard');
            }
            // Redirect other users to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // Then, try to authenticate as a Client - by email or phone number
        $client = Client::where(function($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('phone_number', $login);
        })->first();

        if ($client && Hash::check($password, $client->password)) {
            Auth::guard('client')->login($client, $remember);
            $request->session()->regenerate();

            // Trigger FCM token refresh after login
            $request->session()->put('fcm_token_refresh', true);

            return redirect()->route('client.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        // Logout from both guards
        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        } else {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
