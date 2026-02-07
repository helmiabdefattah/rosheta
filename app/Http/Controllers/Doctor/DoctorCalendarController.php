<?php

namespace App\Http\Controllers\Doctor;

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
        $start = Carbon::parse($month . '-01');
        if ($start->lt(now()->startOfMonth())) {
            $start = now()->startOfMonth();
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
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'weekday' => $current->format('l'),
                'day_name' => $dayName,
                'is_off' => isset($offDates[$current->format('Y-m-d')]),
                'is_past' => $current->lt(now()->startOfDay()),
                'is_today' => $current->isToday(),
                'schedules' => $schedules,
                'has_clinic_open' => $has_clinic_open,
            ];
            $current->addDay();
        }

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();
        $canGoPrev = $prevMonth->gte(now()->startOfMonth());
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
