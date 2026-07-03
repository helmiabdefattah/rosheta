<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Adds N random walk-in patients (each a new Client + a scheduled Appointment)
 * to the demo clinic created by {@see \Database\Seeders\ClinicDemoSeeder} on a
 * given day, so the doctor / assistant dashboards can be populated on demand.
 *
 *   php artisan clinic:add-random-patients                 # today, 5 patients
 *   php artisan clinic:add-random-patients 2026-07-10      # that day, 5
 *   php artisan clinic:add-random-patients 2026-07-10 12   # that day, 12
 */
class AddRandomClinicPatients extends Command
{
    protected $signature = 'clinic:add-random-patients
                            {day? : The day to add patients on (Y-m-d). Defaults to today.}
                            {count=5 : How many random patients to add.}';

    protected $description = 'Add random patients (appointments) to the demo clinic on a given day';

    /** Arabic name pools, so generated patients match the demo data style. */
    private const MALE_FIRST = ['أحمد', 'محمد', 'محمود', 'خالد', 'عمر', 'مصطفى', 'كريم', 'يوسف', 'إبراهيم', 'حسن', 'علي', 'طارق', 'وليد', 'هشام', 'سامح'];
    private const FEMALE_FIRST = ['سارة', 'فاطمة', 'منى', 'هبة', 'ريم', 'نورا', 'مريم', 'دعاء', 'ياسمين', 'أميرة', 'رنا', 'شيماء', 'إسراء', 'دينا', 'سلمى'];
    private const LAST = ['عبد الله', 'محمود', 'السيد', 'مصطفى', 'عبد الرحمن', 'إبراهيم', 'كمال', 'حسن', 'فؤاد', 'عبد العزيز', 'رشاد', 'صبري', 'زكي', 'عثمان', 'شعبان'];

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        if ($count < 1) {
            $this->error('count must be a positive integer.');

            return self::FAILURE;
        }

        $dayArg = $this->argument('day');
        try {
            $day = $dayArg ? Carbon::createFromFormat('Y-m-d', $dayArg)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            $this->error("Invalid day \"{$dayArg}\". Use the format Y-m-d, e.g. 2026-07-10.");

            return self::FAILURE;
        }

        // --- Locate the demo clinic (matching ClinicDemoSeeder) -------------
        $doctor = Doctor::where('slug', 'demo-clinic-doctor')->first();
        if (! $doctor) {
            $this->error('Demo doctor not found. Run: php artisan db:seed --class=ClinicDemoSeeder');

            return self::FAILURE;
        }

        $clinic = Clinic::where('doctor_id', $doctor->id)
            ->where('name', 'عيادة الروشتة التجريبية')
            ->first()
            ?? Clinic::where('doctor_id', $doctor->id)->first();

        if (! $clinic) {
            $this->error('Demo clinic not found. Run: php artisan db:seed --class=ClinicDemoSeeder');

            return self::FAILURE;
        }

        // Warn (but proceed) if the clinic is closed that weekday.
        $weekday = strtolower($day->format('l'));
        $hours = $clinic->workingHours()->where('day', $weekday)->first();
        if ($hours && $hours->is_closed) {
            $this->warn("Note: the clinic is normally closed on {$day->format('l')} — adding patients anyway.");
        }

        // Start time: the clinic's opening time that weekday, else 09:00. The
        // `from` column is cast to datetime, so normalize to a bare H:i string —
        // passing the Carbon straight to setTimeFromTimeString() would drag its
        // (today's) date onto $day and misplace every appointment.
        $startTime = $hours && ! $hours->is_closed && $hours->from
            ? $hours->from->format('H:i')
            : '09:00';
        $duration = (int) ($clinic->appointment_duration ?: 30);

        // Continue numbering / timing after whatever already exists that day.
        $existing = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $day)
            ->count();
        $maxQueue = (int) Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $day)
            ->max('queue_number');

        $this->info("Adding {$count} random patient(s) to \"{$clinic->name}\" on {$day->toDateString()} ({$day->format('l')})…");

        $created = [];

        DB::transaction(function () use ($count, $day, $doctor, $clinic, $startTime, $duration, $existing, $maxQueue, &$created) {
            for ($i = 0; $i < $count; $i++) {
                $client = $this->makeRandomPatient();

                $slot = $existing + $i;
                $scheduledAt = $day->copy()
                    ->setTimeFromTimeString($startTime)
                    ->addMinutes($slot * $duration);

                $isFollowUp = random_int(0, 3) === 0;

                Appointment::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'client_id' => $client->id,
                    'scheduled_at' => $scheduledAt,
                    'queue_number' => $maxQueue + $i + 1,
                    'source' => 'system',
                    'type' => $isFollowUp ? 'follow_up' : 'medical_examination',
                    'price' => $isFollowUp ? $clinic->follow_up_price : $clinic->medical_examination_price,
                    'status' => 'scheduled',
                    'reason' => $isFollowUp ? 'متابعة الحالة' : 'كشف جديد',
                ]);

                $created[] = [$maxQueue + $i + 1, $client->name, $scheduledAt->format('H:i')];
            }
        });

        $this->table(['#', 'Patient', 'Time'], $created);
        $this->info("Done. Added {$count} patient(s) on {$day->toDateString()}.");

        return self::SUCCESS;
    }

    /** Build and persist one random patient with an Arabic name and unique phone. */
    private function makeRandomPatient(): Client
    {
        $gender = random_int(0, 1) === 0 ? 'male' : 'female';
        $first = $gender === 'male'
            ? self::MALE_FIRST[array_rand(self::MALE_FIRST)]
            : self::FEMALE_FIRST[array_rand(self::FEMALE_FIRST)];
        $name = $first . ' ' . self::LAST[array_rand(self::LAST)];

        // Egyptian-style 11-digit mobile, guaranteed unique.
        do {
            $phone = '01' . random_int(0, 2) . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Client::where('phone_number', $phone)->exists());

        return Client::create([
            'name' => $name,
            'phone_number' => $phone,
            'email' => 'rand.patient.' . $phone . '@rosheta.test',
            'password' => Hash::make('password'),
            'gender' => $gender,
            'dob' => Carbon::today()->subYears(random_int(5, 80))->subDays(random_int(0, 364))->toDateString(),
        ]);
    }
}
