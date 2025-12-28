<?php

namespace App\Filament\Resources\Laboratories\Pages;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaboratory extends EditRecord
{
    protected static string $resource = LaboratoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
}



















