<?php

namespace App\Filament\Resources\ClientRequests\Pages;

use App\Filament\Resources\ClientRequests\ClientRequestResource;
use App\Filament\Resources\MedicalTestOffers\MedicalTestOfferResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClientRequest extends ViewRecord
{
    protected static string $resource = ClientRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->color('success'),
            ];
    }
}



