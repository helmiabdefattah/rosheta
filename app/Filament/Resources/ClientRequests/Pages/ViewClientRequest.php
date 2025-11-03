<?php

namespace App\Filament\Resources\ClientRequests\Pages;

use App\Filament\Resources\ClientRequests\ClientRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClientRequest extends ViewRecord
{
    protected static string $resource = ClientRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}



