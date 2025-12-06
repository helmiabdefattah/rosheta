<?php

namespace App\Filament\Resources\MedicalTests\Pages;

use App\Filament\Resources\MedicalTests\MedicalTestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicalTests extends ListRecords
{
    protected static string $resource = MedicalTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}










