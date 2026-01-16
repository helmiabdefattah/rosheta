<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $pharmacy = Pharmacy::find($user->pharmacy_id);

        if (!$pharmacy) {
            return redirect()->route('pharmacies.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي صيدلية.' : 'You are not associated with any pharmacy.');
        }

        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('pharmacies.profile.edit', compact('pharmacy', 'users', 'areas'));
    }

    public function update(Request $request, Pharmacy $pharmacy)
    {
        // Verify user owns this pharmacy
        if (Auth::user()->pharmacy_id != $pharmacy->id) {
            return redirect()->route('pharmacies.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بتعديل هذه الصيدلية.' : 'You are not authorized to edit this pharmacy.');
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
            'pharmacist_name' => 'nullable|string|max:255',
            'pharmacist_license' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        DB::beginTransaction();
        try {
            // Update pharmacy basic info
            $pharmacy->update($validated);

            // Handle pharmacist license upload (if Pharmacy implements HasMedia)
            // For now, we'll skip this as Pharmacy doesn't use Spatie Media Library
            // You can add it later if needed

            DB::commit();

            return redirect()->route('pharmacies.profile.edit')
                ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث معلومات الصيدلية بنجاح' : 'Pharmacy information updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', app()->getLocale() === 'ar' ? 'حدث خطأ أثناء التحديث' : 'An error occurred while updating');
        }
    }
}
