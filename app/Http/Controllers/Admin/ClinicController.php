<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\ClinicDoctorWorkingHour;
use App\Models\ClinicWorkingHour;
use App\Models\Doctor;
use App\Models\Governorate;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinic::with(['doctor.specialization', 'governorate', 'city', 'area'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhereHas('doctor', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('governorate', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    })
                    ->orWhereHas('city', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    })
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $clinics = $query->paginate(15)->withQueryString();

        return view('admin.clinics.index', compact('clinics'));
    }

    public function create()
    {
        $doctors = Doctor::with('specialization')->orderBy('name')->get();
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.clinics.create', compact('doctors', 'governorates'));
    }

    public function store(Request $request)
    {
        $doctorIds = array_values(array_unique((array) $request->input('doctor_ids', [])));
        $rules = [
            'doctor_ids' => 'required|array|min:1',
            'doctor_ids.*' => 'required|exists:doctors,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_examination_price' => 'nullable|numeric|min:0',
            'follow_up_price' => 'nullable|numeric|min:0',
            'working_hours' => 'nullable|array',
            'working_hours.*.day' => 'required_with:working_hours.*|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'working_hours.*.from' => 'nullable|date_format:H:i',
            'working_hours.*.to' => 'nullable|date_format:H:i',
            'working_hours.*.is_closed' => 'nullable|boolean',
        ];

        if (count($doctorIds) > 1) {
            foreach ($doctorIds as $id) {
                $rules["doctor_options.{$id}.medical_examination_price"] = 'required|numeric|min:0';
                $rules["doctor_options.{$id}.follow_up_price"] = 'required|numeric|min:0';
                $rules["doctor_options.{$id}.working_hours"] = 'nullable|array';
                $rules["doctor_options.{$id}.working_hours.*.day"] = 'required_with:doctor_options.'.$id.'.working_hours.*|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday';
                $rules["doctor_options.{$id}.working_hours.*.from"] = 'nullable|date_format:H:i';
                $rules["doctor_options.{$id}.working_hours.*.to"] = 'nullable|date_format:H:i';
                $rules["doctor_options.{$id}.working_hours.*.is_closed"] = 'nullable|boolean';
            }
        } else {
            $rules['medical_examination_price'] = 'required|numeric|min:0';
            $rules['follow_up_price'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $primaryDoctorId = $doctorIds[0];
        $examPrice = 0.0;
        $followUpPrice = 0.0;

        if (count($doctorIds) > 1) {
            $firstOptions = $request->input("doctor_options.{$primaryDoctorId}", []);
            $examPrice = (float) ($firstOptions['medical_examination_price'] ?? 0);
            $followUpPrice = (float) ($firstOptions['follow_up_price'] ?? 0);
        } else {
            $examPrice = (float) ($validated['medical_examination_price'] ?? 0);
            $followUpPrice = (float) ($validated['follow_up_price'] ?? 0);
        }

        $clinic = Clinic::create([
            'doctor_id' => $primaryDoctorId,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'governorate_id' => $validated['governorate_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'medical_examination_price' => $examPrice,
            'follow_up_price' => $followUpPrice,
        ]);

        $syncData = [];
        foreach ($doctorIds as $id) {
            if (count($doctorIds) > 1) {
                $opts = $request->input("doctor_options.{$id}", []);
                $syncData[$id] = [
                    'medical_examination_price' => (float) ($opts['medical_examination_price'] ?? 0),
                    'follow_up_price' => (float) ($opts['follow_up_price'] ?? 0),
                ];
            } else {
                $syncData[$id] = [];
            }
        }
        $clinic->doctors()->sync($syncData);

        if (count($doctorIds) > 1) {
            $this->syncClinicDoctorWorkingHours($clinic, $request->input('doctor_options', []));
            $firstWh = $request->input("doctor_options.{$primaryDoctorId}.working_hours", []);
            $this->syncWorkingHours($clinic, $firstWh);
        } else {
            $this->syncWorkingHours($clinic, $request->input('working_hours', []));
        }

        return redirect()->route('admin.clinics.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء العيادة بنجاح' : 'Clinic created successfully');
    }

    public function show(Clinic $clinic)
    {
        $clinic->load(['doctor.specialization', 'doctors.specialization', 'governorate', 'city', 'area', 'workingHours']);
        return view('admin.clinics.show', compact('clinic'));
    }

    public function edit(Clinic $clinic)
    {
        $clinic->load(['workingHours', 'doctors']);
        $doctors = Doctor::with('specialization')->orderBy('name')->get();
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = $clinic->governorate_id
            ? City::where('governorate_id', $clinic->governorate_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $areas = $clinic->city_id
            ? Area::where('city_id', $clinic->city_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        return view('admin.clinics.edit', compact('clinic', 'doctors', 'governorates', 'cities', 'areas'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'doctor_ids' => 'required|array|min:1',
            'doctor_ids.*' => 'required|exists:doctors,id',
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
        ]);

        $doctorIds = array_values(array_unique($validated['doctor_ids']));
        $primaryDoctorId = $doctorIds[0];
        $clinic->update([
            'doctor_id' => $primaryDoctorId,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'governorate_id' => $validated['governorate_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'medical_examination_price' => (float) ($validated['medical_examination_price'] ?? 0),
            'follow_up_price' => (float) ($validated['follow_up_price'] ?? 0),
        ]);

        $clinic->doctors()->sync($doctorIds);
        $this->syncWorkingHours($clinic, $request->input('working_hours', []));

        return redirect()->route('admin.clinics.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث العيادة بنجاح' : 'Clinic updated successfully');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return redirect()->route('admin.clinics.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف العيادة' : 'Clinic deleted');
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
    }

    protected function syncClinicDoctorWorkingHours(Clinic $clinic, array $doctorOptions): void
    {
        $clinic->clinicDoctorWorkingHours()->delete();
        foreach ($doctorOptions as $doctorId => $opts) {
            if (!is_array($opts) || !isset($opts['working_hours']) || !is_array($opts['working_hours'])) {
                continue;
            }
            $daysSeen = [];
            foreach ($opts['working_hours'] as $row) {
                $day = $row['day'] ?? null;
                if (!$day || isset($daysSeen[$day])) {
                    continue;
                }
                $daysSeen[$day] = true;
                $isClosed = !empty($row['is_closed']) || (empty($row['from']) && empty($row['to']));
                ClinicDoctorWorkingHour::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctorId,
                    'day' => $day,
                    'from' => $isClosed ? null : ($row['from'] ?? null),
                    'to' => $isClosed ? null : ($row['to'] ?? null),
                    'is_closed' => $isClosed,
                ]);
            }
        }
    }
}
