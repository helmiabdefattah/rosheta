<?php

namespace App\Filament\Resources\Laboratories\Pages;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaboratories extends ListRecords
{
    protected static string $resource = LaboratoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(route('laboratories.create')),
        ];
    }
}





















