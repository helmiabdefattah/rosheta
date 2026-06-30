<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Server-to-server API consumed by an external clinic-management system.
 *
 * Every request is authenticated by the `integration` middleware, which
 * resolves the calling clinic from its key and attaches it to the request.
 * All data returned here is therefore scoped to that single clinic.
 */
class IntegrationController extends Controller
{
    private function clinic(Request $request): Clinic
    {
        return $request->attributes->get('integration_clinic');
    }

    /**
     * Map a rosheta status the way the external system expects, and accept the
     * external system's vocabulary when it pushes a status back.
     */
    private const STATUS_FROM_EXTERNAL = [
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'escaped' => 'missed',           // no-show
        'under_examination' => 'confirmed',
        'scheduled' => 'confirmed',
        // passthrough for native rosheta statuses
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'missed' => 'missed',
    ];

    /** Clinic profile, opening hours and capacity. */
    public function clinic_(Request $request): JsonResponse
    {
        $clinic = $this->clinic($request)->load(['workingHours', 'doctors', 'doctor.specialization']);

        return response()->json([
            'data' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'phone' => $clinic->phone_number,
                'address' => $clinic->address,
                'slots_per_interval' => $clinic->getSlotsPerInterval(),
                'opening_hours' => $this->openingHours($clinic),
                'doctors' => $this->doctorList($clinic),
            ],
        ]);
    }

    /**
     * Update this clinic's working hours (and optionally slot capacity) from
     * the external clinic-management system.
     */
    public function updateClinic(Request $request): JsonResponse
    {
        $clinic = $this->clinic($request);

        $data = $request->validate([
            'opening_hours' => ['required', 'array'],
            'opening_hours.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.closed' => ['nullable', 'boolean'],
            'slots_per_interval' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($data['opening_hours'] as $day => $h) {
            $day = strtolower($day);
            if (! array_key_exists($day, ClinicWorkingHour::DAYS)) {
                continue;
            }

            $closed = (bool) ($h['closed'] ?? false);

            ClinicWorkingHour::updateOrCreate(
                ['clinic_id' => $clinic->id, 'day' => $day],
                [
                    'from' => $closed ? null : ($h['open'] ?? null),
                    'to' => $closed ? null : ($h['close'] ?? null),
                    'is_closed' => $closed,
                ]
            );
        }

        if (isset($data['slots_per_interval'])) {
            $clinic->update(['slots_per_interval' => $data['slots_per_interval']]);
        }

        return response()->json([
            'message' => 'Clinic updated.',
            'data' => ['opening_hours' => $this->openingHours($clinic->load('workingHours'))],
        ]);
    }

    /** Doctors who work at this clinic. */
    public function doctors(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->doctorList($this->clinic($request))]);
    }

    /** Patients (clients) who have appointments at this clinic. */
    public function patients(Request $request): JsonResponse
    {
        $clinic = $this->clinic($request);

        $clients = $clinic->appointments()
            ->with('client.governorate', 'client.city')
            ->get()
            ->pluck('client')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone_number,
                'email' => $c->email,
                'governorate' => $c->governorate->name ?? null,
                'city' => $c->city->name ?? null,
            ]);

        return response()->json(['data' => $clients]);
    }

    /**
     * Appointments for this clinic. Defaults to confirmed appointments from
     * today onward; supports ?status=, ?date=YYYY-MM-DD, ?from=, ?to=.
     */
    public function appointments(Request $request): JsonResponse
    {
        $clinic = $this->clinic($request);

        $status = $request->query('status', 'confirmed');

        $query = $clinic->appointments()->with(['client', 'doctor']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($date = $request->query('date')) {
            $query->whereDate('appointment_date', $date);
        } else {
            $from = $request->query('from', Carbon::today()->toDateString());
            $to = $request->query('to', Carbon::today()->addDays(30)->toDateString());
            $query->whereBetween('appointment_date', [$from, $to]);
        }

        $appointments = $query
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get()
            ->map(fn (Appointment $a) => $this->appointmentPayload($a));

        return response()->json(['data' => $appointments]);
    }

    /**
     * Receive a status update from the external clinic system after an
     * appointment is handled there (completed / cancelled / no-show, etc.).
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $clinic = $this->clinic($request);

        if ($appointment->clinic_id !== $clinic->id) {
            return response()->json(['message' => 'Appointment does not belong to this clinic.'], 404);
        }

        $data = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $mapped = self::STATUS_FROM_EXTERNAL[$data['status']] ?? null;

        if (! $mapped) {
            return response()->json([
                'message' => "Unknown status '{$data['status']}'.",
                'accepted' => array_keys(self::STATUS_FROM_EXTERNAL),
            ], 422);
        }

        $appointment->update([
            'status' => $mapped,
            'notes' => $data['notes'] ?? $appointment->notes,
        ]);

        return response()->json([
            'message' => 'Appointment status updated.',
            'data' => $this->appointmentPayload($appointment->fresh(['client', 'doctor'])),
        ]);
    }

    /**
     * Verify a doctor's rosheta staff credentials so the external clinic app
     * can sign them in. Only doctors who actually work at the calling clinic
     * are accepted.
     */
    public function authenticateDoctor(Request $request): JsonResponse
    {
        $clinic = $this->clinic($request);

        $data = $request->validate([
            'login' => ['required', 'string'],     // email or phone number
            'password' => ['required', 'string'],
        ]);

        $user = $this->findUserByLogin($data['login']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $doctor = $user->doctor;
        if (! $doctor) {
            return response()->json(['message' => 'This account is not a doctor.'], 403);
        }

        $worksHere = $clinic->doctor_id === $doctor->id
            || $clinic->doctors()->where('doctors.id', $doctor->id)->exists();

        if (! $worksHere) {
            return response()->json(['message' => 'This doctor does not work at this clinic.'], 403);
        }

        return response()->json([
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization->name ?? null,
                'email' => $user->email,
                'phone' => $user->phone_number,
            ],
        ]);
    }

    // ---- helpers -------------------------------------------------------

    /** Match a staff user by email (case-insensitive) or phone number. */
    private function findUserByLogin(string $login): ?User
    {
        return User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($login, 'UTF-8')])
                ->orWhere('phone_number', $login);

            if (! str_contains($login, '@')) {
                $digits = preg_replace('/\D/', '', $login);
                if (strlen($digits) >= 8) {
                    $query->orWhere('phone_number', $digits);
                }
            }
        })->first();
    }

    private function appointmentPayload(Appointment $a): array
    {
        $time = $a->appointment_time instanceof Carbon
            ? $a->appointment_time->format('H:i')
            : Carbon::parse($a->appointment_time)->format('H:i');

        return [
            'id' => $a->id,
            'status' => $a->status,
            'type' => $a->type,
            'date' => optional($a->appointment_date)->toDateString() ?? (string) $a->appointment_date,
            'time' => $time,
            'price' => (float) $a->price,
            'notes' => $a->notes,
            'doctor' => $a->doctor ? ['id' => $a->doctor->id, 'name' => $a->doctor->name] : null,
            'patient' => $a->client ? [
                'id' => $a->client->id,
                'name' => $a->client->name,
                'phone' => $a->client->phone_number,
                'email' => $a->client->email,
            ] : null,
        ];
    }

    private function doctorList(Clinic $clinic): array
    {
        $doctors = $clinic->doctors()->with('specialization')->get();

        // Include the owner doctor if not already in the pivot list.
        if ($clinic->doctor && ! $doctors->contains('id', $clinic->doctor_id)) {
            $doctors->push($clinic->doctor->loadMissing('specialization'));
        }

        return $doctors->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'specialization' => $d->specialization->name ?? null,
        ])->values()->all();
    }

    private function openingHours(Clinic $clinic): array
    {
        $hours = [];
        foreach ($clinic->workingHours as $wh) {
            $hours[$wh->day] = [
                'open' => $wh->is_closed ? null : optional($wh->from)->format('H:i'),
                'close' => $wh->is_closed ? null : optional($wh->to)->format('H:i'),
                'closed' => (bool) $wh->is_closed,
            ];
        }

        return $hours;
    }
}
