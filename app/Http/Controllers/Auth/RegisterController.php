<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        // If already authenticated, redirect based on user type
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect laboratory owners to their dashboard
            if ($user->laboratory_id) {
                return redirect()->route('laboratories.dashboard');
            }
            
            // Redirect other users to admin dashboard
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        // Check if email exists in users table
        $emailExistsInUsers = User::where('email', $request->email)->exists();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:clients,phone_number',
            'email' => [
                'required',
                'email',
                'unique:clients,email',
                function ($attribute, $value, $fail) use ($emailExistsInUsers) {
                    if ($emailExistsInUsers) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'email.unique' => 'The email has already been taken.',
            'phone_number.unique' => 'The phone number has already been taken.',
        ]);

        $client = Client::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Log the client in
        Auth::guard('client')->login($client);

        $request->session()->regenerate();

        return redirect()->route('client.dashboard')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم التسجيل بنجاح! مرحباً بك.' 
                : 'Registration successful! Welcome.');
    }
}

