<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('laboratories.profile.edit', compact('laboratory', 'users', 'areas'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        // Verify user owns this laboratory
        if (Auth::user()->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بتعديل هذا المعمل.' : 'You are not authorized to edit this laboratory.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        $laboratory->update($validated);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $laboratory->clearMediaCollection('logo');
            $laboratory->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('laboratories.profile.edit')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث معلومات المعمل بنجاح' : 'Laboratory information updated successfully');
    }
}

