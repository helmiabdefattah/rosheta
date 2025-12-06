<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isTestRequest = $this->type == 'test';

        $response = [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client_address_id' => $this->client_address_id,
            'status' => $this->status,
            'type' => $this->type,
            'type_label' => $isTestRequest ? 'Medical Tests' : 'Medicines',
            'pregnant' => $this->pregnant,
            'diabetic' => $this->diabetic,
            'heart_patient' => $this->heart_patient,
            'high_blood_pressure' => $this->high_blood_pressure,
            'note' => $this->note,
            'images' => $this->images,
            'address' => $this->whenLoaded('address', function () {
                return [
                    'id' => $this->address->id,
                    'address' => $this->address->address,
                    'location' => $this->address->location,
                    'city' => $this->whenLoaded('address.city', function () {
                        return [
                            'id' => $this->address->city->id,
                            'name' => $this->address->city->name,
                            'name_ar' => $this->address->city->name_ar,
                        ];
                    }),
                    'area' => $this->whenLoaded('address.area', function () {
                        return [
                            'id' => $this->address->area->id,
                            'name' => $this->address->area->name,
                            'name_ar' => $this->address->area->name_ar,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        // Handle lines based on type
        if ($isTestRequest) {
            $response['lines'] = $this->when($this->relationLoaded('testLines') || $this->relationLoaded('lines'), function () {
                $lines = $this->testLines ?? ($this->lines ? $this->lines->where('item_type', 'test') : collect());
                return ClientRequestLineResource::collection($lines);
            });
        } else {
            $response['lines'] = $this->when($this->relationLoaded('medicineLines') || $this->relationLoaded('lines'), function () {
                $lines = $this->medicineLines ?? ($this->lines ? $this->lines->where('item_type', 'medicine') : collect());
                return ClientRequestLineResource::collection($lines);
            });
        }

        // Handle offers based on type
        $response['offers'] = $this->whenLoaded('offers', function () use ($isTestRequest) {
            $offers = $this->offers;

            return $offers->map(function ($offer) use ($isTestRequest) {
                $offerData = [
                    'id' => $offer->id,
                    'status' => $offer->status,
                    'total_price' => $offer->total_price,
                    'created_at' => $offer->created_at?->toISOString(),
                ];

                if ($isTestRequest && $offer->relationLoaded('laboratory')) {
                    $offerData['laboratory_id'] = $offer->laboratory_id;
                    $offerData['laboratory'] = [
                        'id' => $offer->laboratory->id,
                        'name' => $offer->laboratory->name,
                        'phone' => $offer->laboratory->phone,
                    ];
                } elseif (!$isTestRequest && $offer->relationLoaded('pharmacy')) {
                    $offerData['pharmacy_id'] = $offer->pharmacy_id;
                    $offerData['pharmacy'] = [
                        'id' => $offer->pharmacy->id,
                        'name' => $offer->pharmacy->name,
                        'phone' => $offer->pharmacy->phone,
                    ];
                }

                return $offerData;
            });
        });

        return $response;
    }}
