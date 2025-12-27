<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientProfileController extends Controller
{
    public function edit()
    {
        $client = Auth::guard('client')->user();
        return view('client.profile.edit', compact('client'));
    }

    public function update(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($client) {
                    // Check if phone_number exists in clients table (excluding current client)
                    $existsInClients = Client::where('phone_number', $value)
                        ->where('id', '!=', $client->id)
                        ->exists();
                    
                    // Check if phone_number exists in users table
                    $existsInUsers = User::where('email', $value)->exists();
                    
                    if ($existsInClients || $existsInUsers) {
                        $fail('The phone number has already been taken.');
                    }
                },
            ],
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($client) {
                    if ($value) {
                        // Check if email exists in clients table (excluding current client)
                        $existsInClients = Client::where('email', $value)
                            ->where('id', '!=', $client->id)
                            ->exists();
                        
                        // Check if email exists in users table
                        $existsInUsers = User::where('email', $value)->exists();
                        
                        if ($existsInClients || $existsInUsers) {
                            $fail('The email has already been taken.');
                        }
                    }
                },
            ],
        ], [
            'name.required' => 'The name field is required.',
            'phone_number.required' => 'The phone number field is required.',
        ]);

        $client->update([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'] ?? $client->email,
        ]);

        return redirect()->route('client.profile.edit')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم تحديث الملف الشخصي بنجاح' 
                : 'Profile updated successfully');
    }
}

