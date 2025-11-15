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
            $req = ClientRequest::with('lines')->find($requestId);
            if ($req) {
                $prefill = [
                    'client_request_id' => $req->id,
                    'lines' => $req->lines->map(fn ($l) => [
                        'medicine_id' => $l->medicine_id,
                        'quantity' => $l->quantity,
                        'unit' => $l->unit,
                        'price' => null,
                    ])->toArray(),
                ];
                $this->form->fill($prefill);
            }
        }
    }
}



