<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo data for the clinic (design) system: one doctor, their clinic, a
 * doctor's assistant, a handful of patients, and a month of appointments so the
 * doctor / assistant dashboards and the booking flow can be tested end-to-end.
 *
 * Run with:  php artisan db:seed --class=ClinicDemoSeeder
 *
 * All accounts use the password: password
 *   Doctor    → demo.doctor@rosheta.test     (or phone 01000000010)
 *   Assistant → demo.assistant@rosheta.test  (or phone 01000000011)
 *
 * Idempotent: safe to re-run — accounts are matched by email and this doctor's
 * appointments are rebuilt each time.
 */
class ClinicDemoSeeder extends Seeder
{
    private const PASSWORD = 'password';

    public function run(): void
    {
        // --- Specialization -------------------------------------------------
        $specialization = Specialization::firstOrCreate(
            ['slug' => 'internal-medicine'],
            ['name' => 'باطنة عامة', 'brief' => 'General internal medicine']
        );

        // --- Doctor user + profile -----------------------------------------
        $doctorUser = User::updateOrCreate(
            ['email' => 'demo.doctor@rosheta.test'],
            [
                'name' => 'د. سمير عبد الله',
                'phone_number' => '01000000010',
                'password' => Hash::make(self::PASSWORD),
                'is_active' => true,
            ]
        );

        $doctor = Doctor::updateOrCreate(
            ['slug' => 'demo-clinic-doctor'],
            [
                'name' => 'د. سمير عبد الله',
                'brief' => 'استشاري باطنة عامة — حساب تجريبي لنظام العيادة.',
                'specialization_id' => $specialization->id,
                'user_id' => $doctorUser->id,
            ]
        );

        // --- Clinic ---------------------------------------------------------
        $clinic = Clinic::updateOrCreate(
            ['doctor_id' => $doctor->id, 'name' => 'عيادة الروشتة التجريبية'],
            [
                'address' => '15 شارع التحرير، الدقي، الجيزة',
                'phone_number' => '0233334444',
                'medical_examination_price' => 200,
                'follow_up_price' => 100,
                'appointment_duration' => 30,
                'display_show_next_button' => true,
            ]
        );

        // Weekly hours: open Sat–Thu 09:00–17:00, closed Friday. workingHours is
        // the canonical source; mirror it into the opening_hours JSON.
        foreach (Clinic::DAYS as $day) {
            $closed = $day === 'friday';
            $clinic->workingHours()->updateOrCreate(
                ['day' => $day],
                [
                    'from' => $closed ? null : '09:00',
                    'to' => $closed ? null : '17:00',
                    'is_closed' => $closed,
                ]
            );
        }
        $clinic->syncOpeningHoursFromWorkingHours();

        // --- Assistant (users.doctor_id → this doctor) ----------------------
        User::updateOrCreate(
            ['email' => 'demo.assistant@rosheta.test'],
            [
                'name' => 'منال إبراهيم (مساعدة الطبيب)',
                'phone_number' => '01000000011',
                'password' => Hash::make(self::PASSWORD),
                'is_active' => true,
                'doctor_id' => $doctor->id,
            ]
        );

        // --- Demo patients --------------------------------------------------
        $patientsData = [
            ['name' => 'أحمد كمال',      'gender' => 'male',   'phone' => '01111100001', 'dob' => '1990-04-12'],
            ['name' => 'سارة محمود',     'gender' => 'female', 'phone' => '01111100002', 'dob' => '1985-09-30'],
            ['name' => 'محمد عبد الرحمن', 'gender' => 'male',   'phone' => '01111100003', 'dob' => '2001-01-05'],
            ['name' => 'فاطمة السيد',    'gender' => 'female', 'phone' => '01111100004', 'dob' => '1978-06-22'],
            ['name' => 'خالد مصطفى',     'gender' => 'male',   'phone' => '01111100005', 'dob' => '1995-11-17'],
        ];

        $patients = collect($patientsData)->map(function (array $p, int $i) {
            return Client::updateOrCreate(
                ['phone_number' => $p['phone']],
                [
                    'name' => $p['name'],
                    'email' => 'demo.patient' . ($i + 1) . '@rosheta.test',
                    'password' => Hash::make(self::PASSWORD),
                    'gender' => $p['gender'],
                    'dob' => $p['dob'],
                ]
            );
        });

        // --- Appointments for the coming month ------------------------------
        // Rebuild this doctor's appointments so the seeder stays idempotent.
        Appointment::where('doctor_id', $doctor->id)->delete();

        $today = Carbon::today();
        $end = $today->copy()->addMonth();
        $times = ['09:00', '09:30', '10:00', '10:30', '11:00'];
        $created = 0;
        $showcaseDay = null; // first open day — gets a lifelike status mix
        $pendingBudget = 4;  // a few platform bookings awaiting confirmation

        for ($date = $today->copy(); $date->lte($end); $date->addDay()) {
            // Clinic is closed on Friday.
            if ($date->isFriday()) {
                continue;
            }

            if ($showcaseDay === null) {
                $showcaseDay = $date->copy();
            }

            // 3 appointments per day, patients round-robin.
            $queue = 0;
            for ($slot = 0; $slot < 3; $slot++) {
                $patient = $patients[($created) % $patients->count()];
                $scheduledAt = $date->copy()->setTimeFromTimeString($times[$slot]);
                $isFollowUp = ($created % 4) === 0;

                $status = $this->statusFor($date, $showcaseDay, $slot, $pendingBudget);
                if ($status === 'pending') {
                    $pendingBudget--;
                }

                Appointment::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'client_id' => $patient->id,
                    'scheduled_at' => $scheduledAt,
                    'queue_number' => ++$queue,
                    'source' => $status === 'pending' ? 'reservation' : 'system',
                    'type' => $isFollowUp ? 'follow_up' : 'medical_examination',
                    'price' => $isFollowUp ? $clinic->follow_up_price : $clinic->medical_examination_price,
                    'status' => $status,
                    'reason' => $isFollowUp ? 'متابعة الحالة' : 'كشف جديد',
                ]);

                $created++;
            }
        }

        // --- Diagnosis + prescription for each completed visit --------------
        // Gives the patient's "My Prescriptions" page real data to open/print.
        $completed = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->with('client')
            ->get();

        $rxTemplates = [
            [
                'diagnosis' => 'التهاب الجهاز التنفسي العلوي',
                'treatment_plan' => 'راحة، سوائل دافئة، ومتابعة بعد أسبوع إذا استمرت الأعراض.',
                'items' => [
                    ['medicine_name' => 'Augmentin 1g', 'dose' => 'قرص', 'frequency' => 'مرتين يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                    ['medicine_name' => 'Paracetamol 500mg', 'dose' => 'قرص', 'frequency' => 'عند اللزوم', 'duration' => '5 أيام', 'instructions' => 'كل 8 ساعات للحرارة'],
                    ['medicine_name' => 'Vitamin C 1000mg', 'dose' => 'قرص فوار', 'frequency' => 'مرة يومياً', 'duration' => '14 يوم', 'instructions' => 'يذاب في ماء'],
                ],
            ],
            [
                'diagnosis' => 'ارتفاع ضغط الدم — متابعة',
                'treatment_plan' => 'تقليل الملح، متابعة الضغط يومياً، وإعادة الكشف بعد شهر.',
                'items' => [
                    ['medicine_name' => 'Concor 5mg', 'dose' => 'قرص', 'frequency' => 'مرة صباحاً', 'duration' => '30 يوم', 'instructions' => 'قبل الإفطار'],
                    ['medicine_name' => 'Aspocid 75mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'بعد الغداء'],
                ],
            ],
        ];

        $rxCount = 0;
        foreach ($completed as $i => $appointment) {
            $template = $rxTemplates[$i % count($rxTemplates)];

            $diagnosis = Diagnosis::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'client_id' => $appointment->client_id,
                    'doctor_id' => $doctor->id,
                    'diagnosis' => $template['diagnosis'],
                    'treatment_plan' => $template['treatment_plan'],
                ]
            );

            $prescription = $appointment->prescriptions()->create([
                'code' => 'RX-' . strtoupper(Str::random(8)),
                'client_id' => $appointment->client_id,
                'doctor_id' => $doctor->id,
                'diagnosis_id' => $diagnosis->id,
                'notes' => 'يرجى الالتزام بالجرعات ومتابعة الطبيب عند الحاجة.',
            ]);

            foreach ($template['items'] as $item) {
                $prescription->items()->create($item);
            }

            $rxCount++;
        }

        $this->command->info("Seeded clinic demo: doctor, clinic, assistant, {$patients->count()} patients, {$created} appointments, {$rxCount} prescriptions.");
        $this->command->info('Login (password: ' . self::PASSWORD . ')');
        $this->command->info('  Doctor    → demo.doctor@rosheta.test    / 01000000010');
        $this->command->info('  Assistant → demo.assistant@rosheta.test / 01000000011');
    }

    /**
     * Give the first open day a lifelike queue (one done, one in exam, rest
     * waiting), mark a few later slots as platform "pending" bookings the front
     * desk still needs to confirm, and leave everything else scheduled.
     */
    private function statusFor(Carbon $date, Carbon $showcaseDay, int $slot, int $pendingBudget): string
    {
        if ($date->isSameDay($showcaseDay)) {
            return match ($slot) {
                0 => 'completed',
                1 => 'under_examination',
                default => 'scheduled',
            };
        }

        // A few upcoming platform bookings awaiting confirmation.
        if ($pendingBudget > 0 && $slot === 2) {
            return 'pending';
        }

        return 'scheduled';
    }
}
