<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientRequestLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isTestItem = $this->medical_test_id != null;

        $baseData = [
            'id' => $this->id,
            'client_request_id' => $this->client_request_id,
            'item_type' => $this->item_type,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($isTestItem) {
            // First, try to fetch medical test data if not loaded
            $medicalTestData = null;

            // If relationship is loaded
            if ($this->relationLoaded('medicalTest') && $this->medicalTest) {
                $medicalTestData = [
                    'id' => $this->medicalTest->id,
                    'test_name_en' => $this->medicalTest->test_name_en,
                    'test_name_ar' => $this->medicalTest->test_name_ar,
                    'test_description' => $this->medicalTest->test_description,
                    'conditions' => $this->medicalTest->conditions,
                    'price' => $this->medicalTest->price ?? null,
                ];
            } else {
                // Try to fetch from database manually
                try {
                    $medicalTest = \App\Models\MedicalTest::find($this->medical_test_id);
                    if ($medicalTest) {
                        $medicalTestData = [
                            'id' => $medicalTest->id,
                            'test_name_en' => $medicalTest->test_name_en,
                            'test_name_ar' => $medicalTest->test_name_ar,
                            'test_description' => $medicalTest->test_description,
                            'conditions' => $medicalTest->conditions,
                            'price' => $medicalTest->price ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    // Handle error if needed
                }
            }

            return array_merge($baseData, [
                'medical_test_id' => $this->medical_test_id,
                'medical_test' => $medicalTestData ?? [
                        'id' => $this->medical_test_id,
                        'test_name_en' => 'Test Not Found',
                        'test_name_ar' => 'اختبار غير موجود',
                        'error' => true,
                    ],
            ]);
        } else {
            // For medicine items
            $medicineData = null;

            if ($this->relationLoaded('medicine') && $this->medicine) {
                $medicineData = [
                    'id' => $this->medicine->id,
                    'name' => $this->medicine->name,
                    'arabic' => $this->medicine->arabic,
                    'dosage_form' => $this->medicine->dosage_form,
                    'price' => $this->medicine->price,
                ];
            } else {
                // Try to fetch medicine manually if not loaded
                try {
                    $medicine = \App\Models\Medicine::find($this->medicine_id);
                    if ($medicine) {
                        $medicineData = [
                            'id' => $medicine->id,
                            'name' => $medicine->name,
                            'arabic' => $medicine->arabic,
                            'dosage_form' => $medicine->dosage_form,
                            'price' => $medicine->price,
                        ];
                    }
                } catch (\Exception $e) {
                    // Handle error if needed
                }
            }

            return array_merge($baseData, [
                'medicine_id' => $this->medicine_id,
                'medicine' => $medicineData ?? [
                        'id' => $this->medicine_id,
                        'name' => 'Medicine Not Found',
                        'arabic' => 'دواء غير موجود',
                        'error' => true,
                    ],
            ]);
        }
    }
}
