<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Conversation;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for doctor↔patient chat, built around د. كمال عبد الفتاح (seeded by
 * DoctorsSeeder as doctor3@doctors.local / password).
 *
 * Three of the four patients visited recently, so their chat window is open;
 * the fourth visited long ago, which is what the "window closed" state looks
 * like in both inboxes. Re-running the seeder is safe — everything keys off a
 * stable phone number or an existing thread.
 *
 *   php artisan db:seed --class=ChatDemoSeeder
 */
class ChatDemoSeeder extends Seeder
{
    /** Window used for the demo, in days. */
    private const WINDOW_DAYS = 30;

    public function run(): void
    {
        $doctor = Doctor::where('name', 'like', '%كمال%')->first();

        if (! $doctor) {
            $this->command->warn('Doctor كمال not found. Run DoctorsSeeder first.');

            return;
        }

        $doctor->update([
            'chat_enabled' => true,
            'chat_window_days' => self::WINDOW_DAYS,
        ]);

        $clinic = $this->clinicFor($doctor);

        foreach ($this->patients() as $index => $data) {
            $client = Client::firstOrCreate(
                ['phone_number' => $data['phone']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                ]
            );

            // The visit the chat window is counted from. Distinct times keep the
            // (clinic, date, time) unique index happy.
            $visitedAt = now()->subDays($data['visited_days_ago'])->setTime(10 + $index, 0);
            $this->appointmentFor($doctor, $clinic, $client, $visitedAt);

            $conversation = Conversation::between($doctor, $client);
            $this->seedMessages($conversation, $data['messages'], $visitedAt);
        }

        $this->command->info(sprintf(
            'Chat demo seeded for %s (%s) — %d conversations, window %d days.',
            $doctor->name,
            $doctor->user?->email ?? 'no login',
            $doctor->conversations()->count(),
            self::WINDOW_DAYS
        ));
    }

    /**
     * Demo patients. `messages` is a list of [sender, body, minutes after the
     * visit, read?]; the last patient's window is deliberately expired.
     */
    private function patients(): array
    {
        return [
            [
                'name' => 'أحمد سيد عبد الرحمن',
                'phone' => '01000000101',
                'email' => 'ahmed.chat@patients.local',
                'visited_days_ago' => 3,
                'messages' => [
                    ['client', 'السلام عليكم يا دكتور، عملت العملية من ٣ أيام والحمد لله كويس بس فيه ورم بسيط مكان الجرح.', 60, true],
                    ['doctor', 'وعليكم السلام. الورم البسيط طبيعي في أول أسبوع. هل فيه سخونية أو احمرار زايد؟', 180, true],
                    ['client', 'لا مفيش سخونية، بس بحس بشد لما بقف.', 200, true],
                    ['doctor', 'كويس. استمر على المضاد الحيوي لحد ما يخلص، وقلل المجهود أسبوع كمان. لو ظهرت سخونية كلمني فوراً.', 240, true],
                    ['client', 'تمام يا دكتور، شكراً جزيلاً.', 260, false],
                ],
            ],
            [
                'name' => 'منى إبراهيم فتحي',
                'phone' => '01000000102',
                'email' => 'mona.chat@patients.local',
                'visited_days_ago' => 8,
                'messages' => [
                    ['doctor', 'أهلاً أستاذة منى، عاملة إيه بعد الكشف؟ ظهرت نتيجة تحليل الغدة؟', 1440, true],
                    ['client', 'أهلاً دكتور، النتيجة ظهرت امبارح. TSH = 6.2', 1500, true],
                    ['doctor', 'الرقم مرتفع شوية. ابدئي الجرعة اللي كتبتها ونعيد التحليل بعد ٦ أسابيع.', 1560, false],
                ],
            ],
            [
                'name' => 'خالد محمود السيد',
                'phone' => '01000000103',
                'email' => 'khaled.chat@patients.local',
                'visited_days_ago' => 15,
                'messages' => [
                    ['client', 'دكتور، الروشتة اللي كتبتها فيها دواء مش موجود في الصيدلية. فيه بديل؟', 720, true],
                    ['doctor', 'أيوه، فيه بديل بنفس المادة الفعالة. صور الروشتة وابعتها لي وأقولك على الاسم.', 800, true],
                    ['client', 'حاضر يا دكتور، هبعتها النهاردة.', 830, false],
                    ['client', 'الصيدلي قال إن فيه تركيز ١٠ مجم بدل ٢٠. أقسمها؟', 900, false],
                ],
            ],
            [
                // Visited well outside the window: chat shows as closed for them.
                'name' => 'سعاد رمضان عطية',
                'phone' => '01000000104',
                'email' => 'souad.chat@patients.local',
                'visited_days_ago' => 60,
                'messages' => [
                    ['client', 'دكتور، الجرح قفل تماماً والحمد لله.', 2880, true],
                    ['doctor', 'الحمد لله. لو حسيتي بأي حاجة غير طبيعية احجزي متابعة.', 3000, true],
                ],
            ],
        ];
    }

    /** The doctor's clinic — appointments need one, and DoctorsSeeder makes none. */
    private function clinicFor(Doctor $doctor): Clinic
    {
        return $doctor->clinics()->first() ?? Clinic::create([
            'doctor_id' => $doctor->id,
            'name' => 'عيادة د. كمال عبد الفتاح للجراحة العامة',
            'address' => 'شارع الجمهورية، وسط البلد',
            'medical_examination_price' => 250,
            'follow_up_price' => 150,
        ]);
    }

    /** The completed visit the chat window is counted from. */
    private function appointmentFor(Doctor $doctor, Clinic $clinic, Client $client, Carbon $visitedAt): void
    {
        Appointment::firstOrCreate(
            [
                'doctor_id' => $doctor->id,
                'client_id' => $client->id,
                'appointment_date' => $visitedAt->toDateString(),
                'appointment_time' => $visitedAt->format('H:i:s'),
            ],
            [
                'clinic_id' => $clinic->id,
                'scheduled_at' => $visitedAt,
                'type' => 'medical_examination',
                'status' => 'completed',
                'price' => $clinic->medical_examination_price,
                'source' => 'system',
            ]
        );
    }

    /** Replace the thread's messages so re-runs do not pile up duplicates. */
    private function seedMessages(Conversation $conversation, array $messages, Carbon $visitedAt): void
    {
        $conversation->messages()->delete();

        $lastAt = null;
        foreach ($messages as [$sender, $body, $minutesAfterVisit, $read]) {
            $sentAt = $visitedAt->copy()->addMinutes($minutesAfterVisit);
            $lastAt = $sentAt;

            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => $sender,
                'body' => $body,
                'read_at' => $read ? $sentAt->copy()->addMinutes(5) : null,
            ]);

            // Backdate the row so the demo thread reads like a real timeline.
            // Timestamps are not fillable, hence the second write.
            $message->forceFill(['created_at' => $sentAt, 'updated_at' => $sentAt])->save();
        }

        $conversation->forceFill(['last_message_at' => $lastAt])->save();
    }
}
