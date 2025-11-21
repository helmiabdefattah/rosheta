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
        return [
            'id' => $this->id,
            'client_request_id' => $this->client_request_id,
            'medicine_id' => $this->medicine_id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'medicine' => $this->whenLoaded('medicine', function () {
                return [
                    'id' => $this->medicine->id,
                    'name' => $this->medicine->name,
                    'arabic' => $this->medicine->arabic,
                    'dosage_form' => $this->medicine->dosage_form,
                    'price' => $this->medicine->price,
                ];
            }),
        ];
    }
}
