<?php

namespace App\Http\Controllers\Doctor;

use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DoctorProfileController extends DoctorDashboardController
{
    public function edit(Request $request)
    {
        $doctor = $this->doctor($request);
        $doctor->load(['user', 'specialization']);
        $specializations = Specialization::orderBy('name')->get();
        return view('doctor.profile.edit', compact('doctor', 'specializations'));
    }

    public function update(Request $request)
    {
        $doctor = $this->doctor($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:doctors,slug,' . $doctor->id,
            'brief' => 'nullable|string|max:5000',
            'specialization_id' => 'required|exists:specializations,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|min:8|confirmed',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $doctor->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'brief' => $validated['brief'] ?? null,
            'specialization_id' => $validated['specialization_id'],
        ]);
        if ($request->hasFile('profile_image')) {
            $doctor->clearMediaCollection('profile_image');
            $doctor->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }
        $user = $doctor->user;
        if ($user) {
            if (!empty($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['phone_number'])) {
                $user->phone_number = $validated['phone_number'];
            }
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        }
        return redirect()->route('doctor.profile.edit')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الملف الشخصي' : 'Profile updated.');
    }
}
