<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentInsurance;
use App\Models\AppointmentItem;
use App\Models\BillableItem;
use App\Models\Client;
use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\Clinic;
use App\Models\Collection as PaymentCollection;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\ExaminationField;
use App\Models\InsuranceCompany;
use App\Models\Laboratory;
use App\Models\MedicalPlan;
use App\Models\MedicalRequest;
use App\Models\MedicalTest;
use App\Models\Offer;
use App\Models\OfferLine;
use App\Models\PatientTest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-shot onboarding for the clinic (design) system: creates a doctor with a
 * login, their clinic and working hours, a doctor's assistant, and — optionally
 * — a full set of demo data (patients with medical profiles, appointments for
 * today and the next few days, diagnoses with treatment roadmaps, prescriptions,
 * lab / radiology orders and results, custom examination fields, billable extras,
 * payments and insurance splits) so the whole workspace can be demoed at once.
 *
 * Used by Admin\ClinicOnboardingController; everything runs in one transaction.
 */
class ClinicOnboardingService
{
    /** Days the clinic is open by default (Friday off, Egyptian week). */
    public const DEFAULT_CLOSED_DAYS = ['friday'];

    /** Files generated for demo test results live here on the public disk. */
    private const RESULTS_DIR = 'test-results';

    /** Paths written during this run, so a failed transaction can clean up. */
    private array $writtenFiles = [];

