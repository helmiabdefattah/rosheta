<?php

namespace App\Filament\Resources\MedicalTests\Pages;

use App\Filament\Resources\MedicalTests\MedicalTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalTest extends EditRecord
{
    protected static string $resource = MedicalTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
















