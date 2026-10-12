<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Governorate;
use App\Models\MedicalCenter;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin CRUD for medical centers. A center groups several clinics; each clinic
 * may have a doctor assigned (existing or created inline) or none. Clinics
 * inherit the center's location so the whole center plots as one point on the
 * client map, and each clinic carries its own pricing and weekly schedule.
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
        $this->validateClinics($request);

        DB::transaction(function () use ($data, $request) {
            $center = MedicalCenter::create($data);
            $this->syncClinics($center, $request->input('clinics', []));
        });

        return redirect()->route('admin.medical-centers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المركز الطبي' : 'Medical center created');
    }

    public function edit(MedicalCenter $medicalCenter)
    {
        $medicalCenter->load('clinics.workingHours');

        return view('admin.medical-centers.edit', array_merge(
            $this->formData(),
            ['center' => $medicalCenter],
        ));
    }

    public function update(Request $request, MedicalCenter $medicalCenter)
    {
        $data = $this->validateCenter($request);
        $this->validateClinics($request);

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
            'specializations' => Specialization::orderBy('name')->get(),
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
     * Validate the per-clinic rows. Rules are built only for rows that carry a
     * name (blank rows are skipped on save), and a row creating a new doctor
     * gets the extra doctor rules — including login-account rules only when any
     * account field was filled in.
     */
    private function validateClinics(Request $request): void
    {
        $rows = $request->input('clinics', []);
        $rules = [];

        foreach ((array) $rows as $i => $row) {
            if (! is_array($row) || trim($row['name'] ?? '') === '') {
                continue; // blank row — ignored on save
            }

            $rules["clinics.$i.name"] = 'required|string|max:255';
            $rules["clinics.$i.phone_number"] = 'nullable|string|max:50';
            $rules["clinics.$i.doctor_id"] = 'nullable|exists:doctors,id';
            $rules["clinics.$i.medical_examination_price"] = 'nullable|numeric|min:0';
            $rules["clinics.$i.follow_up_price"] = 'nullable|numeric|min:0';
            $rules["clinics.$i.appointment_duration"] = 'nullable|integer|min:5|max:240';
            $rules["clinics.$i.slots_per_interval"] = 'nullable|integer|min:1|max:50';

            if (($row['doctor_mode'] ?? 'existing') === 'new') {
                $rules["clinics.$i.new_doctor.name"] = 'required|string|max:255';
                $rules["clinics.$i.new_doctor.specialization_id"] = 'required|exists:specializations,id';

                $nd = $row['new_doctor'] ?? [];
                $wantsLogin = trim($nd['email'] ?? '') !== ''
                    || trim($nd['phone'] ?? '') !== ''
                    || ($nd['password'] ?? '') !== '';

                if ($wantsLogin) {
                    $rules["clinics.$i.new_doctor.email"] = 'required|email|unique:users,email';
                    $rules["clinics.$i.new_doctor.phone"] = 'required|string|max:50|unique:users,phone_number';
                    $rules["clinics.$i.new_doctor.password"] = 'required|string|min:8';
                }
            }
        }

        if ($rules) {
            $request->validate($rules);
        }
    }

    /**
     * Create/update/delete the center's clinics from the submitted rows.
     * Clinics inherit the center's location; each keeps its own pricing,
     * appointment settings and weekly opening hours.
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
                'phone_number' => $row['phone_number'] ?? null,
                'doctor_id' => $this->resolveDoctorId($row),
                'medical_center_id' => $center->id,
                'governorate_id' => $center->governorate_id,
                'city_id' => $center->city_id,
                'area_id' => $center->area_id,
                'address' => $center->address,
                'latitude' => $center->latitude,
                'longitude' => $center->longitude,
                'medical_examination_price' => $row['medical_examination_price'] ?? 0,
                'follow_up_price' => ($row['follow_up_price'] ?? '') !== '' ? $row['follow_up_price'] : null,
                'appointment_duration' => (int) ($row['appointment_duration'] ?? 30) ?: 30,
                'slots_per_interval' => (int) ($row['slots_per_interval'] ?? 1) ?: 1,
                'opening_hours' => $this->normalizeHours($row['hours'] ?? []),
            ];

            $clinic = null;
            if (! empty($row['id'])) {
                $clinic = Clinic::where('medical_center_id', $center->id)->find($row['id']);
            }

            if ($clinic) {
                $clinic->update($attrs);
            } else {
                $clinic = Clinic::create($attrs);
            }

            // Keep the booking-side workingHours rows in step with the JSON.
            $clinic->syncWorkingHoursFromOpeningHours();
            $keptIds[] = $clinic->id;
        }

        // Remove clinics that were deleted in the form.
        Clinic::where('medical_center_id', $center->id)
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();
    }

    /**
     * Resolve the doctor for a clinic row: an existing doctor, a brand-new one
     * created inline (optionally with a login account), or none.
     */
    private function resolveDoctorId(array $row): ?int
    {
        if (($row['doctor_mode'] ?? 'existing') !== 'new') {
            return ! empty($row['doctor_id']) ? (int) $row['doctor_id'] : null;
        }

        $nd = $row['new_doctor'] ?? [];
        $name = trim($nd['name'] ?? '');
        if ($name === '') {
            return null;
        }

        $userId = null;
        $email = trim($nd['email'] ?? '');
        $phone = trim($nd['phone'] ?? '');
        $password = $nd['password'] ?? '';

        // A login account is created only when email, phone and password are all
        // present (validateClinics already guaranteed this when any was filled).
        if ($email !== '' && $phone !== '' && $password !== '') {
            $userId = User::create([
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'password' => $password,
                'is_active' => true,
            ])->id;
        }

        $doctor = Doctor::create([
            'name' => $name,
            'specialization_id' => $nd['specialization_id'] ?? null,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'user_id' => $userId,
            'assistant_limit' => Doctor::DEFAULT_ASSISTANT_LIMIT,
        ]);

        return $doctor->id;
    }

    /**
     * Shape the submitted weekly hours into the opening_hours JSON the Clinic
     * model expects: every weekday present, with null times when closed.
     */
    private function normalizeHours(array $hours): array
    {
        $out = [];

        foreach (Clinic::DAYS as $day) {
            $h = $hours[$day] ?? [];
            $closed = ! empty($h['closed']);

            $out[$day] = [
                'open' => $closed ? null : (($h['open'] ?? '') ?: null),
                'close' => $closed ? null : (($h['close'] ?? '') ?: null),
                'closed' => $closed,
            ];
        }

        return $out;
    }
}
