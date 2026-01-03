<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use App\Models\WorkingHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $workingHours = $laboratory->workingHours;

        return view('laboratories.profile.edit', compact('laboratory', 'users', 'areas', 'workingHours'));
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
            'manager_license' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'working_hours' => 'nullable|array',
            'working_hours.default_opening_time' => 'nullable|date_format:H:i',
            'working_hours.default_closing_time' => 'nullable|date_format:H:i',
        ]);

        // Validate working hours for each day
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        foreach ($days as $day) {
            $request->validate([
                "working_hours.{$day}_status" => 'nullable|in:off,default,custom',
                "working_hours.{$day}_opening_time" => 'nullable|date_format:H:i',
                "working_hours.{$day}_closing_time" => 'nullable|date_format:H:i',
            ]);
        }

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        DB::beginTransaction();
        try {
            // Update laboratory basic info (excluding working_hours)
            $labData = $validated;
            unset($labData['working_hours']);
            $laboratory->update($labData);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $laboratory->clearMediaCollection('logo');
                $laboratory->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            // Handle manager license upload
            if ($request->hasFile('manager_license')) {
                $laboratory->clearMediaCollection('manager_license');
                $laboratory->addMediaFromRequest('manager_license')
                    ->toMediaCollection('manager_license');
            }

            // Handle working hours
            if ($request->has('working_hours')) {
                $workingHoursData = $request->input('working_hours');
                
                // Process each day - if status is 'default' or 'off', clear custom times
                $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                foreach ($days as $day) {
                    $status = $workingHoursData[$day . '_status'] ?? 'default';
                    if ($status === 'off' || $status === 'default') {
                        $workingHoursData[$day . '_opening_time'] = null;
                        $workingHoursData[$day . '_closing_time'] = null;
                    } elseif ($status === 'custom') {
                        // Keep custom times as is
                    }
                }

                $workingHours = $laboratory->workingHours;
                if ($workingHours) {
                    $workingHours->update($workingHoursData);
                } else {
                    $workingHoursData['workable_id'] = $laboratory->id;
                    $workingHoursData['workable_type'] = Laboratory::class;
                    WorkingHours::create($workingHoursData);
                }
            }

            DB::commit();

            return redirect()->route('laboratories.profile.edit')
                ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث معلومات المعمل بنجاح' : 'Laboratory information updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', app()->getLocale() === 'ar' ? 'حدث خطأ أثناء التحديث' : 'An error occurred while updating');
        }
    }
}

