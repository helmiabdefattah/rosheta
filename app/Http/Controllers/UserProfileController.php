<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    /**
     * Show the user profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Determine layout based on user type
        $layout = 'admin.layouts.admin';
        if ($user->laboratory_id) {
            $layout = 'laboratories.layouts.dashboard';
        } elseif ($user->pharmacy_id) {
            $layout = 'pharmacies.layouts.dashboard';
        } elseif ($user->nurse_id || $user->nurse()->exists()) {
            $layout = 'nurse.layouts.dashboard';
        }

        return view('profile.edit', compact('user', 'layout'));
    }

    /**
     * Update the user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Add notification sound setting
        $validated['notification_sound'] = $request->has('notification_sound');

        $user->update($validated);

        // Handle profile image if your User model has media
        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')
                ->toMediaCollection('profile_image');
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الملف الشخصي بنجاح' : 'Profile updated successfully');
    }
}
