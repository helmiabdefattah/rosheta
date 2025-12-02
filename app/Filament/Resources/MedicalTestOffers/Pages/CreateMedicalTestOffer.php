<?php

namespace App\Filament\Resources\MedicalTestOffers\Pages;

use App\Filament\Resources\MedicalTestOffers\MedicalTestOfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalTestOffer extends CreateRecord
{
    protected static string $resource = MedicalTestOfferResource::class;

    public function mount(): void
    {
        parent::mount();
        $medicalTestId = request()->query('medical_test_id');
        if ($medicalTestId) {
            $this->form->fill([
                'medical_test_id' => $medicalTestId,
            ]);
        }
    }
}




