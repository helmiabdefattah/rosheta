<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OfferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('request.id')->label('Request'),
            TextEntry::make('agent.name')->label('Agent'),
            TextEntry::make('status')->badge(),
            TextEntry::make('total_price')->label('Total Price'),
        ]);
    }
}













