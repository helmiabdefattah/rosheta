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
            EditAction::make(),
            Action::make('makeLabOffer')
                ->label('Make Lab Offer')
                ->icon('heroicon-o-tag')
                ->color('primary')
                ->url(fn () => MedicalTestOfferResource::getUrl('create', ['client_request_id' => $this->record->id])),
        ];
    }
}



