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
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client_address_id' => $this->client_address_id,
            'status' => $this->status,
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
            'lines' => ClientRequestLineResource::collection($this->whenLoaded('lines')),
            'offers' => $this->whenLoaded('offers', function () {
                return $this->offers->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'pharmacy_id' => $offer->pharmacy_id,
                        'status' => $offer->status,
                        'total_price' => $offer->total_price,
                        'pharmacy' => $this->when($offer->relationLoaded('pharmacy'), function () use ($offer) {
                            return [
                                'id' => $offer->pharmacy->id,
                                'name' => $offer->pharmacy->name,
                                'phone' => $offer->pharmacy->phone,
                            ];
                        }),
                        'created_at' => $offer->created_at?->toISOString(),
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
