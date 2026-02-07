<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 10 users (email doctor1@doctors.local ... doctor10@doctors.local, password: password)
     * and 10 doctors linked to those users.
     */
    public function run(): void
    {
        $specializations = Specialization::orderBy('id')->get();
        if ($specializations->isEmpty()) {
            $this->command->warn('No specializations found. Run SpecializationsSeeder first.');
            return;
        }

        $doctors = [
            ['name' => 'د. أحمد محمد حسن', 'brief' => 'استشاري باطنة عامة، خبرة أكثر من 15 عاماً في التشخيص والعلاج.'],
            ['name' => 'د. فاطمة علي إبراهيم', 'brief' => 'استشارية أطفال، متخصصة في طب حديثي الولادة والمتابعة الدورية.'],
            ['name' => 'د. كمال عبد الفتاح', 'brief' => 'جراح عام، خبرة في جراحات البطن والغدد والمناظير.'],
            ['name' => 'د. منى سعيد محمود', 'brief' => 'استشارية نساء وتوليد، متابعة الحمل والولادة والكشف الدوري.'],
            ['name' => 'د. عماد الدين حسين', 'brief' => 'استشاري قلب وأوعية دموية، قسطرة وفحوصات الجهد.'],
            ['name' => 'د. هدى محسن فهمي', 'brief' => 'استشارية مخ وأعصاب، تشخيص وعلاج الصداع والدوخة والاعتلالات العصبية.'],
            ['name' => 'د. ياسر طارق ناصر', 'brief' => 'استشاري أنف وأذن وحنجرة، جراحات الجيوب واللوز والسمعيات.'],
            ['name' => 'د. سارة خالد رشيد', 'brief' => 'استشارية عيون، كشف نظر، عدسات، وجراحات الساد والليزر.'],
            ['name' => 'د. وائل مصطفى جابر', 'brief' => 'استشاري جلدية، علاج الأمراض الجلدية والتجميل والحساسية.'],
            ['name' => 'د. نادية فاروق صالح', 'brief' => 'استشارية أسنان، علاج وحشو وتركيبات وتجميل الأسنان.'],
        ];

        foreach ($doctors as $index => $data) {
            $num = $index + 1;
            $email = 'doctor' . $num . '@doctors.local';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            $specialization = $specializations->get($index % $specializations->count());
            $slug = Str::slug($data['name'], '-') . '-' . $num;
            Doctor::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'brief' => $data['brief'],
                    'specialization_id' => $specialization->id,
                    'user_id' => $user->id,
                ]
            );
        }

        $this->command->info('Seeded 10 users and 10 doctors.');
    }
}
