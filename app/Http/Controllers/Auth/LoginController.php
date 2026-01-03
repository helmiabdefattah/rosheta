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
//        dd($request);
        $request->validate([
            'email' => 'required|string', // Changed from 'email' to 'string' to accept phone or email
            'password' => 'required|string',
        ]);

        $login = $request->email; // Can be email or phone number
        $password = $request->password;
        $remember = $request->filled('remember');

        // First, try to authenticate as a User (admin/lab owner/nurse) - only by email
        $user = User::where('email', $login)->first();
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Redirect laboratory owners to their dashboard
            if ($user->laboratory_id) {
                return redirect()->route('laboratories.dashboard');
            }

            // Redirect pharmacy owners to admin dashboard (or pharmacy dashboard if exists)
            if ($user->pharmacy_id) {
                return redirect()->route('admin.dashboard');
            }

            // Redirect nurses to admin dashboard (or nurse dashboard if exists)
            if ($user->nurse_id) {
                return redirect()->route('admin.dashboard');
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
