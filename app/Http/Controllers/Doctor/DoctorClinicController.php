<?php

namespace App\Http\Controllers\Doctor;

use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Governorate;
use Illuminate\Http\Request;

class DoctorClinicController extends DoctorDashboardController
{
    public function create(Request $request)
    {
        $doctor = $this->doctor($request);
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $governorateId = old('governorate_id', $request->governorate_id);
        $cityId = old('city_id', $request->city_id);
        $cities = $governorateId
            ? City::where('governorate_id', $governorateId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $areas = $cityId
            ? Area::where('city_id', $cityId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        return view('doctor.clinics.create', compact('doctor', 'governorates', 'cities', 'areas'));
    }

    public function store(Request $request)
    {
        $doctor = $this->doctor($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_examination_price' => 'required|numeric|min:0',
            'follow_up_price' => 'required|numeric|min:0',
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'required_with:working_hours.*|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'working_hours.*.from' => 'nullable|date_format:H:i',
            'working_hours.*.to' => 'nullable|date_format:H:i',
            'working_hours.*.is_closed' => 'nullable|boolean',
            'slots_per_interval' => 'nullable|integer|min:1|max:20',
        ]);
        $clinic = Clinic::create([
            'doctor_id' => $doctor->id,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'governorate_id' => $validated['governorate_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'medical_examination_price' => (float) $validated['medical_examination_price'],
            'follow_up_price' => (float) $validated['follow_up_price'],
            'slots_per_interval' => (int) ($validated['slots_per_interval'] ?? 1),
        ]);
        $clinic->doctors()->sync([$doctor->id => []]);
        $this->syncWorkingHours($clinic, $request->input('working_hours', []));
        return redirect()->route('doctor.clinics.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء العيادة بنجاح' : 'Clinic created successfully.');
    }

    public function index(Request $request)
    {
        $doctor = $this->doctor($request);
        $clinics = Clinic::where('doctor_id', $doctor->id)
            ->orWhereHas('doctors', fn ($q) => $q->where('doctors.id', $doctor->id))
            ->with(['governorate', 'city', 'area', 'workingHours', 'doctor.specialization'])
            ->orderBy('name')
            ->get();
        return view('doctor.clinics.index', compact('doctor', 'clinics'));
    }

    public function show(Request $request, Clinic $clinic)
    {
        $doctor = $this->doctor($request);
        $this->authorizeClinic($doctor, $clinic);
        $clinic->load(['governorate', 'city', 'area', 'workingHours', 'doctor.specialization', 'doctors.specialization']);
        return view('doctor.clinics.show', compact('doctor', 'clinic'));
    }

    public function edit(Request $request, Clinic $clinic)
    {
        $doctor = $this->doctor($request);
        $this->authorizeClinic($doctor, $clinic);
        $clinic->load(['workingHours', 'governorate', 'city', 'area']);
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $governorateId = old('governorate_id', $clinic->governorate_id);
        $cityId = old('city_id', $clinic->city_id);
        $cities = $governorateId
            ? City::where('governorate_id', $governorateId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $areas = $cityId
            ? Area::where('city_id', $cityId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        return view('doctor.clinics.edit', compact('doctor', 'clinic', 'governorates', 'cities', 'areas'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $doctor = $this->doctor($request);
        $this->authorizeClinic($doctor, $clinic);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_examination_price' => 'required|numeric|min:0',
            'follow_up_price' => 'required|numeric|min:0',
            'slots_per_interval' => 'nullable|integer|min:1|max:20',
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'required_with:working_hours.*|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'working_hours.*.from' => 'nullable|date_format:H:i',
            'working_hours.*.to' => 'nullable|date_format:H:i',
            'working_hours.*.is_closed' => 'nullable|boolean',
        ]);
        $clinic->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'governorate_id' => $validated['governorate_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'medical_examination_price' => (float) $validated['medical_examination_price'],
            'follow_up_price' => (float) $validated['follow_up_price'],
            'slots_per_interval' => (int) ($validated['slots_per_interval'] ?? 1),
        ]);
        $this->syncWorkingHours($clinic, $request->input('working_hours', []));
        return redirect()->route('doctor.clinics.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث العيادة بنجاح' : 'Clinic updated successfully.');
    }

    /**
     * Quick-add a city under a governorate from the clinic form.
     */
    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
        ]);

        $name = trim($validated['name']);
        $nameAr = trim($validated['name_ar'] ?? '') ?: $name;

        $city = City::where('governorate_id', $validated['governorate_id'])
            ->where(fn ($q) => $q->where('name', $name)->orWhere('name_ar', $nameAr))
            ->first();

        if (!$city) {
            $city = City::create([
                'governorate_id' => $validated['governorate_id'],
                'name' => $name,
                'name_ar' => $nameAr,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $city->id,
                'name' => $city->name,
                'name_ar' => $city->name_ar,
            ],
        ]);
    }

    /**
     * Quick-add an area under a city from the clinic form.
     */
    public function storeArea(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
        ]);

        $name = trim($validated['name']);
        $nameAr = trim($validated['name_ar'] ?? '') ?: $name;

        $area = Area::where('city_id', $validated['city_id'])
            ->where(fn ($q) => $q->where('name', $name)->orWhere('name_ar', $nameAr))
            ->first();

        if (!$area) {
            $area = Area::create([
                'city_id' => $validated['city_id'],
                'name' => $name,
                'name_ar' => $nameAr,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $area->id,
                'name' => $area->name,
                'name_ar' => $area->name_ar,
            ],
        ]);
    }

    protected function authorizeClinic($doctor, Clinic $clinic): void
    {
        if ($clinic->doctor_id !== $doctor->id && !$clinic->doctors()->where('doctors.id', $doctor->id)->exists()) {
            abort(403);
        }
    }

    protected function syncWorkingHours(Clinic $clinic, array $rows): void
    {
        $clinic->workingHours()->delete();
        $daysSeen = [];
        foreach ($rows ?? [] as $row) {
            $day = $row['day'] ?? null;
            if (!$day || isset($daysSeen[$day])) {
                continue;
            }
            $daysSeen[$day] = true;
            $isClosed = !empty($row['is_closed']) || (empty($row['from']) && empty($row['to']));
            $clinic->workingHours()->create([
                'day' => $day,
                'from' => $isClosed ? null : ($row['from'] ?? null),
                'to' => $isClosed ? null : ($row['to'] ?? null),
                'is_closed' => $isClosed,
            ]);
        }

        // Mirror the schedule into the clinic-system opening_hours JSON so the
        // "practice" clinic page and calendar show the same working days.
        $clinic->syncOpeningHoursFromWorkingHours();
    }
}
