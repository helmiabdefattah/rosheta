<?php

namespace App\Http\Controllers\Doctor;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorOffDate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorCalendarController extends DoctorDashboardController
{
    public function index(Request $request)
    {
        $doctor = $this->doctor($request);
        $month = $request->get('month', now()->format('Y-m'));
        $minNavMonth = now()->copy()->subYears(5)->startOfMonth();
        $start = Carbon::parse($month . '-01')->startOfMonth();
        if ($start->lt($minNavMonth)) {
            $start = $minNavMonth->copy();
        }
        $end = $start->copy()->endOfMonth();

        $clinics = Clinic::where('doctor_id', $doctor->id)
            ->orWhereHas('doctors', fn ($q) => $q->where('doctors.id', $doctor->id))
            ->with(['workingHours', 'clinicDoctorWorkingHours' => fn ($q) => $q->where('doctor_id', $doctor->id)])
            ->get();

        $offDates = DoctorOffDate::where('doctor_id', $doctor->id)
            ->whereBetween('off_date', [$start, $end])
            ->pluck('off_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->flip()
            ->all();

        $appointmentsByDate = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['cancelled'])
            ->with(['clinic', 'client', 'user'])
            ->orderBy('appointment_time')
            ->get()
            ->groupBy(fn (Appointment $a) => $a->appointment_date->format('Y-m-d'));

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dayName = strtolower($current->format('l'));
            $schedules = [];
            foreach ($clinics as $clinic) {
                $wh = $clinic->clinicDoctorWorkingHours->firstWhere('day', $dayName);
                if (!$wh) {
                    $wh = $clinic->workingHours->firstWhere('day', $dayName);
                }
                if (!$wh) {
                    continue;
                }
                $fromTo = null;
                if (!$wh->is_closed && $wh->from && $wh->to) {
                    $from = $wh->from instanceof \Carbon\Carbon ? $wh->from->format('g:i A') : Carbon::parse($wh->from)->format('g:i A');
                    $to = $wh->to instanceof \Carbon\Carbon ? $wh->to->format('g:i A') : Carbon::parse($wh->to)->format('g:i A');
                    $fromTo = $from . ' - ' . $to;
                }
                $schedules[] = [
                    'clinic_name' => $clinic->name,
                    'from_to' => $fromTo,
                    'is_closed' => (bool) $wh->is_closed,
                ];
            }
            $has_clinic_open = collect($schedules)->contains(fn ($s) => $s['from_to'] !== null);
            $dateKey = $current->format('Y-m-d');
            $dayAppointments = $appointmentsByDate->get($dateKey, collect())->map(function (Appointment $a) {
                $t = $a->appointment_time;
                $timeStr = $t instanceof \Carbon\Carbon
                    ? $t->format('g:i A')
                    : Carbon::parse($t)->format('g:i A');

                return [
                    'time' => $timeStr,
                    'patient' => $a->client?->name ?? $a->user?->name ?? '—',
                    'clinic' => $a->clinic?->name,
                    'status' => $a->status,
                ];
            })->values()->all();
            $days[] = [
                'date' => $dateKey,
                'day' => $current->day,
                'weekday' => $current->format('l'),
                'day_name' => $dayName,
                'is_off' => isset($offDates[$dateKey]),
                'is_past' => $current->lt(now()->startOfDay()),
                'is_today' => $current->isToday(),
                'schedules' => $schedules,
                'has_clinic_open' => $has_clinic_open,
                'appointments' => $dayAppointments,
            ];
            $current->addDay();
        }

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $canGoPrev = $prevMonth->gte($minNavMonth);
        return view('doctor.calendar.index', compact('doctor', 'days', 'start', 'prevMonth', 'nextMonth', 'canGoPrev', 'offDates'));
    }

    public function toggleOff(Request $request)
    {
        $doctor = $this->doctor($request);
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);
        $dateStr = Carbon::parse($request->date)->format('Y-m-d');
        $exists = DoctorOffDate::where('doctor_id', $doctor->id)
            ->whereNull('clinic_id')
            ->where('off_date', $dateStr)
            ->first();
        if ($exists) {
            $exists->delete();
            $isOff = false;
        } else {
            DoctorOffDate::create([
                'doctor_id' => $doctor->id,
                'clinic_id' => null,
                'off_date' => $dateStr,
            ]);
            $isOff = true;
        }
        if ($request->wantsJson()) {
            return response()->json(['is_off' => $isOff]);
        }
        return redirect()->back()->with('success', $isOff
            ? (app()->getLocale() === 'ar' ? 'تم تعيين اليوم كإجازة' : 'Day set as off.')
            : (app()->getLocale() === 'ar' ? 'تم تعيين اليوم كيوم عمل' : 'Day set as working.'));
    }
}
