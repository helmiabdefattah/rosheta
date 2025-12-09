<?php

namespace App\Filament\Resources\MedicalTestRequests\Pages;

use App\Filament\Resources\MedicalTestRequests\MedicalTestRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\MedicalTestOffers\MedicalTestOfferResource;

class ListMedicalTestRequests extends ListRecords
{
    protected static string $resource = MedicalTestRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('makeOffer')
                ->label('Make Offer')
                ->icon('heroicon-o-tag')
                ->url(fn () => MedicalTestOfferResource::getUrl('create'))
                ->color('primary'),
        ];
    }
}




