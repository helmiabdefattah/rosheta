<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use App\Models\MedicalTest;
use App\Models\MedicalTestOffer;
use Illuminate\Database\Seeder;

class MedicalTestOfferSeeder extends Seeder
{
    public function run(): void
    {
        $testIds = MedicalTest::query()->pluck('id')->all();
        $labIds = Laboratory::query()->pluck('id')->all();

        if (empty($testIds) || empty($labIds)) {
            return;
        }

        $target = 300;
        $created = 0;
        $guard = 0;
        while ($created < $target && $guard < $target * 3) {
            $guard++;

            $testId = $testIds[array_rand($testIds)];
            $labId = $labIds[array_rand($labIds)];
            $price = random_int(100, 2000);
            $discount = random_int(5, 60);
            $offerPrice = round($price * (1 - ($discount / 100)), 2);

            $offer = MedicalTestOffer::firstOrCreate(
                ['medical_test_id' => $testId, 'laboratory_id' => $labId],
                [
                    'price' => $price,
                    'offer_price' => $offerPrice,
                    'discount' => $discount,
                ]
            );

            if ($offer->wasRecentlyCreated) {
                $created++;
            }
        }
    }
}




