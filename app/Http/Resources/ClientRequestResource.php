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
        $isTestRequest = $this->type === 'test';

        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client_address_id' => $this->client_address_id,
            'status' => $this->status,
            'type' => $this->type,
            'type_label' => match ($this->type) {
                'test' => 'Medical Tests',
                'radiology' => 'Radiology',
                default => 'Medicines',
            },

            'pregnant' => (bool)$this->pregnant,
            'diabetic' => (bool)$this->diabetic,
            'heart_patient' => (bool)$this->heart_patient,
            'high_blood_pressure' => (bool)$this->high_blood_pressure,
            'note' => $this->note,
            'images' => $this->images ?? [],

            'address' => $this->address ? [
                'id' => $this->address->id,
                'address' => $this->address->address,
                'location' => $this->address->location,
                'city' => $this->address->city ? [
                    'id' => $this->address->city->id,
                    'name' => $this->address->city->name,
                    'name_ar' => $this->address->city->name_ar,
                ] : null,
                'area' => $this->address->area ? [
                    'id' => $this->address->area->id,
                    'name' => $this->address->area->name,
                    'name_ar' => $this->address->area->name_ar,
                ] : null,
            ] : null,

            'lines' => ClientRequestLineResource::collection(
                $this->lines
                    ? $this->lines->where('item_type', $this->type === 'test' ? 'test' : 'medicine')
                    : collect()
            ),

            'offers' => $this->offers
                ? $this->offers->map(function ($offer) use ($isTestRequest) {
                    $data = [
                        'id' => $offer->id,
                        'status' => $offer->status,
                        'total_price' => $offer->total_price,
                        'created_at' => $offer->created_at?->toISOString(),
                    ];

                    if ($isTestRequest && $offer->laboratory) {
                        $data['laboratory'] = [
                            'id' => $offer->laboratory->id,
                            'name' => $offer->laboratory->name,
                            'phone' => $offer->laboratory->phone,
                        ];
                    }

                    if (!$isTestRequest && $offer->pharmacy) {
                        $data['pharmacy'] = [
                            'id' => $offer->pharmacy->id,
                            'name' => $offer->pharmacy->name,
                            'phone' => $offer->pharmacy->phone,
                        ];
                    }

                    return $data;
                })
                : [],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
