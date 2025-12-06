<?php

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use App\Models\ClientRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;

    public function mount(): void
    {
        parent::mount();

        $requestId = (int) request()->query('request', 0);
        if ($requestId > 0) {
            $req = ClientRequest::with('lines.medicine', 'lines.medicalTest')->find($requestId);
            if ($req) {
                $prefill = [
                    'client_request_id' => $req->id,
                ];

                // Prefill offer lines based on type
                if ($req->type === 'medicine') {
                    $prefill['lines'] = $req->lines->map(fn($l) => [
                        'medicine_id' => $l->medicine_id,
                        'quantity' => $l->quantity,
                        'unit' => $l->unit,
                        'price' => null,
                    ])->toArray();
                } elseif ($req->type === 'test') {
                    $prefill['lines'] = $req->lines->map(fn($l) => [
                        'medical_test_id' => $l->medical_test_id,
                        'price' => null,
                    ])->toArray();

                    // If user has a lab, prefill and disable the field in the form
                    if (auth()->user()?->laboratory_id) {
                        $prefill['laboratory_id'] = auth()->user()->laboratory_id;
                    }
                }

                $this->form->fill($prefill);
            }
        }
    }
}
