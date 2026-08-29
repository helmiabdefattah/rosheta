<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Collection;

/**
 * Who a clinic broadcast may reach: the patients holding a live appointment
 * with this doctor today.
 *
 * The picker in the modal and the check the controller runs on what comes back
 * both read this, so the list on screen and the list the server will accept can
 * never drift apart — a submitted id that is not in here is simply ignored.
 */
class ClinicBroadcastAudience
{
    /**
     * Today's appointments, one per patient (the earliest in queue order when
     * somebody is booked twice), each with its client loaded.
     *
     * @return Collection<int, Appointment>
     */
    public static function forDoctor(Doctor $doctor): Collection
    {
        return Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', today())
            ->where('status', '!=', 'cancelled')
            ->with('client')
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->get()
            ->filter(fn (Appointment $a) => $a->client !== null)
            ->unique('client_id')
            ->values();
    }
}