    /**
     * Create the doctor / clinic / assistant (and optionally the demo data).
     *
     * @param  array  $input  Validated payload from the admin form.
     * @return array{doctor: Doctor, clinic: Clinic, doctor_user: User, assistant_user: User, credentials: array, stats: array}
     */
    public function onboard(array $input): array
    {
        $this->writtenFiles = [];

        try {
            return DB::transaction(fn () => $this->run($input));
        } catch (\Throwable $e) {
            // The DB rolled back; drop any result files we already wrote so the
            // storage folder doesn't collect orphans.
            foreach ($this->writtenFiles as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    private function run(array $input): array
    {
        $doctorName = trim($input['doctor_name']);
        // "if I did not give you a clinic name, use the doctor name"
        $clinicName = filled($input['clinic_name'] ?? null)
            ? trim($input['clinic_name'])
            : $this->clinicNameFromDoctor($doctorName);

        $password = $input['doctor_password'] ?? $this->defaultPassword();
        $assistantPassword = $input['assistant_password'] ?? $password;

        // --- Doctor login + profile ----------------------------------------
        $doctorUser = User::create([
            'name' => $doctorName,
            'email' => $input['doctor_email'] ?? $this->uniqueEmail($doctorName, 'doctor'),
            'phone_number' => $input['doctor_phone'] ?? $this->uniquePhone(),
            'password' => $password,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialization_id' => $input['specialization_id'],
            'name' => $doctorName,
            'slug' => $this->uniqueSlug($doctorName),
            'brief' => $input['brief'] ?? null,
        ]);

        // --- Clinic ---------------------------------------------------------
        $clinic = Clinic::create([
            'doctor_id' => $doctor->id,
            'user_id' => $doctorUser->id,
            'name' => $clinicName,
            'address' => $input['address'],
            'phone_number' => $input['phone_number'] ?? null,
            'governorate_id' => $input['governorate_id'] ?? null,
            'city_id' => $input['city_id'] ?? null,
            'area_id' => $input['area_id'] ?? null,
            'latitude' => $input['latitude'] ?? null,
            'longitude' => $input['longitude'] ?? null,
            'medical_examination_price' => $input['medical_examination_price'],
            'follow_up_price' => $input['follow_up_price'],
            'appointment_duration' => $input['appointment_duration'] ?? 30,
            'slots_per_interval' => 1,
            'display_show_next_button' => true,
            'printer_language' => 'ar',
        ]);

        $clinic->doctors()->syncWithoutDetaching([
            $doctor->id => [
                'medical_examination_price' => $input['medical_examination_price'],
                'follow_up_price' => $input['follow_up_price'],
            ],
        ]);

        $this->seedWorkingHours($clinic, $doctor, $input);

        // --- Doctor's assistant ---------------------------------------------
        $assistantName = filled($input['assistant_name'] ?? null)
            ? trim($input['assistant_name'])
            : $this->assistantNameFor($clinicName);

        $assistantUser = User::create([
            'name' => $assistantName,
            'email' => $input['assistant_email'] ?? $this->uniqueEmail($doctorName, 'assistant'),
            'phone_number' => $input['assistant_phone'] ?? $this->uniquePhone(),
            'password' => $assistantPassword,
            'is_active' => true,
            'doctor_id' => $doctor->id,
        ]);

        $stats = [
            'patients' => 0, 'appointments' => 0, 'diagnoses' => 0, 'prescriptions' => 0,
            'medical_requests' => 0, 'patient_tests' => 0, 'lab_offers' => 0,
            'examination_fields' => 0, 'billable_items' => 0, 'medical_plans' => 0,
            'collections' => 0, 'insurance_splits' => 0, 'extra_items' => 0,
        ];

        if ($input['seed_demo'] ?? true) {
            $stats = $this->seedDemoData($doctor, $clinic, $assistantUser, $input, $stats);
        }

        return [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'doctor_user' => $doctorUser,
            'assistant_user' => $assistantUser,
            'credentials' => [
                'doctor' => [
                    'name' => $doctorUser->name,
                    'email' => $doctorUser->email,
                    'phone' => $doctorUser->phone_number,
                    'password' => $password,
                ],
                'assistant' => [
                    'name' => $assistantUser->name,
                    'email' => $assistantUser->email,
                    'phone' => $assistantUser->phone_number,
                    'password' => $assistantPassword,
                ],
            ],
            'stats' => $stats,
        ];
    }

    // =====================================================================
    //  Clinic setup
    // =====================================================================

    /**
     * Weekly hours for both the clinic and the clinic↔doctor pivot hours, then
     * mirror them into the opening_hours JSON the clinic system reads.
     */
    private function seedWorkingHours(Clinic $clinic, Doctor $doctor, array $input): void
    {
        $from = $input['open_from'] ?? '09:00';
        $to = $input['open_to'] ?? '17:00';
        $closedDays = $input['closed_days'] ?? self::DEFAULT_CLOSED_DAYS;

        foreach (Clinic::DAYS as $day) {
            $closed = in_array($day, $closedDays, true);
            $attributes = [
                'from' => $closed ? null : $from,
                'to' => $closed ? null : $to,
                'is_closed' => $closed,
            ];

            $clinic->workingHours()->updateOrCreate(['day' => $day], $attributes);
            $clinic->clinicDoctorWorkingHours()->updateOrCreate(
                ['day' => $day, 'doctor_id' => $doctor->id],
                $attributes
            );
        }

        $clinic->syncOpeningHoursFromWorkingHours();
    }

    // =====================================================================
    //  Demo data
    // =====================================================================

    private function seedDemoData(Doctor $doctor, Clinic $clinic, User $assistant, array $input, array $stats): array
    {
        $doctorUserId = $doctor->user_id;

        // Doctor's own setup: chargeable extras, treatment-plan templates and
        // custom examination fields — the clinic "Setup" screens.
        $billableItems = $this->seedBillableItems($doctor);
        $medicalPlans = $this->seedMedicalPlans($doctor);
        $examinationFields = $this->seedExaminationFields($doctor);
        $stats['billable_items'] = $billableItems->count();
        $stats['medical_plans'] = $medicalPlans->count();
        $stats['examination_fields'] = $examinationFields->count();

        $patients = $this->seedPatients((int) ($input['patients_count'] ?? 8));
        $stats['patients'] = $patients->count();

        $perDay = (int) ($input['appointments_per_day'] ?? 6);
        $daysAhead = (int) ($input['days_ahead'] ?? 3);

        // Past visits give each patient a real medical history to open in the
        // workspace; today + the next N days give a live queue to work through.
        $appointments = collect();

        if ($input['seed_history'] ?? true) {
            $appointments = $appointments->merge(
                $this->seedPastVisits($doctor, $clinic, $patients, (int) ($input['history_visits'] ?? 6))
            );
        }

        $appointments = $appointments->merge(
            $this->seedUpcomingAppointments($doctor, $clinic, $patients, $perDay, $daysAhead, $input)
        );

        $stats['appointments'] = $appointments->count();

        // Clinical content hangs off every visit that has actually happened.
        $visited = $appointments->whereIn('status', ['completed', 'under_examination']);

        foreach ($visited->values() as $i => $appointment) {
            $this->seedDiagnosisAndPrescription($appointment, $doctor, $i);
            $stats['diagnoses']++;
            $stats['prescriptions']++;
            $stats['medical_requests'] += $this->seedMedicalRequests($appointment, $doctor, $i);
            $stats['patient_tests'] += $this->seedPatientTests($appointment, $doctor, $doctorUserId, $i);
            $this->seedExaminationValues($appointment, $examinationFields, $i);
        }

        // Money: front-desk collections, chargeable extras and insurance splits.
        $money = $this->seedMoney($appointments, $billableItems, $assistant, $clinic);
        $stats = array_merge($stats, $money);

        // Lab / radiology results coming from the marketplace side, so the
        // examine screen's "Lab results" panel is populated too.
        $stats['lab_offers'] = $this->seedMarketplaceLabResults($patients, $doctorUserId);

        return $stats;
    }

    /** Chargeable extras the doctor can add during an examination. */
    private function seedBillableItems(Doctor $doctor): \Illuminate\Support\Collection
    {
        $items = [
            ['name' => 'جلسة علاج طبيعي', 'price' => 150],
            ['name' => 'حقن وريدي', 'price' => 75],
            ['name' => 'تغيير غيار جرح', 'price' => 60],
            ['name' => 'رسم قلب (ECG)', 'price' => 120],
            ['name' => 'قياس وظائف التنفس', 'price' => 200],
        ];

        return collect($items)->map(fn ($item) => BillableItem::create([
            'doctor_id' => $doctor->id,
            'name' => $item['name'],
            'price' => $item['price'],
            'is_active' => true,
        ]));
    }

    /**
     * Reusable treatment roadmaps ("خطة العلاج") the doctor can apply to a
     * prescription in one click.
     */
    private function seedMedicalPlans(Doctor $doctor): \Illuminate\Support\Collection
    {
        $plans = [
            [
                'title' => 'خطة علاج التهاب الجهاز التنفسي',
                'items' => [
                    ['medicine_name' => 'Augmentin 1g', 'dose' => 'قرص', 'frequency' => 'مرتين يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                    ['medicine_name' => 'Paracetamol 500mg', 'dose' => 'قرص', 'frequency' => 'عند اللزوم', 'duration' => '5 أيام', 'instructions' => 'كل 8 ساعات عند ارتفاع الحرارة'],
                    ['medicine_name' => 'Vitamin C 1000mg', 'dose' => 'قرص فوار', 'frequency' => 'مرة يومياً', 'duration' => '14 يوم', 'instructions' => 'يذاب في كوب ماء'],
                ],
            ],
            [
                'title' => 'خطة متابعة ضغط الدم',
                'items' => [
                    ['medicine_name' => 'Concor 5mg', 'dose' => 'قرص', 'frequency' => 'مرة صباحاً', 'duration' => '30 يوم', 'instructions' => 'قبل الإفطار'],
                    ['medicine_name' => 'Aspocid 75mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'بعد الغداء'],
                    ['medicine_name' => 'Lasix 20mg', 'dose' => 'نصف قرص', 'frequency' => 'يوم بعد يوم', 'duration' => '15 يوم', 'instructions' => 'صباحاً مع متابعة الأملاح'],
                ],
            ],
            [
                'title' => 'خطة ضبط السكري من النوع الثاني',
                'items' => [
                    ['medicine_name' => 'Glucophage 850mg', 'dose' => 'قرص', 'frequency' => 'مرتين يومياً', 'duration' => 'مستمر', 'instructions' => 'مع الوجبة'],
                    ['medicine_name' => 'Januvia 100mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'صباحاً'],
                ],
            ],
            [
                'title' => 'خطة علاج القولون العصبي',
                'items' => [
                    ['medicine_name' => 'Colospasmin Forte', 'dose' => 'قرص', 'frequency' => 'ثلاث مرات يومياً', 'duration' => '10 أيام', 'instructions' => 'قبل الأكل بنصف ساعة'],
                    ['medicine_name' => 'Lacteol Fort', 'dose' => 'كبسولة', 'frequency' => 'مرتين يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                ],
            ],
        ];

        return collect($plans)->map(function (array $plan) use ($doctor) {
            $model = MedicalPlan::create(['doctor_id' => $doctor->id, 'title' => $plan['title']]);
            foreach ($plan['items'] as $item) {
                $model->items()->create($item);
            }

            return $model;
        });
    }

    /** Doctor-defined fields captured on every examination (vitals etc.). */
    private function seedExaminationFields(Doctor $doctor): \Illuminate\Support\Collection
    {
        $fields = [
            ['label' => 'ضغط الدم', 'type' => 'text', 'options' => null],
            ['label' => 'درجة الحرارة (°م)', 'type' => 'number', 'options' => null],
            ['label' => 'الوزن (كجم)', 'type' => 'number', 'options' => null],
            ['label' => 'نسبة الأكسجين', 'type' => 'percentage', 'options' => null],
            ['label' => 'الحالة العامة', 'type' => 'select', 'options' => 'مستقرة,تحتاج متابعة,حرجة'],
            ['label' => 'مدخّن', 'type' => 'select', 'options' => 'نعم,لا'],
        ];

        return collect($fields)->map(fn ($field, $i) => ExaminationField::create([
            'doctor_id' => $doctor->id,
            'label' => $field['label'],
            'type' => $field['type'],
            'options' => $field['options'],
            'sort_order' => $i,
            'is_active' => true,
        ]));
    }

    /** Demo patients with a filled-in medical profile. */
    private function seedPatients(int $count): \Illuminate\Support\Collection
    {
        $pool = [
            ['name' => 'أحمد كمال إبراهيم', 'gender' => 'male', 'dob' => '1990-04-12', 'blood' => 'O+', 'allergies' => ['البنسلين'], 'chronic' => ['ارتفاع ضغط الدم'], 'history' => 'استئصال زائدة دودية عام 2015. غير مدخن.'],
            ['name' => 'سارة محمود عبد العزيز', 'gender' => 'female', 'dob' => '1985-09-30', 'blood' => 'A+', 'allergies' => ['الأسبرين', 'غبار الطلع'], 'chronic' => ['الربو'], 'history' => 'حملان سابقان بدون مضاعفات. متابعة دورية للربو.'],
            ['name' => 'محمد عبد الرحمن سيد', 'gender' => 'male', 'dob' => '2001-01-05', 'blood' => 'B+', 'allergies' => [], 'chronic' => [], 'history' => 'لا يوجد تاريخ مرضي يُذكر.'],
            ['name' => 'فاطمة السيد حسن', 'gender' => 'female', 'dob' => '1978-06-22', 'blood' => 'AB+', 'allergies' => ['السلفا'], 'chronic' => ['السكري من النوع الثاني', 'ارتفاع الكوليسترول'], 'history' => 'سكري منذ 2012، على علاج فموي. قسطرة قلبية تشخيصية 2020.'],
            ['name' => 'خالد مصطفى الشناوي', 'gender' => 'male', 'dob' => '1995-11-17', 'blood' => 'O-', 'allergies' => [], 'chronic' => ['القولون العصبي'], 'history' => 'كسر في الساعد الأيمن 2018 — التئم بالكامل.'],
            ['name' => 'منى صلاح الدين', 'gender' => 'female', 'dob' => '1969-03-08', 'blood' => 'A-', 'allergies' => ['اليود'], 'chronic' => ['خشونة الركبة', 'الغدة الدرقية'], 'history' => 'استئصال جزئي للغدة الدرقية 2016. على Eltroxin يومياً.'],
            ['name' => 'يوسف طارق منصور', 'gender' => 'male', 'dob' => '2013-07-25', 'blood' => 'B-', 'allergies' => ['الفول السوداني'], 'chronic' => ['حساسية الصدر'], 'history' => 'نزلات شعبية متكررة في الشتاء. تطعيمات مكتملة.'],
            ['name' => 'نورهان عادل فؤاد', 'gender' => 'female', 'dob' => '1993-12-02', 'blood' => 'O+', 'allergies' => [], 'chronic' => ['أنيميا نقص الحديد'], 'history' => 'متابعة أنيميا منذ 2021 — تحسن بعد العلاج بالحديد.'],
            ['name' => 'عمرو حسني الجندي', 'gender' => 'male', 'dob' => '1962-02-14', 'blood' => 'A+', 'allergies' => ['البنسلين'], 'chronic' => ['ارتفاع ضغط الدم', 'قصور الشريان التاجي'], 'history' => 'دعامتان بالشريان التاجي 2019. على مضاد تجلط.'],
            ['name' => 'هالة رمضان عثمان', 'gender' => 'female', 'dob' => '1988-08-19', 'blood' => 'AB-', 'allergies' => [], 'chronic' => [], 'history' => 'ولادة قيصرية 2019. لا أمراض مزمنة.'],
            ['name' => 'كريم وليد شعبان', 'gender' => 'male', 'dob' => '1999-05-11', 'blood' => 'B+', 'allergies' => ['اللاتكس'], 'chronic' => [], 'history' => 'رياضي — إصابة رباط صليبي 2022، تم إصلاحها بالمنظار.'],
            ['name' => 'إيمان جمال الدسوقي', 'gender' => 'female', 'dob' => '1975-10-27', 'blood' => 'O+', 'allergies' => [], 'chronic' => ['الصداع النصفي'], 'history' => 'نوبات صداع نصفي شهرية، تستجيب للعلاج.'],
        ];

        $insurers = $this->insurers();

        return collect($pool)->take(max(1, $count))->map(function (array $p, int $i) use ($insurers) {
            // The phone is already unique across users + clients, so deriving
            // the demo email from it keeps repeat runs from colliding.
            $phone = $this->uniquePhone();

            return Client::create([
                'name' => $p['name'],
                'phone_number' => $phone,
                'email' => "patient.{$phone}@demo.rosheta.test",
                'password' => $this->defaultPassword(),
                'gender' => $p['gender'],
                'dob' => $p['dob'],
                'national_id' => $this->nationalId($p['dob'], $i),
                'blood_type' => $p['blood'],
                'address' => $this->addressLine($i),
                'allergies' => $p['allergies'] ?: null,
                'chronic_diseases' => $p['chronic'] ?: null,
                'medical_history' => $p['history'],
                // Give roughly every third patient insurance so the split flow shows.
                'insurance_company_id' => $i % 3 === 0 ? $insurers->random()->id : null,
            ]);
        })->values();
    }

    /** Completed visits in the recent past, so patient files have history. */
    private function seedPastVisits(Doctor $doctor, Clinic $clinic, \Illuminate\Support\Collection $patients, int $count): \Illuminate\Support\Collection
    {
        $created = collect();

        for ($i = 0; $i < $count; $i++) {
            $patient = $patients[$i % $patients->count()];
            $daysBack = ($i + 1) * 9; // spread visits over the past few months
            $scheduledAt = Carbon::today()->subDays($daysBack)->setTime(10 + ($i % 4), ($i % 2) * 30);
            $isFollowUp = $i % 3 !== 0;

            $created->push(Appointment::create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinic->id,
                'client_id' => $patient->id,
                'scheduled_at' => $scheduledAt,
                'queue_number' => ($i % 5) + 1,
                'source' => 'system',
                'type' => $isFollowUp ? 'follow_up' : 'medical_examination',
                'price' => $isFollowUp ? $clinic->follow_up_price : $clinic->medical_examination_price,
                'status' => 'completed',
                'reason' => $isFollowUp ? 'متابعة الحالة' : 'كشف أول',
            ]));
        }

        return $created;
    }

    /**
     * Today plus the next N days: a lifelike queue for today (one done, one in
     * the room, the rest waiting) and a filling schedule for the days after.
     */
    private function seedUpcomingAppointments(
        Doctor $doctor,
        Clinic $clinic,
        \Illuminate\Support\Collection $patients,
        int $perDay,
        int $daysAhead,
        array $input
    ): \Illuminate\Support\Collection {
        $closedDays = $input['closed_days'] ?? self::DEFAULT_CLOSED_DAYS;
        $openFrom = $input['open_from'] ?? '09:00';
        $duration = (int) ($input['appointment_duration'] ?? 30);

        $created = collect();
        $patientIndex = 0;

        for ($dayOffset = 0; $dayOffset <= $daysAhead; $dayOffset++) {
            $date = Carbon::today()->addDays($dayOffset);

            // Keep the closed day on the calendar but leave it empty.
            if (in_array(strtolower($date->englishDayOfWeek), $closedDays, true)) {
                continue;
            }

            $slotStart = $date->copy()->setTimeFromTimeString($openFrom);

            for ($slot = 0; $slot < $perDay; $slot++) {
                $patient = $patients[$patientIndex % $patients->count()];
                $patientIndex++;

                $isFollowUp = ($slot % 3) === 1;
                $status = $this->statusFor($dayOffset, $slot, $perDay);

                $created->push(Appointment::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'client_id' => $patient->id,
                    'scheduled_at' => $slotStart->copy()->addMinutes($slot * $duration),
                    'queue_number' => $slot + 1,
                    'source' => $status === 'pending' ? 'reservation' : ($slot % 4 === 0 ? 'kiosk' : 'system'),
                    'type' => $isFollowUp ? 'follow_up' : 'medical_examination',
                    'price' => $isFollowUp ? $clinic->follow_up_price : $clinic->medical_examination_price,
                    'status' => $status,
                    'reason' => $this->reasonFor($slot),
                ]));
            }
        }

        return $created;
    }

    /**
     * Today shows a queue mid-flow; later days are mostly scheduled with a
     * couple of platform bookings still awaiting the front desk's confirmation.
     */
    private function statusFor(int $dayOffset, int $slot, int $perDay): string
    {
        if ($dayOffset === 0) {
            return match (true) {
                $slot === 0 => 'completed',
                $slot === 1 => 'completed',
                $slot === 2 => 'under_examination',
                $slot === $perDay - 1 => 'pending',
                default => 'scheduled',
            };
        }

        return $slot % 4 === 3 ? 'pending' : 'scheduled';
    }

    private function reasonFor(int $slot): string
    {
        $reasons = [
            'كشف أول — ألم بالصدر وكحة مستمرة',
            'متابعة نتائج التحاليل',
            'ارتفاع في درجة الحرارة منذ يومين',
            'متابعة ضغط الدم وضبط الجرعة',
            'صداع متكرر ودوخة',
            'ألم بالمعدة بعد الأكل',
            'متابعة بعد انتهاء كورس العلاج',
        ];

        return $reasons[$slot % count($reasons)];
    }

    /**
     * Diagnosis (with its treatment roadmap) plus a printable prescription for
     * a visit that has happened.
     */
    private function seedDiagnosisAndPrescription(Appointment $appointment, Doctor $doctor, int $i): void
    {
        $templates = $this->clinicalTemplates();
        $template = $templates[$i % count($templates)];

        $diagnosis = Diagnosis::create([
            'appointment_id' => $appointment->id,
            'client_id' => $appointment->client_id,
            'doctor_id' => $doctor->id,
            'diagnosis' => $template['diagnosis'],
            'treatment_plan' => $template['treatment_plan'],
            'notes' => $template['notes'],
        ]);

        $prescription = $appointment->prescriptions()->create([
            'code' => 'RX-' . strtoupper(Str::random(8)),
            'client_id' => $appointment->client_id,
            'doctor_id' => $doctor->id,
            'diagnosis_id' => $diagnosis->id,
            'notes' => 'يرجى الالتزام بالجرعات، ومراجعة الطبيب فوراً عند ظهور أي أعراض جديدة.',
        ]);

        foreach ($template['medicines'] as $item) {
            $prescription->items()->create($item);
        }
    }

    /** Doctor's orders: lab tests, radiology and follow-up examinations. */
    private function seedMedicalRequests(Appointment $appointment, Doctor $doctor, int $i): int
    {
        $templates = $this->clinicalTemplates();
        $template = $templates[$i % count($templates)];
        $count = 0;

        foreach ($template['requests'] as $request) {
            MedicalRequest::create([
                'appointment_id' => $appointment->id,
                'client_id' => $appointment->client_id,
                'doctor_id' => $doctor->id,
                'type' => $request['type'],
                'name' => $request['name'],
                // Older visits already have their results back.
                'status' => $appointment->status === 'completed' && $i % 2 === 0 ? 'completed' : 'requested',
                'notes' => $request['notes'] ?? null,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Lab / radiology results uploaded into the clinic, each with a real (small,
     * generated) PDF so the "view result" links open something.
     */
    private function seedPatientTests(Appointment $appointment, Doctor $doctor, ?int $uploadedBy, int $i): int
    {
        // Only attach results to some visits — an empty state is worth showing too.
        if ($i % 2 !== 0) {
            return 0;
        }

        $sets = [
            [
                'type' => 'lab',
                'title' => 'صورة دم كاملة (CBC)',
                'notes' => 'كرات الدم البيضاء مرتفعة قليلاً — يتوافق مع التهاب حاد.',
                'lines' => ['Complete Blood Count (CBC)', 'WBC: 12.4 x10^3/uL (high)', 'RBC: 4.8 x10^6/uL', 'Hemoglobin: 13.6 g/dL', 'Platelets: 268 x10^3/uL'],
            ],
            [
                'type' => 'radiology',
                'title' => 'أشعة عادية على الصدر',
                'notes' => 'تشوش بالفص السفلي الأيمن — يُنصح بالمتابعة بعد العلاج.',
                'lines' => ['Chest X-Ray - PA view', 'Findings: Right lower lobe haziness.', 'Heart size within normal limits.', 'No pleural effusion.', 'Impression: Early bronchopneumonia.'],
            ],
            [
                'type' => 'lab',
                'title' => 'وظائف كبد وكلى',
                'notes' => 'جميع القيم داخل المعدل الطبيعي.',
                'lines' => ['Liver & Kidney Function Panel', 'ALT: 28 U/L', 'AST: 24 U/L', 'Creatinine: 0.9 mg/dL', 'Urea: 31 mg/dL'],
            ],
            [
                'type' => 'radiology',
                'title' => 'موجات صوتية على البطن',
                'notes' => 'دهون بسيطة على الكبد من الدرجة الأولى.',
                'lines' => ['Abdominal Ultrasound', 'Liver: mildly increased echogenicity (grade I).', 'Gallbladder: no stones.', 'Kidneys: normal size and outline.', 'Impression: Grade I fatty liver.'],
            ],
        ];

        $chosen = [$sets[$i % count($sets)], $sets[($i + 1) % count($sets)]];
        $count = 0;

        foreach ($chosen as $set) {
            $test = PatientTest::create([
                'client_id' => $appointment->client_id,
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'uploaded_by' => $uploadedBy,
                'type' => $set['type'],
                'title' => $set['title'],
                'notes' => $set['notes'],
            ]);

            $this->attachGeneratedPdf($test, $appointment->id, $uploadedBy, $set['type'], $set['lines']);
            $count++;
        }

        return $count;
    }

    /** Vitals captured for the visit against the doctor's custom fields. */
    private function seedExaminationValues(Appointment $appointment, \Illuminate\Support\Collection $fields, int $i): void
    {
        $values = [
            'ضغط الدم' => ['120/80', '135/85', '110/70', '145/95'],
            'درجة الحرارة (°م)' => ['37.2', '38.4', '36.8', '37.9'],
            'الوزن (كجم)' => ['78', '64', '91', '55'],
            'نسبة الأكسجين' => ['98', '95', '99', '93'],
            'الحالة العامة' => ['مستقرة', 'تحتاج متابعة', 'مستقرة', 'تحتاج متابعة'],
            'مدخّن' => ['لا', 'نعم', 'لا', 'لا'],
        ];

        foreach ($fields as $field) {
            $options = $values[$field->label] ?? null;
            if (! $options) {
                continue;
            }

            $appointment->examinationValues()->updateOrCreate(
                ['examination_field_id' => $field->id],
                ['value' => $options[$i % count($options)]]
            );
        }
    }

    /**
     * Front-desk money: chargeable extras on some visits, the payment itself,
     * and an insurance split for insured patients.
     */
    private function seedMoney(\Illuminate\Support\Collection $appointments, \Illuminate\Support\Collection $billableItems, User $assistant, Clinic $clinic): array
    {
        $stats = ['collections' => 0, 'insurance_splits' => 0, 'extra_items' => 0];
        $insurers = $this->insurers();

        foreach ($appointments->where('status', 'completed')->values() as $i => $appointment) {
            $extrasTotal = 0.0;

            // Every other completed visit picks up a chargeable extra.
            if ($i % 2 === 0 && $billableItems->isNotEmpty()) {
                $item = $billableItems[$i % $billableItems->count()];
                $quantity = ($i % 3) + 1;

                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'billable_item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $quantity,
                    'unit_price' => $item->price,
                ]);

                $extrasTotal = (float) $item->price * $quantity;
                $stats['extra_items']++;
            }

            $total = (float) $appointment->price + $extrasTotal;
            $client = $appointment->client;

            // Insured patients: the company covers 70%, the patient pays the rest.
            if ($client && $client->insurance_company_id) {
                $insuranceAmount = round($total * 0.7, 2);
                $patientAmount = round($total - $insuranceAmount, 2);

                AppointmentInsurance::create([
                    'appointment_id' => $appointment->id,
                    'insurance_company_id' => $client->insurance_company_id,
                    'patient_amount' => $patientAmount,
                    'insurance_amount' => $insuranceAmount,
                    'note' => 'تغطية تأمينية 70% حسب عقد الشركة.',
                    'created_by' => $assistant->id,
                ]);

                $stats['insurance_splits']++;
                $total = $patientAmount;
            } elseif ($i % 5 === 0 && $insurers->isNotEmpty()) {
                // A walk-in who turned out to carry a card.
                $insuranceAmount = round($total * 0.5, 2);
                AppointmentInsurance::create([
                    'appointment_id' => $appointment->id,
                    'insurance_company_id' => $insurers->random()->id,
                    'patient_amount' => round($total - $insuranceAmount, 2),
                    'insurance_amount' => $insuranceAmount,
                    'note' => 'تغطية 50% — بطاقة تأمين مقدمة عند الاستقبال.',
                    'created_by' => $assistant->id,
                ]);
                $stats['insurance_splits']++;
                $total = round($total - $insuranceAmount, 2);
            }

            if ($total <= 0) {
                continue;
            }

            PaymentCollection::create([
                'appointment_id' => $appointment->id,
                'amount' => $total,
                'collected_by' => $assistant->id,
                'collected_at' => $appointment->scheduled_at,
                'note' => $extrasTotal > 0 ? 'كشف + خدمات إضافية' : null,
            ]);
            $stats['collections']++;
        }

        return $stats;
    }

    /**
     * Accepted lab / radiology offers on the marketplace side, which is where
     * the examine screen's read-only "Lab results" panel reads from.
     */
    private function seedMarketplaceLabResults(\Illuminate\Support\Collection $patients, ?int $uploadedBy): int
    {
        // The offer belongs to the lab's owner account, so only labs that have
        // one can stand in for the marketplace side.
        $laboratory = Laboratory::query()->whereNotNull('user_id')->inRandomOrder()->first();
        if (! $laboratory) {
            return 0;
        }

        $labTests = MedicalTest::query()->inRandomOrder()->limit(6)->get();
        if ($labTests->isEmpty()) {
            return 0;
        }

        $count = 0;

        // Two patients get results back from the labs marketplace: one lab
        // panel, one radiology study.
        foreach ($patients->take(2)->values() as $i => $patient) {
            $type = $i === 0 ? 'test' : 'radiology';

            $request = ClientRequest::create([
                'client_id' => $patient->id,
                'status' => 'completed',
                'type' => $type === 'radiology' ? 'radiology' : 'test',
                'model_type' => Laboratory::class,
                'model_id' => $laboratory->id,
                'note' => 'طلب تحاليل بناءً على روشتة الطبيب.',
            ]);

            $chosenTests = $labTests->skip($i * 2)->take(2);
            foreach ($chosenTests as $test) {
                ClientRequestLine::create([
                    'client_request_id' => $request->id,
                    'item_type' => $type === 'radiology' ? 'radiology' : 'test',
                    'medical_test_id' => $test->id,
                    'quantity' => 1,
                ]);
            }

            $offer = Offer::create([
                'client_request_id' => $request->id,
                'laboratory_id' => $laboratory->id,
                'user_id' => $laboratory->user_id,
                'status' => 'accepted',
                'vendor_status' => 'test_completed',
                'request_type' => $type,
                'total_price' => 350 + ($i * 120),
                'visit_price' => 50,
            ]);

            foreach ($chosenTests as $test) {
                OfferLine::create([
                    'offer_id' => $offer->id,
                    'item_type' => 'test',
                    'medical_test_id' => $test->id,
                    'quantity' => 1,
                    'price' => 175 + ($i * 60),
                ]);
            }

            $this->attachGeneratedPdf(
                $offer,
                null,
                $uploadedBy,
                $type === 'radiology' ? 'radiology' : 'lab',
                $type === 'radiology'
                    ? ['Radiology Report', 'Study: CT scan without contrast.', 'Findings: No acute intracranial abnormality.', 'Impression: Unremarkable study.']
                    : ['Laboratory Report', 'Fasting blood sugar: 96 mg/dL', 'HbA1c: 5.4 %', 'Total cholesterol: 178 mg/dL', 'Impression: Within normal limits.']
            );

            $count++;
        }

        return $count;
    }

    // =====================================================================
    //  Helpers
    // =====================================================================

    /**
     * Diagnosis + treatment roadmap + medicines + orders, reused across visits.
     */
    private function clinicalTemplates(): array
    {
        static $templates = null;

        return $templates ??= [
            [
                'diagnosis' => 'التهاب رئوي شعبي بالفص السفلي الأيمن',
                'treatment_plan' => "خريطة العلاج:\n1) مضاد حيوي واسع المجال لمدة 7 أيام.\n2) خافض حرارة عند اللزوم مع سوائل دافئة.\n3) راحة تامة لمدة 3 أيام وتجنب المجهود.\n4) إعادة أشعة على الصدر بعد أسبوع.\n5) متابعة بالعيادة بعد 10 أيام لتقييم الاستجابة.",
                'notes' => 'المريض يشكو من كحة منتجة وحرارة منذ 4 أيام. الصدر به فرقعات بالقاعدة اليمنى.',
                'medicines' => [
                    ['medicine_name' => 'Augmentin 1g', 'dose' => 'قرص', 'frequency' => 'مرتين يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                    ['medicine_name' => 'Paracetamol 500mg', 'dose' => 'قرص', 'frequency' => 'عند اللزوم', 'duration' => '5 أيام', 'instructions' => 'كل 8 ساعات للحرارة'],
                    ['medicine_name' => 'Bronchicum syrup', 'dose' => 'ملعقة', 'frequency' => 'ثلاث مرات يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                ],
                'requests' => [
                    ['type' => 'lab_test', 'name' => 'صورة دم كاملة (CBC)', 'notes' => 'لتقييم شدة الالتهاب'],
                    ['type' => 'lab_test', 'name' => 'سرعة الترسيب (ESR)'],
                    ['type' => 'radiology', 'name' => 'أشعة عادية على الصدر', 'notes' => 'وضع أمامي خلفي'],
                ],
            ],
            [
                'diagnosis' => 'ارتفاع ضغط الدم — غير منضبط على الجرعة الحالية',
                'treatment_plan' => "خريطة العلاج:\n1) رفع جرعة حاصر بيتا مع إضافة مدر بول خفيف.\n2) نظام غذائي قليل الملح (أقل من 5 جم يومياً).\n3) قياس الضغط مرتين يومياً وتسجيل القراءات.\n4) مشي 30 دقيقة يومياً 5 أيام أسبوعياً.\n5) إعادة تقييم بعد شهر مع تحاليل وظائف كلى وأملاح.",
                'notes' => 'قراءات الضغط بالمنزل تتراوح 150/95. لا توجد أعراض قصور قلب.',
                'medicines' => [
                    ['medicine_name' => 'Concor 5mg', 'dose' => 'قرص', 'frequency' => 'مرة صباحاً', 'duration' => '30 يوم', 'instructions' => 'قبل الإفطار'],
                    ['medicine_name' => 'Aspocid 75mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'بعد الغداء'],
                    ['medicine_name' => 'Lasix 20mg', 'dose' => 'نصف قرص', 'frequency' => 'يوم بعد يوم', 'duration' => '15 يوم', 'instructions' => 'صباحاً'],
                ],
                'requests' => [
                    ['type' => 'lab_test', 'name' => 'وظائف كلى وأملاح'],
                    ['type' => 'lab_test', 'name' => 'دهون الدم (Lipid profile)'],
                    ['type' => 'examination', 'name' => 'رسم قلب بالمجهود', 'notes' => 'لاستبعاد نقص تروية'],
                ],
            ],
            [
                'diagnosis' => 'سكري من النوع الثاني — متابعة دورية',
                'treatment_plan' => "خريطة العلاج:\n1) استمرار الميتفورمين مع إضافة مثبط DPP-4.\n2) نظام غذائي منخفض الكربوهيدرات وتقليل الوزن 5%.\n3) قياس السكر الصائم والفاطر 3 مرات أسبوعياً.\n4) فحص قاع العين وفحص القدم السكري سنوياً.\n5) إعادة HbA1c بعد 3 شهور.",
                'notes' => 'HbA1c الأخير 7.8%. لا توجد شكوى من تنميل الأطراف.',
                'medicines' => [
                    ['medicine_name' => 'Glucophage 850mg', 'dose' => 'قرص', 'frequency' => 'مرتين يومياً', 'duration' => 'مستمر', 'instructions' => 'مع الوجبة'],
                    ['medicine_name' => 'Januvia 100mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'صباحاً'],
                ],
                'requests' => [
                    ['type' => 'lab_test', 'name' => 'السكر التراكمي (HbA1c)'],
                    ['type' => 'lab_test', 'name' => 'ميكروألبومين بالبول'],
                    ['type' => 'examination', 'name' => 'فحص قاع العين'],
                ],
            ],
            [
                'diagnosis' => 'التهاب معدة وقولون عصبي',
                'treatment_plan' => "خريطة العلاج:\n1) مثبط مضخة بروتون قبل الإفطار لمدة أسبوعين.\n2) مضاد تقلصات قبل الوجبات.\n3) تجنب الأطعمة الحارة والقهوة والمشروبات الغازية.\n4) وجبات صغيرة متكررة بدل الوجبات الكبيرة.\n5) متابعة بعد أسبوعين، وعمل منظار في حال استمرار الأعراض.",
                'notes' => 'ألم بأعلى البطن يزيد بعد الأكل، مع انتفاخ وتغير في عادات الإخراج.',
                'medicines' => [
                    ['medicine_name' => 'Nexium 40mg', 'dose' => 'كبسولة', 'frequency' => 'مرة يومياً', 'duration' => '14 يوم', 'instructions' => 'قبل الإفطار بنصف ساعة'],
                    ['medicine_name' => 'Colospasmin Forte', 'dose' => 'قرص', 'frequency' => 'ثلاث مرات يومياً', 'duration' => '10 أيام', 'instructions' => 'قبل الأكل'],
                    ['medicine_name' => 'Lacteol Fort', 'dose' => 'كبسولة', 'frequency' => 'مرتين يومياً', 'duration' => '7 أيام', 'instructions' => 'بعد الأكل'],
                ],
                'requests' => [
                    ['type' => 'lab_test', 'name' => 'تحليل براز كامل'],
                    ['type' => 'radiology', 'name' => 'موجات صوتية على البطن والحوض'],
                    ['type' => 'lab_test', 'name' => 'تحليل H. Pylori بالبراز'],
                ],
            ],
            [
                'diagnosis' => 'أنيميا نقص حديد متوسطة',
                'treatment_plan' => "خريطة العلاج:\n1) مكمل حديد فموي مع فيتامين C لتحسين الامتصاص.\n2) نظام غذائي غني بالحديد (كبد، سبانخ، بقوليات).\n3) تجنب الشاي والقهوة مع الوجبات.\n4) إعادة صورة الدم ومخزون الحديد بعد 6 أسابيع.\n5) البحث عن مصدر فقد الدم في حال عدم التحسن.",
                'notes' => 'هيموجلوبين 9.8 جم/دل مع شحوب وإرهاق عند المجهود البسيط.',
                'medicines' => [
                    ['medicine_name' => 'Ferrofolic', 'dose' => 'كبسولة', 'frequency' => 'مرة يومياً', 'duration' => '60 يوم', 'instructions' => 'بعد الأكل مع عصير برتقال'],
                    ['medicine_name' => 'Vitamin C 500mg', 'dose' => 'قرص', 'frequency' => 'مرة يومياً', 'duration' => '30 يوم', 'instructions' => 'مع جرعة الحديد'],
                ],
                'requests' => [
                    ['type' => 'lab_test', 'name' => 'صورة دم كاملة + مخزون الحديد'],
                    ['type' => 'lab_test', 'name' => 'دم خفي بالبراز'],
                ],
            ],
        ];
    }

    /**
     * Write a small generated PDF to the public disk and hang it off the model
     * as a polymorphic attachment.
     */
    private function attachGeneratedPdf(object $model, ?int $appointmentId, ?int $uploadedBy, string $fileType, array $lines): void
    {
        $bytes = $this->pdfBytes($lines);
        $fileName = Str::slug($lines[0] ?? 'result') . '-' . Str::random(6) . '.pdf';
        $path = self::RESULTS_DIR . '/' . $fileName;

        Storage::disk('public')->put($path, $bytes);
        $this->writtenFiles[] = $path;

        $model->attachments()->create([
            'uploaded_by' => $uploadedBy,
            'appointment_id' => $appointmentId,
            'title' => $lines[0] ?? 'Result',
            'file_type' => $fileType,
            'file_path' => $path,
            'file_name' => $fileName,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($bytes),
        ]);
    }

    /**
     * Build a minimal one-page PDF containing the given ASCII lines. Keeps the
     * demo self-contained — no PDF library and no placeholder binaries in the
     * repo. Lines must be Latin text (the base-14 Helvetica font has no Arabic).
     */
    private function pdfBytes(array $lines): string
    {
        $stream = "BT\n/F1 13 Tf\n56 780 Td\n14 TL\n";
        foreach ($lines as $index => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= $index === 0 ? "($escaped) Tj\n" : "T*\nT*\n($escaped) Tj\n";
        }
        $stream .= 'ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                . '/Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $startXref = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$startXref}\n%%EOF";

        return $pdf;
    }

    /** Insurance companies to split visits against, creating a couple if bare. */
    private function insurers(): \Illuminate\Support\Collection
    {
        $defaults = [
            ['name' => 'MedNet Egypt', 'name_ar' => 'ميد نت مصر'],
            ['name' => 'Allianz Care', 'name_ar' => 'أليانز للرعاية الصحية'],
            ['name' => 'Misr Insurance', 'name_ar' => 'مصر للتأمين'],
        ];

        foreach ($defaults as $insurer) {
            InsuranceCompany::firstOrCreate(
                ['name' => $insurer['name']],
                ['name_ar' => $insurer['name_ar'], 'is_active' => true]
            );
        }

        return InsuranceCompany::where('is_active', true)->get();
    }

    private function clinicNameFromDoctor(string $doctorName): string
    {
        return app()->getLocale() === 'ar'
            ? 'عيادة ' . $doctorName
            : $doctorName . ' Clinic';
    }

    private function assistantNameFor(string $clinicName): string
    {
        return app()->getLocale() === 'ar'
            ? 'مساعد ' . $clinicName
            : $clinicName . ' Assistant';
    }

    private function defaultPassword(): string
    {
        return 'password';
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'doctor';
        $slug = $base;
        $i = 2;

        while (Doctor::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * A readable, unused login email derived from the doctor's name — Arabic
     * names slug to empty, so fall back to the role.
     */
    private function uniqueEmail(string $doctorName, string $role): string
    {
        $base = Str::slug($doctorName) ?: 'clinic';
        $email = "{$base}.{$role}@rosheta.test";
        $i = 2;

        while (User::where('email', $email)->exists()) {
            $email = "{$base}.{$role}{$i}@rosheta.test";
            $i++;
        }

        return $email;
    }

    /** An unused Egyptian-format mobile number (unique across users + clients). */
    private function uniquePhone(): string
    {
        do {
            $phone = '010' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (
            User::where('phone_number', $phone)->exists()
            || Client::where('phone_number', $phone)->exists()
        );

        return $phone;
    }

    private function nationalId(string $dob, int $index): string
    {
        $date = Carbon::parse($dob);
        $century = $date->year < 2000 ? '2' : '3';

        return $century
            . $date->format('ymd')
            . str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT)
            . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }

    private function addressLine(int $index): string
    {
        $streets = ['شارع التحرير، الدقي', 'شارع جامعة الدول العربية، المهندسين', 'شارع الهرم، الجيزة', 'شارع مصطفى النحاس، مدينة نصر', 'شارع البحر، طنطا', 'شارع سعد زغلول، المنصورة'];

        return ($index + 3) . ' ' . $streets[$index % count($streets)];
    }
}
