<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Governorate;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for medical centers. A center groups several clinics; each clinic
 * may have a doctor assigned or none. Clinics inherit the center's location so
 * the whole center plots as one point on the client map.
 */
class MedicalCenterController extends Controller
{
    public function index()
    {
        $centers = MedicalCenter::withCount('clinics')
            ->with('governorate', 'city')
            ->latest()
            ->paginate(20);

        return view('admin.medical-centers.index', compact('centers'));
    }

    public function create()
    {
        return view('admin.medical-centers.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateCenter($request);

        DB::transaction(function () use ($data, $request) {
            $center = MedicalCenter::create($data);
            $this->syncClinics($center, $request->input('clinics', []));
        });

        return redirect()->route('admin.medical-centers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المركز الطبي' : 'Medical center created');
    }

    public function edit(MedicalCenter $medicalCenter)
    {
        $medicalCenter->load('clinics');

        return view('admin.medical-centers.edit', array_merge(
            $this->formData(),
            ['center' => $medicalCenter],
        ));
    }

    public function update(Request $request, MedicalCenter $medicalCenter)
    {
        $data = $this->validateCenter($request);

        DB::transaction(function () use ($data, $request, $medicalCenter) {
            $medicalCenter->update($data);
            $this->syncClinics($medicalCenter, $request->input('clinics', []));
        });

        return redirect()->route('admin.medical-centers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المركز الطبي' : 'Medical center updated');
    }

    public function destroy(MedicalCenter $medicalCenter)
    {
        // Detach clinics (keep them as standalone) rather than deleting them.
        $medicalCenter->clinics()->update(['medical_center_id' => null]);
        $medicalCenter->delete();

        return redirect()->route('admin.medical-centers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المركز' : 'Medical center deleted');
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'governorates' => Governorate::where('is_active', true)->orderBy('name')->get(),
            'cities' => City::where('is_active', true)->orderBy('name')->get(['id', 'name', 'name_ar', 'governorate_id']),
            'areas' => Area::where('is_active', true)->orderBy('name')->get(['id', 'name', 'name_ar', 'city_id']),
            'doctors' => Doctor::with('specialization')->orderBy('name')->get(),
        ];
    }

    private function validateCenter(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'is_active' => 'nullable|boolean',
            'clinics' => 'nullable|array|max:50',
            'clinics.*.id' => 'nullable|integer|exists:clinics,id',
            'clinics.*.name' => 'nullable|string|max:255',
            'clinics.*.doctor_id' => 'nullable|exists:doctors,id',
            'clinics.*.medical_examination_price' => 'nullable|numeric|min:0',
        ]);

        // Only the center's own columns (clinics are handled separately).
        return [
            'name' => $request->input('name'),
            'phone_number' => $request->input('phone_number'),
            'address' => $request->input('address'),
            'governorate_id' => $request->input('governorate_id') ?: null,
            'city_id' => $request->input('city_id') ?: null,
            'area_id' => $request->input('area_id') ?: null,
            'latitude' => $request->input('latitude') ?: null,
            'longitude' => $request->input('longitude') ?: null,
            'open_time' => $request->input('open_time') ?: null,
            'close_time' => $request->input('close_time') ?: null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Create/update/delete the center's clinics from the submitted rows.
     * Clinics inherit the center's location and area fields.
     */
    private function syncClinics(MedicalCenter $center, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                continue; // skip blank rows
            }

            $attrs = [
                'name' => $name,
                'doctor_id' => $row['doctor_id'] ?? null,
                'medical_center_id' => $center->id,
                'governorate_id' => $center->governorate_id,
                'city_id' => $center->city_id,
                'area_id' => $center->area_id,
                'address' => $center->address,
                'latitude' => $center->latitude,
                'longitude' => $center->longitude,
                'medical_examination_price' => $row['medical_examination_price'] ?? 0,
            ];

            if (! empty($row['id'])) {
                $clinic = Clinic::where('medical_center_id', $center->id)->find($row['id']);
                if ($clinic) {
                    $clinic->update($attrs);
                    $keptIds[] = $clinic->id;
                    continue;
                }
            }

            $clinic = Clinic::create($attrs);
            $keptIds[] = $clinic->id;
        }

        // Remove clinics that were deleted in the form.
        Clinic::where('medical_center_id', $center->id)
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();
    }
}
